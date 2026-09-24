<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gender' => $this->gender,
            'province_code' => $this->province_code,
            'province_name' => $this->province_name,
            'ward_code' => $this->ward_code,
            'ward_name' => $this->ward_name,
            'address_detail' => $this->address_detail,
            'is_admin' => (bool) $this->is_admin,
            'created_at' => $this->created_at,
        ];
    }
}
