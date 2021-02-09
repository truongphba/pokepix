<?php

namespace App\Http\Requests\API\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreatePostRequest extends FormRequest
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
            'file' => 'required|file_exists',
            'name' => 'required|string|max:255',
            'note' => 'nullable|string'
        ];
    }

    public function withValidator($validator)
    {
        $validator->addExtension('file_exists', function ($attribute, $value, $parameters, $validator) {
            return \File::exists(public_path($value));
        });

        $validator->addReplacer('not_lorem_ipsum', function ($message, $attribute, $rule, $parameters, $validator) {
            return __("The :attribute can't not exists.", compact('attribute'));
        });
    }

    public function messages()
    {
        return [            
          'file.file_exists' => "File not exists!"
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = (new ValidationException($validator))->errors();
        throw new HttpResponseException(
            response()->json(['errors' => $errors], 422)
        );
    }
}
