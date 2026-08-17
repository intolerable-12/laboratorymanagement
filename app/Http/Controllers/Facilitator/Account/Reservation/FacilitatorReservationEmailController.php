<?php

namespace App\Http\Controllers\Facilitator\Account\Reservation;

use App\Mail\ReservationDecisionMail;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class FacilitatorReservationEmailController
{
	public function sendForwardedToCoordinator(Reservation $reservation, User $facilitator): void
	{
		$this->send(
			reservation: $reservation,
			reviewerRole: 'Facilitator',
			decisionLabel: 'Approved by Facilitator',
			subjectPrefix: 'Forwarded for coordinator review',
			bodyMessage: 'Your reservation request has been approved by the facilitator and forwarded to the coordinator for final review.',
			reason: null,
			actorName: $this->buildUserName($facilitator),
		);
	}

	public function sendRejectedToRequester(Reservation $reservation, User $facilitator, string $reason): void
	{
		$this->send(
			reservation: $reservation,
			reviewerRole: 'Facilitator',
			decisionLabel: 'Rejected by Facilitator',
			subjectPrefix: 'Facilitator rejection notice',
			bodyMessage: 'Your reservation request was rejected by the facilitator.',
			reason: $reason,
			actorName: $this->buildUserName($facilitator),
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
