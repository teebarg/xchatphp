<?php

namespace App\Http\Resources;

use App\Traits\ResponseTrait;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseResource extends JsonResource
{
    use ResponseTrait;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
