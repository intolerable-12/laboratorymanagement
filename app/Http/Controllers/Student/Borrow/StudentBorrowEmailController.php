<?php

namespace App\Http\Controllers\Student\Borrow;

use App\Mail\BorrowDecisionMail;
use App\Models\BorrowTransaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class StudentBorrowEmailController
{
	public function sendSubmittedToRequester(BorrowTransaction $borrowTransaction, User $student): void
	{
		$this->send(
			borrowTransaction: $borrowTransaction,
			reviewerRole: 'System',
			decisionLabel: 'Borrow request submitted',
			subjectPrefix: 'Borrow request received',
			bodyMessage: 'Your borrow request has been submitted and is waiting for instructor review.',
			reason: null,
			actorName: null,
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
