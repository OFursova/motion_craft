<x-filament-panels::page>
    <div class="grid grid-cols-3 gap-4" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
        <div class="col-span-2" style="grid-column: span 2 / span 2;">
            {{ $this->watchInfolist }}
        </div>
        <div class="col-span-1" style="grid-column: span 1 / span 1;">
        {{ $this->lessonsInfolist }}
        </div>
    </div>
</x-filament-panels::page>
