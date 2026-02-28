{{-- Filtro de período reutilizável para páginas de estatísticas --}}
<div class="flex flex-wrap items-end gap-3 mb-6 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
    <div>
        <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Período</label>
        <select
            wire:model.live="period"
            class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
        >
            <option value="7">Últimos 7 dias</option>
            <option value="14">Últimos 14 dias</option>
            <option value="30">Últimos 30 dias</option>
            <option value="month">Mês atual</option>
            <option value="custom">Personalizado</option>
        </select>
    </div>

    @if ($period === 'custom')
        <div>
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">De</label>
            <input type="date" wire:model.live="startDate"
                   class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
        </div>
        <div>
            <label class="text-xs font-medium text-gray-500 dark:text-gray-400 block mb-1">Até</label>
            <input type="date" wire:model.live="endDate"
                   class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500">
        </div>
    @endif
</div>
