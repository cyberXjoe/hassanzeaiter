<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\CategoryField;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); 
    }

    public function rules(): array
    {
        $rules = [
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['required', 'string'],
            'price'          => ['required', 'numeric_strict', 'min:0'],
            'category_id'    => ['required', 'exists:categories,id'],
            'dynamic_fields' => ['required', 'array'],
        ];

        $categoryId = $this->input('category_id');
        if (!$categoryId) {
            return $rules;
        }

        $categoryFields = CategoryField::with('options')
            ->where('category_id', $categoryId)
            ->get();

        foreach ($categoryFields as $field) {
            $handle = $field->handle;
            $fieldRules = [];

            // Required / nullable
            if ($field->required) {
                $fieldRules[] = 'required';
                $fieldRules[] = 'present';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Type-based validation
            switch ($field->type) {
                case 'number':
                    $fieldRules[] = 'numeric_strict';
                    if (isset($field->meta['minValue'])) $fieldRules[] = 'min:' . $field->meta['minValue'];
                    if (isset($field->meta['maxValue'])) $fieldRules[] = 'max:' . $field->meta['maxValue'];
                    break;

                case 'text':
                case 'textarea':
                    $fieldRules[] = 'string';
                    if (isset($field->meta['minLength'])) $fieldRules[] = 'min:' . $field->meta['minLength'];
                    if (isset($field->meta['maxLength'])) $fieldRules[] = 'max:' . $field->meta['maxLength'];
                    break;

                case 'select':
                case 'radio':
                    $choices = $field->options->pluck('value')->toArray();
                    if (!empty($choices)) $fieldRules[] = Rule::in($choices);
                    break;

                case 'checkbox':
                    $fieldRules[] = 'array';
                    $choices = $field->options->pluck('value')->toArray();
                    if (!empty($choices)) $rules["dynamic_fields.$handle.*"] = [Rule::in($choices)];
                    break;
            }

            $rules["dynamic_fields.$handle"] = $fieldRules;
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->input('category_id');
            if (!$categoryId) return;

            $validHandles = CategoryField::where('category_id', $categoryId)
                ->pluck('handle')
                ->toArray();

            $submitted = array_keys($this->input('dynamic_fields', []));

            $invalid = array_diff($submitted, $validHandles);

            if (!empty($invalid)) {
                $validator->errors()->add(
                    'dynamic_fields',
                    'Invalid fields provided: ' . implode(', ', $invalid)
                );
            }
        });
    }

    public function messages(): array
    {
        $messages = [
            'title.required'          => 'Title is required.',
            'description.required'    => 'Description is required.',
            'price.required'          => 'Price is required.',
            'price.numeric_strict'    => 'Price must be a numeric value, not a string.',
            'price.min'               => 'Price must be at least 0.',
            'category_id.required'    => 'Category is required.',
            'category_id.exists'      => 'Selected category does not exist.',
            'dynamic_fields.required' => 'Dynamic fields are required.',
            'dynamic_fields.array'    => 'Dynamic fields must be an object.',
        ];

        $categoryId = $this->input('category_id');
        if (!$categoryId) return $messages;

        $categoryFields = CategoryField::where('category_id', $categoryId)->get();

        foreach ($categoryFields as $field) {
            $handle = $field->handle;
            $label  = $field->name;

            $messages["dynamic_fields.$handle.required"] = "$label is required.";
            $messages["dynamic_fields.$handle.present"]  = "$label must be provided.";

            if ($field->type === 'number') {
                $messages["dynamic_fields.$handle.numeric_strict"] = "$label must be a numeric value, not a string.";
                $messages["dynamic_fields.$handle.min"]            = "$label is too small.";
                $messages["dynamic_fields.$handle.max"]            = "$label is too large.";
            }

            if (in_array($field->type, ['select', 'radio', 'checkbox'])) {
                $messages["dynamic_fields.$handle.in"]       = "Invalid value selected for $label.";
                $messages["dynamic_fields.$handle.*.in"]     = "Invalid value selected for $label.";
            }
        }

        return $messages;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->toArray();
        $flatErrors = [];
        foreach ($errors as $field => $messages) {
            $flatErrors[$field] = implode(' ', $messages);
        }

        throw new HttpResponseException(
            response()->json(
                array_merge(
                    generate_response([], 0, true, "Validation failed"),
                    ['errors' => $flatErrors]
                ),
                422
            )
        );
    }
}
