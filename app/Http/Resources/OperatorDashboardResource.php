<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OperatorDashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'operator' => $this['operator'],

            'summary' => $this['summary'],

        ];
    }
}