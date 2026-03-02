<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit()
    {
        $customer = auth('customer')->user();

        return view('account.profile.edit', compact('customer'));
    }

    public function update(Request $request)
    {
        $customer = auth('customer')->user();

        $rules = [
            'mobile' => ['nullable', 'string', 'max:20'],
            'phone'  => ['nullable', 'string', 'max:20'],
        ];

        if ($customer->isPF()) {
            $rules['name']       = ['required', 'string', 'max:120'];
            $rules['rg']         = ['nullable', 'string', 'max:20'];
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
        } else {
            $rules['company_name']       = ['required', 'string', 'max:120'];
            $rules['responsible_name']   = ['nullable', 'string', 'max:120'];
            $rules['state_registration'] = ['nullable', 'string', 'max:30'];
        }

        $validated = $request->validate($rules);

        // Converte birth_date de DD/MM/AAAA → Y-m-d antes de persistir
        if (! empty($validated['birth_date'])) {
            $d = \DateTime::createFromFormat('d/m/Y', $validated['birth_date']);
            $validated['birth_date'] = $d ? $d->format('Y-m-d') : null;
        }

        // Remove formatação de máscara dos campos de telefone antes de salvar
        if (isset($validated['mobile'])) {
            $validated['mobile'] = preg_replace('/\D/', '', $validated['mobile']) ?: null;
        }
        if (isset($validated['phone'])) {
            $validated['phone'] = preg_replace('/\D/', '', $validated['phone']) ?: null;
        }

        $customer->update($validated);

        return back()->with('success', 'Dados atualizados com sucesso!');
    }

    public function updatePassword(Request $request)
    {
        $customer = auth('customer')->user();

        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'password.min'              => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.confirmed'        => 'A confirmação da senha não confere.',
        ]);

        if (! Hash::check($request->current_password, $customer->password)) {
            return back()->withErrors(['current_password' => 'Senha atual incorreta.'])->withInput();
        }

        $customer->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Senha alterada com sucesso!');
    }
}
