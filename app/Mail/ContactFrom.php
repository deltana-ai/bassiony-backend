<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Contact;
use Illuminate\Mail\Mailables\Address;

class ContactFrom extends Mailable
{
    use Queueable, SerializesModels;
    public $contact;
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
         $this->contact = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
      return new Envelope(
          from: new Address(config('mail.from.address'), config('app.name')),
          replyTo: [new Address($this->contact['email'],$this->contact['email'])],

          subject: 'new contact from contact form '
      );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
          view: 'emails.contact_from',
          with: ['data' => $this->contact]
        );

    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
