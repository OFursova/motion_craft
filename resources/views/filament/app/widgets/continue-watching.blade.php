<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex justify-between gap-x-3">
            <div class="flex items-center gap-x-3">
                <img src="{{$cover}}" class="max-w-none object-cover object-center"
                     style="height:2.5rem; width:2.5rem"/>
                <p class="text-sm leading-6">{{str($course->title)->limit(40)->toString()}}</p>
            </div>
            <x-filament::button color="primary" icon="heroicon-s-play" href="{{$url}}" tag="a" size="sm" class="max-h-[32px]">
                {{ __('Continue') }}
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
