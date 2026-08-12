<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use App\Services\ContactSubmissionHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Stage 6 addition, not present in the source — the original app only wrote
 * submissions to the DB. Sent synchronously (QUEUE_CONNECTION=sync — this
 * host has no queue worker); ContactSubmissionHandler catches send failures
 * so they never roll back an already-saved submission.
 */
class NewContactSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ContactSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая заявка с сайта — '.$this->submission->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-submission',
            with: [
                // Canonical order, not the decoded JSON array's own key
                // order — see ContactSubmissionHandler::PRIMARY_TECH_KEYS.
                'orderedTechKeys' => [
                    ...ContactSubmissionHandler::PRIMARY_TECH_KEYS,
                    ...ContactSubmissionHandler::EXTRA_TECH_KEYS,
                ],
            ],
        );
    }
}
