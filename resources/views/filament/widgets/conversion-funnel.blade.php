<x-filament-widgets::widget>
    <x-filament::section heading="Funil de conversão">

        @php $steps = $this->getFunnelData(); @endphp

        <div class="space-y-3">
            @foreach ($steps as $i => $step)
                @php
                    $colors = ['indigo','blue','violet','purple','green'];
                    $c = $colors[$i] ?? 'gray';
                @endphp

                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $step['label'] }}</span>
                        <span class="text-gray-500 dark:text-gray-400">
                            {{ number_format($step['count']) }}
                            @if ($i > 0)
                                <span class="ml-1 text-{{ $c }}-600 dark:text-{{ $c }}-400 font-semibold">{{ $step['pct'] }}%</span>
                            @endif
                        </span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                        <div
                            class="h-2.5 rounded-full bg-{{ $c }}-500 transition-all duration-700"
                            style="width: {{ min($step['pct'], 100) }}%"
                        ></div>
                    </div>
                </div>
            @endforeach
        </div>

    </x-filament::section>
</x-filament-widgets::widget>
