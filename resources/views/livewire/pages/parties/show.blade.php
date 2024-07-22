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

<div x-data="{
    audio: null,
    isLoading: true,
    isLive: false,
    isPlaying: false,
    countdownText: '',
    isReady: false,
    currentTime: 0,
    startTimestamp: {{ $listeningParty->start_time->timestamp }},

    initializeAudioPlayer() {
        this.audio = this.$refs.audioPlayer;
        this.audio.addEventListener('loadedmetadata', () => {
            this.isLoading = false
            this.checkAndUpdate();
        });

        this.audio.addEventListener('timeupdate', () => {
            this.currentTime = this.audio.currentTime;
        });

        this.audio.addEventListener('play', () => {
            this.isPlaying = true;
        });

        this.audio.addEventListener('pause', () => {
            this.isPlaying = false;
        });
    },

    checkAndUpdate() {
        const now = Math.floor(Date.now() / 1000);
        const timeUntilStart = this.startTimestamp - now;

        if (timeUntilStart <= 0) {
            if (!this.isPlaying) {
                this.isLive = true;
                if (this.isReady) {
                    this.audio.play().catch(error => console.error('Playback failed:', error));
                }
            }
        } else {
            const days = Math.floor(timeUntilStart / 86400);
            const hours = Math.floor((timeUntilStart % 86400) / 3600);
            const minutes = Math.floor((timeUntilStart % 3600) / 60);
            const seconds = timeUntilStart % 60;
            this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
            setTimeout(() => this.checkAndUpdate(), 1000);
        }
    },

    playAudio() {
        const now = Math.floor(Date.now() / 1000);
        const elapsedTime = Math.max(0, now - this.startTimestamp);
        this.audio.currentTime = elapsedTime;
        this.audio.play().catch(error => {
            console.error('Playback failed:', error);
            this.isPlaying = false;
        });
    },

    joinAndBeReady() {
        this.isReady = true;
        this.audio.play().then(() => {
            this.audio.pause();
        }).catch(error => {
            console.error('Playback failed:', error);
            this.isPlaying = false;
        });
    },

    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
}" x-init="initializeAudioPlayer()">
    @if ($listeningParty->end_time === null)
        <div class="flex items-center justify-center p-6 font-serif text-sm" wire:poll.5s>
            Creating your <span class="font-bold">{{ $listeningParty->name }}</span> listening party...
        </div>
    @else
        <audio x-ref="audioPlayer" :src="'{{ $listeningParty->episode->media_url }}'" preload="auto"></audio>

        <div x-show="!isLive" class="flex items-center justify-center min-h-screen bg-emerald-50">
            <div class="w-full max-w-2xl p-8 bg-white rounded-lg shadow-lg">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}" size="xl"
                            rounded="sm" alt="Podcast Artwork" />
                    </div>
                    <div class="flex items-center justify-between w-full">
                        <div class="flex-1 min-w-0">
                            <p class="text-[0.9rem] font-semibold truncate text-slate-900">
                                {{ $listeningParty->name }}</p>
                            <div class="mt-0.8">
                                <p class="max-w-xs text-sm truncate text-slate-600">
                                    {{ $listeningParty->episode->title }}</p>
                                <p class="text-[0.7rem] tracking-tighter uppercase text-slate-400">
                                    {{ $listeningParty->podcast->title }}</p>
                            </div>
                            <div class="mt-1 text-xs text-slate-600">

                            </div>
                        </div>
                        <p class="text-lg text-slate-700 font-bolder">
                            Starts in: <span x-text="countdownText"></span>
                        </p>
                    </div>
                </div>
                <x-button x-show="!isReady" class="w-full mt-8" @click="joinAndBeReady()">Join and Be Ready</x-button>
                <h2 x-show="isReady"
                    class="mt-8 font-serif text-lg tracking-tight text-center text-slate-900 font-bolder">
                    Ready to start the awwd.io party! Stay tuned. 🫶</h2>
            </div>
        </div>
        <div x-show="isLive">
            <div>{{ $listeningParty->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time }}</div>
            <div x-show="isLoading">Loading...</div>
        </div>
    @endif
</div>
