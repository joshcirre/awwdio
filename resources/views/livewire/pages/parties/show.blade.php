<?php

use Livewire\Volt\Component;
use App\Models\ListeningParty;

new class extends Component {
    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty)
    {
        $this->listeningParty = $listeningParty->load(['episode.podcast']);
    }
}; ?>

@assets
    <script src="https://unpkg.com/wavesurfer.js@7" defer></script>
@endassets
<div>
    {{ $listeningParty->name }}
    {{ $listeningParty->start_time }}
    {{ $listeningParty->episode->title }}
    {{ $listeningParty->episode->podcast->title }}
    <img src="{{ $listeningParty->episode->podcast->artwork_url }}" class="size-28" />
    <div id="waveform" class="max-w-xl mx-auto">
        <div id="time">0:00</div>
        <div id="duration">0:00</div>
    </div>
</div>
@script
    <script>
        const canvas = document.createElement('canvas')
        const ctx = canvas.getContext('2d')

        // Define the waveform gradient
        const gradient = ctx.createLinearGradient(0, 0, 0, canvas.height * 1.35)
        gradient.addColorStop(0, '#656666') // Top color
        gradient.addColorStop((canvas.height * 0.7) / canvas.height, '#656666') // Top color
        gradient.addColorStop((canvas.height * 0.7 + 1) / canvas.height, '#ffffff') // White line
        gradient.addColorStop((canvas.height * 0.7 + 2) / canvas.height, '#ffffff') // White line
        gradient.addColorStop((canvas.height * 0.7 + 3) / canvas.height, '#B1B1B1') // Bottom color
        gradient.addColorStop(1, '#B1B1B1') // Bottom color

        // Define the progress gradient
        const progressGradient = ctx.createLinearGradient(0, 0, 0, canvas.height * 1.35)
        progressGradient.addColorStop(0, '#EE772F') // Top color
        progressGradient.addColorStop((canvas.height * 0.7) / canvas.height, '#EB4926') // Top color
        progressGradient.addColorStop((canvas.height * 0.7 + 1) / canvas.height, '#ffffff') // White line
        progressGradient.addColorStop((canvas.height * 0.7 + 2) / canvas.height, '#ffffff') // White line
        progressGradient.addColorStop((canvas.height * 0.7 + 3) / canvas.height, '#F6B094') // Bottom color
        progressGradient.addColorStop(1, '#F6B094') // Bottom color

        // Create the waveform
        const wavesurfer = WaveSurfer.create({
            container: '#waveform',
            waveColor: gradient,
            progressColor: progressGradient,
            interact: false,
            barWidth: 2,
            url: '{{ $listeningParty->episode->media_url }}',
        })

        let audioContext;

        function attemptAutoplay() {
            audioContext = new(window.AudioContext || window.webkitAudioContext)();
            if (audioContext.state === 'suspended') {
                audioContext.resume();
            }

            wavesurfer.play()
                .then(() => {
                    console.log('Autoplay started!');
                })
                .catch((error) => {
                    console.log('Autoplay was prevented:', error);
                    // If autoplay is prevented, we can start muted
                    wavesurfer.setMuted(true);
                    wavesurfer.play();
                });
        }

        wavesurfer.on('ready', function() {
            attemptAutoplay();
        });

        const formatTime = (seconds) => {
            const minutes = Math.floor(seconds / 60)
            const secondsRemainder = Math.round(seconds) % 60
            const paddedSeconds = `0${secondsRemainder}`.slice(-2)
            return `${minutes}:${paddedSeconds}`
        }

        const timeEl = document.querySelector('#time')
        const durationEl = document.querySelector('#duration')
        wavesurfer.on('decode', (duration) => (durationEl.textContent = formatTime(duration)))
        wavesurfer.on('timeupdate', (currentTime) => (timeEl.textContent = formatTime(currentTime)))
    </script>
@endscript
