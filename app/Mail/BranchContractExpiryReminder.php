<?php

namespace App\Mail;

use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BranchContractExpiryReminder extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Branch $branch,
        public int $daysRemaining = 7
    ) {
    }

    public function envelope(): Envelope
    {
        $location = $this->branch->location ?: 'Your Branch';

        return new Envelope(
            subject: "Contract Expiry Reminder - {$location}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.branch-contract-expiry-reminder',
            with: [
                'branch' => $this->branch,
                'daysRemaining' => $this->daysRemaining,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
