<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'requester_name' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'responsible_id' => [
                'required',
                'integer',
                Rule::exists('responsibles', 'id')->where('active', true),
            ],
        ];
    }
}
