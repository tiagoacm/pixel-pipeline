<?php

namespace App\Http\Requests\Opportunities;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOpportunityStatusRequest extends FormRequest
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
                'required',
                'string',
                'in:nova,validacao,aguardando_cliente,aprovada,em_execucao,finalizada,reprovada,cancelada',
            ],
        ];
    }
}
