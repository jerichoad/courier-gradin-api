<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourierResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'courier_id' => $this->courier_id,
            'courier_code' => $this->courier_code,
            'courier_name' => $this->courier_name,
            'courier_phone' => $this->courier_phone,
            'courier_email' => $this->courier_email,
            'courier_level' => $this->courier_level,
            'courier_address' => $this->courier_address,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
