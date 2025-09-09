<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:5'
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000'
            ],
            'status' => [
                'required',
                'integer',
                Rule::in([0, 1]) // 0 = draft, 1 = published
            ],
            'category' => [
                'nullable',
                'string',
                'max:100'
            ],
            'tags' => [
                'nullable',
                'string',
                'max:500'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Post title is required.',
            'title.min' => 'Post title must be at least 5 characters.',
            'title.max' => 'Post title cannot exceed 255 characters.',
            'description.required' => 'Post content is required.',
            'description.min' => 'Post content must be at least 10 characters.',
            'description.max' => 'Post content cannot exceed 10,000 characters.',
            'status.required' => 'Post status is required.',
            'status.in' => 'Invalid post status selected.',
            'category.max' => 'Category cannot exceed 100 characters.',
            'tags.max' => 'Tags cannot exceed 500 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Clean and format tags
        if ($this->has('tags')) {
            $tags = $this->input('tags');
            $tags = preg_replace('/\s*,\s*/', ',', $tags); // Remove spaces around commas
            $tags = trim($tags, ', '); // Remove leading/trailing commas and spaces
            $this->merge(['tags' => $tags]);
        }
    }
}