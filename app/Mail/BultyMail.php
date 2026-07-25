<?php

namespace App\Mail;

use App\Models\Bulty;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class BultyMail extends Mailable
{
    use Queueable, SerializesModels;

    public Bulty $bulty;

    public function __construct(Bulty $bulty)
    {
        $this->bulty = $bulty;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.mailers.smtp.username') ?: config('mail.from.address');

        return new Envelope(
            subject: "Bilty Details - {$this->bulty->lr_no}",
            from: new Address($fromAddress, config('mail.from.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'admin.email.bulty-mail',
        );
    }

    public function attachments(): array
    {
        $bulty = $this->bulty->load([
            'branch', 'consignor', 'consignee', 'vehicle', 'driver',
            'originCity', 'destinationCity', 'bultyItems', 'company', 'bultyDetail',
        ]);

        $pdf = Pdf::loadView('admin.transport.bulties.pdf', compact('bulty'));
        $pdf->setPaper('A4', 'portrait');

        return [
            Attachment::fromData(fn () => $pdf->output(), "Bilty-{$bulty->lr_no}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}
