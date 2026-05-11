<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturaPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public $factura;
    public $pdfOutput;

    /**
     * Create a new message instance.
     */
    public function __construct($factura, $pdfOutput)
    {
        $this->factura = $factura;
        $this->pdfOutput = $pdfOutput;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Su Factura Electrónica - ' . $this->factura->numero_factura,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfOutput, $this->factura->numero_factura . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
