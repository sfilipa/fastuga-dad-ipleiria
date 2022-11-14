<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'phone' => $this->phone,
            'points' => $this->points,
            'nif' => $this->nif,
            'default_payment_type' => $this->default_payment_type,
            'default_payment_reference' => $this->default_payment_reference,
            'custom' => $this->custom,
            'user_id' => new UserResource(User::findOrFail($this->user_id))
        ];
    }
}
