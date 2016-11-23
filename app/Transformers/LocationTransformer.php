<?php

namespace App\Transformers;

use App\Location;
use League\Fractal\TransformerAbstract;

/**
 * Class LocationTransformer
 * @package namespace App\Transformers;
 */
class LocationTransformer extends TransformerAbstract
{

    /**
     * Transform the \Location entity
     * @param \Location $model
     *
     * @return array
     */
    public function transform(Location $model)
    {
        return [
            'id' => (int)$model->id,
            'name' => $model->name,
            'phone' => $model->phone,
            'address_line_1' => $model->address_line_1,
            'address_line_2' => $model->address_line_2,
            'city' => $model->city,
            'state' => $model->state,
            'timezone' => $model->timezone,
            'postal_code' => $model->postal_code,
            'billing_code' => $model->billing_code,
        ];
    }
}
