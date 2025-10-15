<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Exception;

class MailService extends BaseService
{
    /**
     * Gửi một email.
     *
     * @param string
     * @param Mailable
     * @return void
     */
    public function send(string $recipientEmail, Mailable $mailable): void
    {
        try {
            Mail::to($recipientEmail)->send($mailable);
        } catch (Exception $e) {

            Log::error('Email sending failed to ' . $recipientEmail . ': ' . $e->getMessage());
        }
    }
}
