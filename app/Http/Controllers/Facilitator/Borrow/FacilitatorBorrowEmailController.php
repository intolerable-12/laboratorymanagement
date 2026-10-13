<?php

namespace App\Http\Controllers\Facilitator\Borrow;

use App\Mail\BorrowDecisionMail;
use App\Models\BorrowTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class FacilitatorBorrowEmailController
{
	public function sendForwardedToCoordinator(BorrowTransaction $borrowTransaction, User $facilitator): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'Laboratory In-charge',
			decisionLabel: 'Approved by Laboratory In-charge',
			subjectPrefix: 'Forwarded for coordinator review',
			bodyMessage: 'Your borrow request has been approved by the Laboratory In-charge and forwarded to the coordinator for final review.',
			reason: null,
			actorName: $this->buildUserName($facilitator),
		);
	}

	public function sendApprovedToRequester(BorrowTransaction $borrowTransaction, User $facilitator): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'IBED Laboratory In-charge',
			decisionLabel: 'Approved by Laboratory In-charge',
			subjectPrefix: 'Borrow request approved',
			bodyMessage: 'Your borrow request has been fully approved by the IBED Laboratory In-charge.',
			reason: null,
			actorName: $this->buildUserName($facilitator),
		);
	}

	public function sendRescheduledToRequester(BorrowTransaction $borrowTransaction, User $facilitator): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'IBED Laboratory In-charge',
			decisionLabel: 'Borrow schedule updated',
			subjectPrefix: 'Borrow schedule updated',
			bodyMessage: 'The IBED Laboratory In-charge changed the schedule of your approved borrow request.',
			reason: null,
			actorName: $this->buildUserName($facilitator),
		);
	}

	public function sendRejectedToRequester(BorrowTransaction $borrowTransaction, User $facilitator, string $reason): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'Laboratory In-charge',
			decisionLabel: 'Rejected by Laboratory In-charge',
			subjectPrefix: 'Laboratory In-charge rejection notice',
			bodyMessage: 'Your borrow request was rejected by the Laboratory In-charge.',
			reason: $reason,
			actorName: $this->buildUserName($facilitator),
		);
	}

	private function send(
		BorrowTransaction $borrowTransaction,
		string $reviewerRole,
		string $decisionLabel,
		string $subjectPrefix,
		string $bodyMessage,
		?string $reason,
		?string $actorName,
	): void {
		if (! $borrowTransaction->borrower?->email) {
			return;
		}

		Mail::to($borrowTransaction->borrower->email)->queue(new BorrowDecisionMail(
			borrowTransaction: $borrowTransaction,
			recipientName: $this->buildUserName($borrowTransaction->borrower),
			reviewerRole: $reviewerRole,
			decisionLabel: $decisionLabel,
			bodyMessage: $bodyMessage,
			reason: $reason,
			actorName: $actorName,
			subjectPrefix: $subjectPrefix,
		));
	}

	private function buildUserName(User $user): string
	{
		return trim(collect([
			$user->first_name,
			$user->middle_name,
			$user->last_name,
			$user->suffix,
		])->filter()->implode(' '));
	}
}
