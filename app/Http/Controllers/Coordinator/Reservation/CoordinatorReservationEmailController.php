<?php

namespace App\Http\Controllers\Coordinator\Reservation;

use App\Mail\ReservationDecisionMail;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CoordinatorReservationEmailController
{
	public function sendApprovedToRequester(Reservation $reservation, User $coordinator, ?string $remarks = null): void
	{
		$this->send(
			reservation: $reservation,
			reviewerRole: 'Coordinator',
			decisionLabel: 'Approved by Coordinator',
			subjectPrefix: 'Reservation approved',
			bodyMessage: 'Your reservation request has been approved by the coordinator.',
			reason: $remarks,
			actorName: $this->buildUserName($coordinator),
		);
	}

	public function sendRejectedToRequester(Reservation $reservation, User $coordinator, string $reason): void
	{
		$this->send(
			reservation: $reservation,
			reviewerRole: 'Coordinator',
			decisionLabel: 'Rejected by Coordinator',
			subjectPrefix: 'Coordinator rejection notice',
			bodyMessage: 'Your reservation request was rejected by the coordinator.',
			reason: $reason,
			actorName: $this->buildUserName($coordinator),
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

		Mail::to($reservation->user->email)->queue(new ReservationDecisionMail(
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
