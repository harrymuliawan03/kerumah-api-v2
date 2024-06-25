<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class KontrakanUpdateRequest extends FormRequest
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
            // 'id' => ['required'],
            'name' => ['nullable', 'max:100'],
            'alamat' => ['nullable', 'max:100'],
            'provinsi' => ['nullable', 'max:100'],
            'kota' => ['nullable', 'max:100'],
            'kode_pos' => ['nullable', 'max:100'],
            'periode_pembayaran' => ['nullable', 'max:100'],
            'kode_unit' => ['nullable', 'max:5'],
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            "errors" => $validator->getMessageBag()
        ], 400));
    }
}
