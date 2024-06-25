<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UnitUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() != null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => 'nullable|in:empty,filled,late',
            // 'type' => 'nullable|in:perumahan,kontrakan,kostan',
            'periode_pembayaran' => 'nullable|in:year,month',
            'purchase_type' => 'nullable|in:sewa,angsuran',
            'tenor' => 'nullable',
            'nama_penghuni' => 'nullable|string|max:100',
            'no_identitas' => 'nullable|integer',
            'alamat' => 'nullable|string',
            'provinsi' => 'nullable|string',
            'kota' => 'nullable|string',
            'kode_pos' => 'nullable|integer',
            'tanggal_mulai' => 'nullable|date',
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            "errors" => $validator->getMessageBag()
        ], 400));
    }
}
