<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $url;
    protected $username;
    protected $password;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($url, $username, $password)
    {
        //
        $this->url = $url;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from('support@expressonline.com')
                    ->markdown('emails.customer.created', [
                        'url' => $this->url,
                        'username' => $this->username,
                        'password' => $this->password
                    ]);
    }
}
