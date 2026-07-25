<?php

namespace App\Mail;

use App\Models\Letterhead;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class LetterheadMail extends Mailable
{
    use Queueable, SerializesModels;

    public Letterhead $letterhead;

    public function __construct(Letterhead $letterhead)
    {
        $this->letterhead = $letterhead;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.mailers.smtp.username') ?: config('mail.from.address');

        return new Envelope(
            subject: $this->letterhead->subject ?: "Letter from {$this->letterhead->company->name}",
            from: new Address($fromAddress, $this->letterhead->company->name ?? config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.email.letterhead-mail',
        );
    }

    public function attachments(): array
    {
        $letterhead = $this->letterhead->load('company');

        $pdf = Pdf::loadView('admin.letterheads.pdf', compact('letterhead'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'chroot' => public_path(),
        ]);

        $filename = "Letter-{$letterhead->letter_no}.pdf";

        return [
            Attachment::fromData(fn () => $pdf->output(), $filename)
                ->withMime('application/pdf'),
        ];
    }
}
