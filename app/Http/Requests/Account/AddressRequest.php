<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label'      => ['nullable', 'string', 'max:50'],
            'cep'        => ['required', 'string', 'size:9'],
            'street'     => ['required', 'string', 'max:255'],
            'number'     => ['required', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:100'],
            'district'   => ['required', 'string', 'max:100'],
            'city'       => ['required', 'string', 'max:100'],
            'state'      => ['required', 'string', 'size:2'],
            'country'    => ['required', 'string', 'size:2'],
            'is_default' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'cep.required'     => 'O CEP é obrigatório.',
            'cep.size'         => 'O CEP deve ter 9 caracteres (00000-000).',
            'street.required'  => 'O logradouro é obrigatório.',
            'number.required'  => 'O número é obrigatório.',
            'district.required'=> 'O bairro é obrigatório.',
            'city.required'    => 'A cidade é obrigatória.',
            'state.required'   => 'O estado é obrigatório.',
            'state.size'       => 'Use a sigla do estado (ex: SP).',
        ];
    }
}
