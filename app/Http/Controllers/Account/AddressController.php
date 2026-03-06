<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\AddressRequest;
use App\Models\Customer\CustomerAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AddressController extends Controller
{
    private function customer()
    {
        return Auth::guard('customer')->user();
    }

    public function index(): View
    {
        $addresses = $this->customer()->addresses()->orderByDesc('is_default')->orderBy('id')->get();

        return view('account.addresses.index', compact('addresses'));
    }

    public function create(): View
    {
        return view('account.addresses.form', ['address' => new CustomerAddress()]);
    }

    public function store(AddressRequest $request): RedirectResponse
    {
        $customer = $this->customer();
        $data = $request->validated();

        if ($request->boolean('is_default')) {
            $customer->addresses()->update(['is_default' => false]);
        }

        // Se for o primeiro endereço, define como padrão automaticamente
        if ($customer->addresses()->count() === 0) {
            $data['is_default'] = true;
        }

        $customer->addresses()->create($data);

        return redirect()->route('account.addresses.index')
            ->with('success', 'Endereço adicionado com sucesso!');
    }

    public function edit(CustomerAddress $address): View
    {
        $this->authorizeAddress($address);

        return view('account.addresses.form', compact('address'));
    }

    public function update(AddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $customer = $this->customer();
        $data = $request->validated();

        if ($request->boolean('is_default')) {
            $customer->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()->route('account.addresses.index')
            ->with('success', 'Endereço atualizado com sucesso!');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $wasDefault = $address->is_default;
        $address->delete();

        // Se era o padrão, promove o próximo
        if ($wasDefault) {
            $this->customer()->addresses()->first()?->update(['is_default' => true]);
        }

        return redirect()->route('account.addresses.index')
            ->with('success', 'Endereço removido.');
    }

    public function setDefault(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $customer = $this->customer();
        $customer->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('account.addresses.index')
            ->with('success', 'Endereço padrão atualizado!');
    }

    private function authorizeAddress(CustomerAddress $address): void
    {
        abort_if($address->customer_id !== $this->customer()->id, 403);
    }
}
