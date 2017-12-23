<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WeeklyProviderReport extends Mailable
{
    use Queueable, SerializesModels;


    /**
     * @var User
     */
    protected $provider;

    /**
     * The data passed to the view
     *
     * For an example @see: EmailWeeklyProviderReport, method handle
     * @var array
     */
    protected $data;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $provider, array $data)
    {
        $this->provider = $provider;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('sales.by-provider.report')
            ->with($this->data)
            ->to($this->provider->email)
            ->from('notifications@careplanmanager.com', 'CircleLink Health')
            ->subject('Dr. ' . $this->provider->last_name . '\'s CCM Weekly Summary');
    }
}
