<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Phaxio;

use App\Contracts\Efax;
use App\Contracts\FaxableNotification;
use App\FaxLog;
use Illuminate\Support\Collection;
use Phaxio;

class PhaxioFaxService implements Efax
{
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

        $response = $this->fax->faxes()->create($options->all());

        if ($this->shouldLogResponse($response->getArrayCopy())) {
            $log = $this->logResponse($response);
        }

        return $response;
    }

    public function sendNotification($notifiable, FaxableNotification &$notification, array $options = [])
    {
        $options = array_merge($notification->toFax($notifiable), $options);

        if (optional($notification)->id) {
            $options['tag[notification_id]'] = $notification->id;
        }

        $fax = $this->send($options);
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

    private function logResponse(Phaxio\Fax $response)
    {
        $response = $response->retrieve();

        return FaxLog::create(
            [
                'fax_id'    => $response['id'],
                'status'    => $response['status'],
                'direction' => $response['direction'],
                'response'  => $response,
            ]
        );
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

    private function shouldLogResponse(array $response)
    {
        return array_key_exists('id', $response) && ! empty($response['id']);
    }
}
