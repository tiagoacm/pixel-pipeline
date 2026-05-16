<?php

namespace App\Http\Requests\Opportunities;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpportunityRequest extends FormRequest
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
     *
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [
                'sometimes',
                'string',
                'in:nova,validacao,aguardando_cliente,aprovada,em_execucao,finalizada,reprovada,cancelada',
            ],
            'client_name' => ['required', 'string', 'max:255'],
            'segment' => ['required', 'string', 'max:255'],
            'commercial_contact' => ['required', 'string', 'max:255'],
            'commercial_phone' => ['required', 'string', 'max:30'],
            'technical_contact' => ['required', 'string', 'max:255'],
            'technical_phone' => ['required', 'string', 'max:30'],
            'opportunity_description' => ['required', 'string'],
            'responsible_user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
