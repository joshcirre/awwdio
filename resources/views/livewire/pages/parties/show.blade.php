<?php

use Livewire\Volt\Component;
use App\Models\ListeningParty;

new class extends Component {
    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty)
    {
        $this->listeningParty = $listeningParty->load('episode.podcast');
    }
}; ?>

<div class="flex flex-col max-h-screen min-h-screen m-auto">
    @if ($listeningParty->end_time === null)
        <div class="flex items-center justify-center p-4" wire:poll.5s>
            Creating a new listening party hold tight...
        </div>
    @else
        <div x-data="{
            audio: null,
            isLoading: true,
            currentTime: 0,
            startTimestamp: {{ $listeningParty->start_time->timestamp }},
            serverTime: {{ now()->timestamp }},
        
            initializeAudio() {
                this.audio = this.$refs.audioPlayer;
                this.audio.addEventListener('loadedmetadata', () => {
                    this.isLoading = false;
                    this.checkAndPlay();
                });
                this.audio.addEventListener('timeupdate', () => {
                    this.currentTime = this.audio.currentTime;
                });
            },
        
            checkAndPlay() {
                const now = Math.floor(Date.now() / 1000);
                const timeDiff = now - this.serverTime;
                const adjustedNow = now - timeDiff;
        
                if (adjustedNow >= this.startTimestamp) {
                    const elapsedTime = adjustedNow - this.startTimestamp;
                    this.audio.currentTime = elapsedTime;
                    this.audio.play().catch(error => console.error('Playback failed:', error));
                } else {
                    // If it's not time to start yet, we'll wait and check again
                    setTimeout(() => this.checkAndPlay(), 1000);
                }
            },
        
            formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const remainingSeconds = Math.floor(seconds % 60);
                return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
            }
        }" x-init="initializeAudio()">
            <audio x-ref="audioPlayer" :src="'{{ $listeningParty->episode->media_url }}'" preload="auto"></audio>

            <div>{{ $listeningParty->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time }}</div>
            <div>Server Time: {{ now() }}</div>

            <div x-show="isLoading">Loading...</div>
        </div>
    @endif
</div>
