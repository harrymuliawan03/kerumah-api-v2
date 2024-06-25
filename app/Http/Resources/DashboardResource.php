<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
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
            'total_properties' => $this['total_properties'],
            'total_units' => $this['total_units'],
            'available_units' => $this['available_units'],
            'filled_units' => $this['filled_units'],
            'late_payment_count' => $this['late_payment_count'],
            'labels' => $this['labels'],
            'list_payment_late' => $this['list_payment_late'],
            'list_empty_properties' => $this['list_empty_properties'],
        ];
    }
}
