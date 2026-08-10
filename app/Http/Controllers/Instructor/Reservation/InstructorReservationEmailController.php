<?php

namespace App\Http\Controllers\Instructor\Reservation;

use App\Mail\ReservationDecisionMail;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class InstructorReservationEmailController
{
	public function sendForwardedToFacilitator(Reservation $reservation, User $instructor): void
	{
		$this->send(
			reservation: $reservation,
			reviewerRole: 'Instructor',
			decisionLabel: 'Approved by Instructor',
			subjectPrefix: 'Forwarded for facilitator review',
			bodyMessage: 'Your reservation request has been approved by the instructor and forwarded to the facilitator for availability review.',
			reason: null,
			actorName: $this->buildUserName($instructor),
		);
	}

	public function sendRejectedToRequester(Reservation $reservation, User $instructor, string $reason): void
	{
		$this->send(
			reservation: $reservation,
			reviewerRole: 'Instructor',
			decisionLabel: 'Rejected by Instructor',
			subjectPrefix: 'Instructor rejection notice',
			bodyMessage: 'Your reservation request was rejected by the instructor.',
			reason: $reason,
			actorName: $this->buildUserName($instructor),
		);
	}

	private function send(
		Reservation $reservation,
		string $reviewerRole,
		string $decisionLabel,
		string $subjectPrefix,
		string $bodyMessage,
		?string $reason,
		?string $actorName,
	): void {
		if (! $reservation->user?->email) {
			return;
		}

		Mail::to($reservation->user->email)->send(new ReservationDecisionMail(
			reservation: $reservation,
			recipientName: $this->buildUserName($reservation->user),
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
