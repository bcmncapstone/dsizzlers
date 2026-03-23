<?php

namespace App\Mail;

use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BranchContractExpiredNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Branch $branch
    ) {
    }

    public function envelope(): Envelope
    {
        $location = $this->branch->location ?: 'Your Branch';

        return new Envelope(
            subject: "Contract Expired - {$location}"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.branch-contract-expired-notification',
            with: [
                'branch' => $this->branch,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
