<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry)
    {
    }

    public function envelope(): Envelope
    {
        $from = $this->enquiry->organisation ?: $this->enquiry->name;

        return new Envelope(
            replyTo: [$this->enquiry->email],
            subject: $this->enquiry->typeLabel().': '.$from,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.enquiry');
    }
}
