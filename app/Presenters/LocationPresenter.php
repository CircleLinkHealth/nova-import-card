<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Presenters;

use App\Transformers\LocationTransformer;
use Prettus\Repository\Presenter\FractalPresenter;

/**
 * Class LocationPresenter.
 */
class LocationPresenter extends FractalPresenter
{
    /**
     * Transformer.
     *
     * @return \League\Fractal\TransformerAbstract
     */
    public function getTransformer()
    {
        return new LocationTransformer();
    }
}
