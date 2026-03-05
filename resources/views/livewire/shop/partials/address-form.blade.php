{{-- Identificação + Nome do destinatário --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Identificação do endereço</label>
        <input wire:model="addrLabel" type="text" placeholder="Ex: Casa, Trabalho..."
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Nome do destinatário *</label>
        <input wire:model="addrName" type="text" placeholder="Quem vai receber"
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('addrName') border-red-400 @enderror">
        @error('addrName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

{{-- CEP (20%) + Rua/Logradouro (60%) + Número (20%) --}}
<div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
    <div class="sm:col-span-1">
        <label class="block text-xs font-medium text-gray-700 mb-1">CEP *</label>
        <input wire:model.blur="addrZip" type="text" placeholder="00000-000" maxlength="9"
               wire:change="lookupZip" x-mask="99999-999"
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('addrZip') border-red-400 @enderror">
        @error('addrZip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-3">
        <label class="block text-xs font-medium text-gray-700 mb-1">Rua/Logradouro *</label>
        <input wire:model="addrStreet" type="text" placeholder="Rua, Avenida, etc."
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('addrStreet') border-red-400 @enderror">
        @error('addrStreet') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div class="sm:col-span-1">
        <label class="block text-xs font-medium text-gray-700 mb-1">Número *</label>
        <input wire:model="addrNumber" type="text" placeholder="123"
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('addrNumber') border-red-400 @enderror">
        @error('addrNumber') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Complemento (100%) --}}
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">Complemento</label>
    <input wire:model="addrComplement" type="text" placeholder="Apto, Bloco, Sala..."
           class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none">
</div>

{{-- Bairro + Cidade + Estado (33% cada) --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Bairro *</label>
        <input wire:model="addrDistrict" type="text"
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('addrDistrict') border-red-400 @enderror">
        @error('addrDistrict') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Cidade *</label>
        <input wire:model="addrCity" type="text"
               class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none @error('addrCity') border-red-400 @enderror">
        @error('addrCity') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Estado *</label>
        <select wire:model="addrState"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-0 focus:border-black focus:outline-none bg-white @error('addrState') border-red-400 @enderror">
            <option value="">UF</option>
            @foreach(['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'] as $uf)
                <option value="{{ $uf }}">{{ $uf }}</option>
            @endforeach
        </select>
        @error('addrState') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
    </div>
</div>
