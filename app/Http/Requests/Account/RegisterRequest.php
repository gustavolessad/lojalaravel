<?php

namespace App\Http\Requests\Account;

use App\Rules\ValidCnpj;
use App\Rules\ValidCpf;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Remove máscaras dos campos antes da validação.
     * CPF e CNPJ são armazenados como dígitos puros (sem pontos, traço ou barra).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'cpf'    => $this->cpf    ? preg_replace('/\D/', '', $this->cpf)    : null,
            'cnpj'   => $this->cnpj   ? preg_replace('/\D/', '', $this->cnpj)   : null,
            'mobile' => $this->mobile ? preg_replace('/\D/', '', $this->mobile) : null,
            'phone'  => $this->phone  ? preg_replace('/\D/', '', $this->phone)  : null,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'type'     => ['required', 'in:pf,pj'],
            'email'    => ['required', 'email', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => ['nullable', 'string', 'max:11'],
            'mobile'   => ['nullable', 'string', 'max:11'],
        ];

        if ($this->input('type') === 'pf') {
            $rules['name']       = ['required', 'string', 'max:255'];
            $rules['cpf']        = ['required', 'digits:11', new ValidCpf(), 'unique:customers,cpf'];
            $rules['birth_date'] = ['nullable', function ($attribute, $value, $fail) {
                if (! $value) return;
                if (! preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
                    $fail('Informe a data no formato DD/MM/AAAA.');
                    return;
                }
                $d = \DateTime::createFromFormat('d/m/Y', $value);
                if (! $d || $d->format('d/m/Y') !== $value) {
                    $fail('Data de nascimento inválida.');
                    return;
                }
                if ($d >= new \DateTime('today')) {
                    $fail('A data de nascimento deve ser anterior a hoje.');
                }
            }];
        }

        if ($this->input('type') === 'pj') {
            $rules['name']               = ['required', 'string', 'max:255'];
            $rules['company_name']       = ['required', 'string', 'max:255'];
            $rules['cnpj']               = ['required', 'digits:14', new ValidCnpj(), 'unique:customers,cnpj'];
            $rules['responsible_name']   = ['required', 'string', 'max:255'];
            $rules['state_registration'] = ['nullable', 'string', 'max:30'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'type.required'               => 'Selecione o tipo de pessoa.',
            'type.in'                     => 'Tipo de pessoa inválido.',
            'name.required'               => 'O nome é obrigatório.',
            'email.required'              => 'O e-mail é obrigatório.',
            'email.email'                 => 'Informe um e-mail válido.',
            'email.unique'                => 'Este e-mail já está cadastrado.',
            'password.required'           => 'A senha é obrigatória.',
            'password.confirmed'          => 'A confirmação de senha não confere.',
            'cpf.required'                => 'O CPF é obrigatório.',
            'cpf.digits'                  => 'O CPF deve conter 11 dígitos.',
            'cpf.unique'                  => 'Este CPF já está cadastrado.',
            'company_name.required'       => 'A razão social é obrigatória.',
            'cnpj.required'               => 'O CNPJ é obrigatório.',
            'cnpj.digits'                 => 'O CNPJ deve conter 14 dígitos.',
            'cnpj.unique'                 => 'Este CNPJ já está cadastrado.',
            'responsible_name.required'   => 'O nome do responsável é obrigatório.',
        ];
    }
}
