<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        
        // Admin can edit any post, users can only edit their own posts
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
                Rule::in([0, 1])
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
            'content.required' => 'Post content is required.',
            'content.min' => 'Post content must be at least 10 characters.',
            'content.max' => 'Post content cannot exceed 10,000 characters.',
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
            $tags = preg_replace('/\s*,\s*/', ',', $tags);
            $tags = trim($tags, ', ');
            $this->merge(['tags' => $tags]);
        }
    }
}