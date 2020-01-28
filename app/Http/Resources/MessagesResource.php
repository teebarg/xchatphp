<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessagesResource extends BaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'message' => $this->message,
            'to' => $this->to,
            'status' => $this->status,
            'from' => new UserResource($this->user),
            'time' => $this->created_at->diffForHumans()
        ];
    }
}
