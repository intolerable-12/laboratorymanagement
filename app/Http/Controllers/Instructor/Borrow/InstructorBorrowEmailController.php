<?php

namespace App\Http\Controllers\Instructor\Borrow;

use App\Mail\BorrowDecisionMail;
use App\Models\BorrowTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InstructorBorrowEmailController
{
	public function sendForwardedToFacilitator(BorrowTransaction $borrowTransaction, User $instructor): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'Instructor',
			decisionLabel: 'Approved by Instructor',
			subjectPrefix: 'Forwarded for facilitator review',
			bodyMessage: 'Your borrow request has been approved by the instructor and forwarded to the facilitator for review.',
			reason: null,
			actorName: $this->buildUserName($instructor),
		);
	}

	public function sendRejectedToRequester(BorrowTransaction $borrowTransaction, User $instructor, string $reason): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'Instructor',
			decisionLabel: 'Rejected by Instructor',
			subjectPrefix: 'Instructor rejection notice',
			bodyMessage: 'Your borrow request was rejected by the instructor.',
			reason: $reason,
			actorName: $this->buildUserName($instructor),
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

		Mail::to($borrowTransaction->borrower->email)->send(new \App\Mail\BorrowDecisionMail(
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
