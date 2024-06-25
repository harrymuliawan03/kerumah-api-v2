<?php

namespace App\Http\Requests;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
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
            'email' => ['required', 'email', 'max:100'],
            'password' => ['required', 'max:100'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->getMessageBag()->all();

        // Convert error messages into arrays if they are strings
        foreach ($errors as &$error) {
            if (!is_array($error)) {
                $error = [$error];
            }
        }

        throw new HttpResponseException(response([
            "success" => false,
            "message" => array_merge(...array_values($errors)),
            "data" => null
        ], 400));
        // return ApiResponse::error($validator->getMessageBag(), 400);
    }
}
