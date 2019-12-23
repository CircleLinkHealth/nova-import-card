<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\Phaxio;

use App\Contracts\Efax;
use Illuminate\Support\Collection;
use Phaxio;

class PhaxioService implements Efax
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
     * PhaxioService constructor.
     *
     * @param Phaxio $phaxio
     */
    public function __construct(Phaxio $phaxio)
    {
        $this->fax = $phaxio;
        $this->options = new Collection();
    }

    public function createFaxFor(string $number): Efax
    {
        $this->options->put('to', $number);

        return $this;
    }
    
    /**
     * Send a fax.
     *
     * https://www.phaxio.com/docs/api/v2.1/faxes/create_and_send_fax
     *
     * @param array $options
     *
     * @return mixed|Phaxio\Fax
     */
    public function send(array $options = [])
    {
        $options = $this->options->merge($options);

        if ( ! $options->has('to')) {
            throw new \InvalidArgumentException('Filed `to` was not specified. Need to knw where to send the fax to.');
        }

        if ($options->has('file')) {
            $options['file'] = $this->prepareFiles($options['file']);
        }

        return $this->fax->faxes()->create($options->all());
    }

    public function setOption(string $name, $value): Efax
    {
        $this->options->put($name, $value);

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

        return $handles;
    }
    
    /**
     * @return Collection
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }
    
    /**
     * @param Collection $options
     *
     * @return PhaxioService
     */
    public function setOptions(Collection $options): PhaxioService
    {
        $this->options = $options;
        
        return $this;
}
}
