<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PriceChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $url;
    public float $oldPrice;
    public float $newPrice;
    public string $currency;

    public function __construct(string $url, float $oldPrice, float $newPrice, string $currency = 'USD')
    {
        $this->url = $url;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->currency = $currency;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('The price has changed!'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'email.price_changed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
