<?php

namespace App\Http\Controllers\Coordinator\Borrow;

use App\Mail\BorrowDecisionMail;
use App\Models\BorrowTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CoordinatorBorrowEmailController
{
	public function sendApprovedToRequester(BorrowTransaction $borrowTransaction, User $coordinator, ?string $remarks = null): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'Coordinator',
			decisionLabel: 'Approved by Coordinator',
			subjectPrefix: 'Borrow request approved',
			bodyMessage: 'Your borrow request has been approved by the coordinator.',
			reason: $remarks,
			actorName: $this->buildUserName($coordinator),
		);
	}

	public function sendRejectedToRequester(BorrowTransaction $borrowTransaction, User $coordinator, string $reason): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'Coordinator',
			decisionLabel: 'Rejected by Coordinator',
			subjectPrefix: 'Coordinator rejection notice',
			bodyMessage: 'Your borrow request was rejected by the coordinator.',
			reason: $reason,
			actorName: $this->buildUserName($coordinator),
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

		Mail::to($borrowTransaction->borrower->email)->queue(new \App\Mail\BorrowDecisionMail(
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
