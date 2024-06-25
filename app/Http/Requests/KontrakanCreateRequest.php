<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class KontrakanCreateRequest extends FormRequest
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
            'name' => ['required', 'max:100'],
            'alamat' => ['required', 'max:100'],
            'provinsi' => ['required', 'max:100'],
            'kota' => ['required', 'max:100'],
            'kode_pos' => ['required', 'max:100'],
            'jml_unit' => ['required'],
            'periode_pembayaran' => ['required'],
            'kode_unit' => ['required', 'max:5'],
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response([
            "errors" => $validator->getMessageBag()
        ], 400));
    }
}
