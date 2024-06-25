<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ListPaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $nameProperti = null;
        switch ($this->unit->type) {
            case 'perumahan':
                $nameProperti = optional($this->unit->perumahan)->name;
                break;
            case 'kontrakan':
                $nameProperti = optional($this->unit->kontrakan)->name;
                break;
            case 'kostan':
                $nameProperti = optional($this->unit->kostan)->name;
                break;
        }

        return [
            'tipe' => $this->unit->type,
            'kode_unit' => $this->unit->name,
            'nama' => $nameProperti,
            'status' => $this->isLate,
            'tanggal_pembayaran' => $this->payment_date,
        ];
    }
}
