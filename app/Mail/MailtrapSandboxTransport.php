<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

/**
 * Symfony Mailer transport custom que envía emails a Mailtrap Sandbox vía API HTTP.
 *
 * ¿Por qué? Railway bloquea SMTP outbound. La API de Mailtrap usa HTTPS (puerto 443)
 * que siempre está abierto. Así Laravel puede mandar mails desde producción.
 *
 * Endpoint: https://sandbox.api.mailtrap.io/api/send/{inboxId}
 * Auth: header Api-Token
 *
 * Se registra en AppServiceProvider::boot() vía Mail::extend('mailtrap_sandbox', ...).
 */
class MailtrapSandboxTransport extends AbstractTransport
{
    public function __construct(
        private string $apiToken,
        private string $inboxId
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $payload = [
            'from' => $this->addressToArray($email->getFrom()[0] ?? null),
            'to'   => array_map([$this, 'addressToArray'], $email->getTo()),
            'subject' => $email->getSubject() ?? '(sin asunto)',
        ];

        if ($text = $email->getTextBody()) {
            $payload['text'] = $text;
        }
        if ($html = $email->getHtmlBody()) {
            $payload['html'] = $html;
        }

        if ($cc = $email->getCc()) {
            $payload['cc'] = array_map([$this, 'addressToArray'], $cc);
        }
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = array_map([$this, 'addressToArray'], $bcc);
        }
        if ($replyTo = $email->getReplyTo()) {
            $payload['reply_to'] = $this->addressToArray($replyTo[0]);
        }

        $url = "https://sandbox.api.mailtrap.io/api/send/{$this->inboxId}";

        $response = Http::withHeaders([
            'Api-Token'    => $this->apiToken,
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->timeout(15)->post($url, $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Mailtrap Sandbox API error ({$response->status()}): {$response->body()}"
            );
        }
    }

    private function addressToArray($address): array
    {
        if (!$address) {
            return ['email' => 'no-reply@saborgestion.com'];
        }
        $arr = ['email' => $address->getAddress()];
        if ($name = $address->getName()) {
            $arr['name'] = $name;
        }
        return $arr;
    }

    public function __toString(): string
    {
        return 'mailtrap-sandbox-api';
    }
}
