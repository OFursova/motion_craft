<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="relative pb-[56.25%]">
        <iframe
            src="https://www.youtube.com/embed/{{ $getState() }}?enablejsapi=1&rel=0&modestbranding=1"
            id="player"
            type="text/html"
            title="Video player"
            class="absolute top-0 left-0 w-full h-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen></iframe>
    </div>
</x-dynamic-component>

@script
<script>
    const tag = document.createElement('script');

    tag.src = "https://www.youtube.com/iframe_api";
    let firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

    let player;
    window.onYouTubeIframeAPIReady = function() {
        player = new YT.Player('player', {
            events: {
                'onStateChange': onPlayerStateChange
            }
        });

        console.log('Player was mounted', player);
    }

    function onPlayerStateChange(event) {
        // PlayerStates: PLAYING PAUSED ENDED CUED UNSTARTED BUFFERING

        if (event.data == YT.PlayerState.ENDED) {
            console.log('Video has ended');

            $wire.dispatch('episode-ended', {
                episode: '{{ $getRecord()->id }}'
            });
        }
    }
</script>
@endscript
