<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'requester_name' => ['required', 'string', 'max:255'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'assignment_mode' => ['required', Rule::in(['automatic', 'manual'])],
            'responsible_id' => [
                'exclude_unless:assignment_mode,manual',
                'required_if:assignment_mode,manual',
                'integer',
                Rule::exists('responsibles', 'id')->where('active', true),
            ],
        ];
    }
}
