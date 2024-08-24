<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="h-4 w-full bg-gray-100 dark:bg-gray-800 rounded-full shadow-inner overflow-hidden">
        <div class="flex h-full items-center justify-center bg-primary-600 dark:bg-primary-500 rounded-r-full shadow-inner text-xs font-bold text-white"
            style="width: {{ $getProgress() }}%;">
            {{ $getProgress() }}%
        </div>
    </div>
</x-dynamic-component>
