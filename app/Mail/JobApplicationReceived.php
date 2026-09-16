<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [$this->application->email],
            subject: 'Application: '.$this->application->jobPost->title.' - '.$this->application->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'mail.job-application');
    }

    public function attachments(): array
    {
        return [Attachment::fromStorageDisk('local', $this->application->cv_path)->as($this->application->cv_name)];
    }
}
