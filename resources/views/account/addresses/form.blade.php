@extends('layouts.account')

@section('title', $address->exists ? 'Editar endereço' : 'Novo endereço')

@section('page-content')
    <div class="max-w-2xl">
        <div class="mb-4">
            <h1 class="font-medium text-gray-900">
                {{ $address->exists ? 'Editar endereço' : 'Novo endereço' }}
            </h1>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form
                method="POST"
                action="{{ $address->exists ? route('account.addresses.update', $address) : route('account.addresses.store') }}"
                x-data="addressForm()"
                x-init="init()"
            >
                @csrf
                @if ($address->exists)
                    @method('PUT')
                @endif

                {{-- Identificação --}}
                <div class="mb-6">
                    <label for="label" class="block text-sm font-medium text-gray-700 mb-1">
                        Identificação <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input
                        type="text"
                        id="label"
                        name="label"
                        value="{{ old('label', $address->label) }}"
                        placeholder="Ex: Casa, Trabalho, Apt..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900"
                    >
                </div>

                {{-- CEP --}}
                <div class="mb-4">
                    <label for="cep" class="block text-sm font-medium text-gray-700 mb-1">
                        CEP
                    </label>
                    <div class="flex gap-3">
                        <input
                            type="text"
                            id="cep"
                            name="cep"
                            x-model="cep"
                            x-on:blur="buscarCep()"
                            value="{{ old('cep', $address->cep) }}"
                            maxlength="9"
                            placeholder="00000-000"
                            class="w-40 rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900 @error('cep') border-red-400 @enderror"
                        >
                        <button
                            type="button"
                            x-on:click="buscarCep()"
                            x-bind:disabled="buscando"
                            class="text-sm text-gray-600 hover:text-gray-900 disabled:opacity-50"
                        >
                            <span x-show="!buscando">Buscar CEP</span>
                            <span x-show="buscando">Buscando...</span>
                        </button>
                    </div>
                    @error('cep')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p x-show="cepError" x-text="cepError" class="mt-1 text-xs text-red-600"></p>
                </div>

                {{-- Logradouro + Número --}}
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div class="col-span-2">
                        <label for="street" class="block text-sm font-medium text-gray-700 mb-1">Logradouro</label>
                        <input
                            type="text"
                            id="street"
                            name="street"
                            x-model="street"
                            value="{{ old('street', $address->street) }}"
                            placeholder="Rua, Av, Alameda..."
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900 @error('street') border-red-400 @enderror"
                        >
                        @error('street')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="number" class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                        <input
                            type="text"
                            id="number"
                            name="number"
                            value="{{ old('number', $address->number) }}"
                            placeholder="123"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900 @error('number') border-red-400 @enderror"
                        >
                        @error('number')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Complemento --}}
                <div class="mb-4">
                    <label for="complement" class="block text-sm font-medium text-gray-700 mb-1">
                        Complemento <span class="text-gray-400 font-normal">(opcional)</span>
                    </label>
                    <input
                        type="text"
                        id="complement"
                        name="complement"
                        value="{{ old('complement', $address->complement) }}"
                        placeholder="Apto, Bloco, Casa..."
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900"
                    >
                </div>

                {{-- Bairro + Cidade + Estado --}}
                <div class="grid grid-cols-3 gap-4 mb-4">
                    <div>
                        <label for="district" class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                        <input
                            type="text"
                            id="district"
                            name="district"
                            x-model="district"
                            value="{{ old('district', $address->district) }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900 @error('district') border-red-400 @enderror"
                        >
                        @error('district')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            x-model="city"
                            value="{{ old('city', $address->city) }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900 @error('city') border-red-400 @enderror"
                        >
                        @error('city')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                        <select
                            id="state"
                            name="state"
                            x-model="state"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm  focus:outline-none focus:ring-2 focus:ring-gray-900 @error('state') border-red-400 @enderror"
                        >
                            <option value="">UF</option>
                            @foreach (['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                                <option value="{{ $uf }}" {{ old('state', $address->state) === $uf ? 'selected' : '' }}>
                                    {{ $uf }}
                                </option>
                            @endforeach
                        </select>
                        @error('state')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="country" value="BR">

                {{-- Endereço padrão --}}
                <div class="mb-6 flex items-center gap-2">
                    <input
                        type="checkbox"
                        id="is_default"
                        name="is_default"
                        value="1"
                        {{ old('is_default', $address->is_default) ? 'checked' : '' }}
                        class="h-4 w-4 text-gray-900 border-gray-300 rounded"
                    >
                    <label for="is_default" class="text-sm text-gray-700">
                        Usar como endereço padrão
                    </label>
                </div>

                {{-- Ações --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                    <button
                        type="submit"
                        class="bg-gray-900 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-gray-800 transition-colors"
                    >
                        {{ $address->exists ? 'Salvar alterações' : 'Adicionar endereço' }}
                    </button>
                    <a
                        href="{{ route('account.addresses.index') }}"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function addressForm() {
    return {
        cep: '{{ old('cep', $address->cep) }}',
        street: '{{ old('street', $address->street) }}',
        district: '{{ old('district', $address->district) }}',
        city: '{{ old('city', $address->city) }}',
        state: '{{ old('state', $address->state) }}',
        buscando: false,
        cepError: '',

        init() {},

        async buscarCep() {
            const cepLimpo = this.cep.replace(/\D/g, '');
            if (cepLimpo.length !== 8) return;

            this.buscando = true;
            this.cepError = '';

            try {
                const res = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
                const data = await res.json();

                if (data.erro) {
                    this.cepError = 'CEP não encontrado.';
                    return;
                }

                this.street   = data.logradouro || this.street;
                this.district = data.bairro || this.district;
                this.city     = data.localidade || this.city;
                this.state    = data.uf || this.state;

                // Formata o CEP com máscara
                this.cep = cepLimpo.replace(/^(\d{5})(\d{3})$/, '$1-$2');

                // Foca no campo número após o preenchimento
                document.getElementById('number').focus();
            } catch {
                this.cepError = 'Erro ao buscar o CEP. Tente novamente.';
            } finally {
                this.buscando = false;
            }
        },
    };
}
</script>
@endpush
