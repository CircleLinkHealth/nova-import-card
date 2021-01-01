<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Services\Phaxio;

use CircleLinkHealth\Core\Contracts\Efax;
use CircleLinkHealth\Core\Contracts\FaxableNotification;
use Illuminate\Support\Collection;
use Phaxio;

class PhaxioFaxService implements Efax
{
    const EVENT_STATUS_IN_PROGRESS     = 'inprogress';
    const EVENT_STATUS_SUCCESS         = 'success';
    const EVENT_TYPE_FAX_COMPLETED     = 'fax_completed';
    const EVENT_TYPE_TRANSMITTING_PAGE = 'transmitting_page';
    /**
     * @var Phaxio
     */
    public $fax;

    /**
     * @var Collection
     */
    private $options;

    /**
     * PhaxioFaxService constructor.
     */
    public function __construct(Phaxio $phaxio)
    {
        $this->fax     = $phaxio;
        $this->options = new Collection();
    }

    public function createFaxFor(string $number): Efax
    {
        $this->options->put('to', $number);

        return $this;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * Send a fax.
     *
     * https://www.phaxio.com/docs/api/v2.1/faxes/create_and_send_fax
     *
     * @return mixed|Phaxio\Fax
     */
    public function send(array $options = [])
    {
        $options = $this->options->merge($options);

        if (isUnitTestingEnv()) {
            $options['direction'] = 'received';
        }

        if ( ! $options->has('to')) {
            throw new \InvalidArgumentException('Filed `to` was not specified. Need to knw where to send the fax to.');
        }

        if ($options->has('file')) {
            $options['file'] = $this->prepareFiles($options['file']);
        }

        return $this->fax->faxes()->create($options->all());
    }

    public function sendNotification($notifiable, FaxableNotification $notification, array $options = [])
    {
        $options = array_merge($notification->toFax($notifiable), $options);

        if (optional($notification)->id) {
            $options['tag[notification_id]'] = $notification->id;
        }

        return $this->send($options);
    }

    public function setOption(string $name, $value): Efax
    {
        $this->options->put($name, $value);

        return $this;
    }

    public function setOptions(Collection $options): PhaxioFaxService
    {
        $this->options = $options;

        return $this;
    }

    private function prepareFiles($files)
    {
        if ( ! $files) {
            return [];
        }

        if ( ! is_array($files)) {
            $files = [$files];
        }

        $handles = [];

        foreach ($files as $file) {
            if (file_exists($file)) {
                $handles[] = fopen($file, 'r');
            }
        }

        if (1 == count($handles)) {
            return $handles[0];
        }

        return $handles;
    }
}
