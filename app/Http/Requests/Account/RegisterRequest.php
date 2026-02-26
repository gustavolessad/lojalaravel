<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'type'     => ['required', 'in:pf,pj'],
            'email'    => ['required', 'email', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'mobile'   => ['nullable', 'string', 'max:20'],
        ];

        if ($this->input('type') === 'pf') {
            $rules['name']       = ['required', 'string', 'max:255'];
            $rules['cpf']        = ['required', 'string', 'size:14', 'unique:customers,cpf'];
            $rules['birth_date'] = ['nullable', 'date', 'before:today'];
        }

        if ($this->input('type') === 'pj') {
            $rules['name']             = ['required', 'string', 'max:255'];
            $rules['company_name']     = ['required', 'string', 'max:255'];
            $rules['cnpj']             = ['required', 'string', 'size:18', 'unique:customers,cnpj'];
            $rules['responsible_name'] = ['required', 'string', 'max:255'];
            $rules['state_registration'] = ['nullable', 'string', 'max:30'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required'             => 'Selecione o tipo de pessoa.',
            'type.in'                   => 'Tipo de pessoa inválido.',
            'name.required'             => 'O nome é obrigatório.',
            'email.required'            => 'O e-mail é obrigatório.',
            'email.email'               => 'Informe um e-mail válido.',
            'email.unique'              => 'Este e-mail já está cadastrado.',
            'password.required'         => 'A senha é obrigatória.',
            'password.confirmed'        => 'A confirmação de senha não confere.',
            'cpf.required'              => 'O CPF é obrigatório.',
            'cpf.size'                  => 'O CPF deve ter 14 caracteres (com pontos e traço).',
            'cpf.unique'                => 'Este CPF já está cadastrado.',
            'company_name.required'     => 'A razão social é obrigatória.',
            'cnpj.required'             => 'O CNPJ é obrigatório.',
            'cnpj.size'                 => 'O CNPJ deve ter 18 caracteres.',
            'cnpj.unique'               => 'Este CNPJ já está cadastrado.',
            'responsible_name.required' => 'O nome do responsável é obrigatório.',
        ];
    }
}
