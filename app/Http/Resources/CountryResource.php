<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CountryResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'country_name' => $this->country_name,
            'country_alias' => $this->country_alias,
            $this->mergeWhen($this->states->count(), [
                'states' => StateResource::collection($this->states),
            ]),
        ];
    }
}
