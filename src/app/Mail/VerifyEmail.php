<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use SerializesModels;

    public $user;
    public $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\User $user
     * @param string $verificationUrl
     * @return void
     */
    public function __construct($user, $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     *
     * @return \Illuminate\Mail\Mailable
     */
    public function build()
    {
        return $this->subject('メール認証のお知らせ')
            ->view('emails.verify-email')
            ->with([
                'name' => $this->user->name,
                'verificationUrl' => $this->verificationUrl,
            ]);
    }
}
