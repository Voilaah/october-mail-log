<?php

namespace Voilaah\MailLog\Models;

use Illuminate\Mail\Message;
use Model;
use October\Rain\Mail\Mailer;
use Swift_Attachment;
use Swift_Message;

/**
 * Model
 */
class MailLog extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'voilaah_maillog_log';

    /**
     * @var array Guarded fields
     */
    protected $guarded = [];

    /**
     * @var array Attribute names to encode and decode using JSON.
     */
    protected $jsonable = ['attachments'];

    protected $hiddenWhenEmpty = [
        'attachments',
        'cc',
        'bcc',
    ];

    /**
     * @param Mailer  $mailer
     * @param string  $view
     * @param Message $message
     *
     * @return $this
     */
    public function createFromMailerSendEvent(\October\Rain\Mail\Mailer $mailer, string $view, \Illuminate\Mail\Message $message)
    {
        try {
            // Retrieve recipient's addresses
            $to = $this->formatEmails($message->getTo());
            $cc = $this->formatEmails($message->getCc());
            $bcc = $this->formatEmails($message->getBcc());
            $from = $this->formatEmails($message->getFrom());

            // Retrieve the email subject
            $subject = $message->getSubject();

            // Retrieve the Symfony Message instance
            $symfonyMessage = $message->getSymfonyMessage();

            // Initialize variables for HTML and plain text body
            $htmlBody = null;
            $plainTextBody = null;

            // Check if the body is multipart
            $body = $symfonyMessage->getBody();

            if (
                $body instanceof \Symfony\Component\Mime\Part\Multipart\MixedPart ||
                $body instanceof \Symfony\Component\Mime\Part\Multipart\AlternativePart
            ) {
                // Loop through the parts to extract plain text and HTML content
                foreach ($body->getParts() as $part) {
                    if ($part->getMediaType() === 'text') {
                        if ($part->getMediaSubtype() === 'plain') {
                            $plainTextBody = $part->bodyToString();
                        } elseif ($part->getMediaSubtype() === 'html') {
                            $htmlBody = $part->bodyToString();
                        }
                    }
                }
            } elseif ($body instanceof \Symfony\Component\Mime\Part\TextPart) {
                // Handle single-part emails
                if ($body->getMediaType() === 'text' && $body->getMediaSubtype() === 'plain') {
                    $plainTextBody = $body->getBody();
                } elseif ($body->getMediaType() === 'text' && $body->getMediaSubtype() === 'html') {
                    $htmlBody = $body->getBody();
                }
            }

            // \Log::info('Email sent', [
            //     'to' => $to,
            //     'subject' => $subject,
            //     'html_body' => $htmlBody,
            //     'plain_text_body' => $plainTextBody ?? null,
            // ]);

            $this->fill([
                'to'          => $to,
                'cc'          => $cc,
                'bcc'         => $bcc,
                'from'        => $from,
                'subject'     => $subject,
                'body'        => $plainTextBody ?? null,
                'template'    => $view,
                'sent'        => true,
                // 'attachments' => $this->extractAttachments($mail),
            ])->save();

            return $this;
        } catch (\Throwable $th) {
            \Log::error('Mail log sent', $th->getTraceAsString());
        }
    }

    public function getAttachmentsCountAttribute()
    {
        if ($this->attachments === null || $this->attachments === 0) {
            return 0;
        } else {
            return count($this->attachments);
        }
    }

    public function filterFields($fields, $context = null)
    {
        foreach ($this->hiddenWhenEmpty as $field) {
            if (empty($fields->{$field}->value)) {
                $fields->{$field}->hidden = true;
            }
        }
    }

    /**
     * @param $contacts
     *
     * @return string|void
     */
    private function formatEmails($contacts)
    {
        if (is_array($contacts)) {
            $emails = collect($contacts)
                ->map(fn($item) => $item->getAddress());

            return implode(", ", $emails->toArray());
        }
    }

    /**
     * @param Swift_Message $mail
     *
     * @return \October\Rain\Support\Collection
     */
    private function extractAttachments(Swift_Message $mail)
    {
        return collect($mail->getChildren())->filter(function ($item) {
            return $item instanceof Swift_Attachment;
        })->map(function (Swift_Attachment $attachment) {
            return [
                'name' => $attachment->getFilename(),
            ];
        });
    }
}
