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
    currentTime: 0,
    isLive: false,
    isPlaying: false,
    isReady: false,
    countdownText: '',
    startTimestamp: {{ $listeningParty->start_time->timestamp }},

    initializeAudioPlayer() {
        this.audio = this.$refs.audioPlayer;
        this.audio.addEventListener('loadedmetadata', () => {
            this.isLoading = false;
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
            if (!this.isLive) {
                this.isLive = true;
                this.countdownText = 'Live';
                if (this.isReady) {
                    this.playAudio();
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
        // Attempt to play and immediately pause to satisfy autoplay policies
        this.audio.play().then(() => {
            this.audio.pause();
        }).catch(error => {
            console.error('Could not prepare audio:', error);
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

        <div x-show="!isLive" class="flex items-center justify-center min-h-screen bg-gray-100">
            <div class="p-8 bg-white rounded-lg shadow-md max-w-2xl w-full">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <x-avatar src="{{ $listeningParty->episode->podcast->artwork_url }}" size="xl"
                            rounded="sm" alt="Podcast Artwork" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[0.9rem] font-semibold truncate text-slate-900">
                            {{ $listeningParty->name }}</p>
                        <div class="mt-0.8">
                            <p class="max-w-xs text-sm truncate text-slate-600">
                                {{ $listeningParty->episode->title }}</p>
                            <p class="text-[0.7rem] tracking-tighter uppercase text-slate-400">
                                {{ $listeningParty->episode->podcast->title }}</p>
                        </div>
                        <div class="mt-4 text-sm text-slate-600">
                            Starts in: <span x-text="countdownText"></span>
                        </div>
                        <button x-show="!isReady" @click="joinAndBeReady()"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mt-4">
                            Join and be ready
                        </button>
                        <div x-show="isReady" class="mt-4 text-green-600">
                            Ready to start! The audio will play automatically when the countdown finishes.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="isLive" x-cloak>
            <div>{{ $listeningParty->episode->podcast->title }}</div>
            <div>{{ $listeningParty->episode->title }}</div>
            <div>Current Time: <span x-text="formatTime(currentTime)"></span></div>
            <div>Start Time: {{ $listeningParty->start_time }}</div>
            <div x-show="isLoading">Loading...</div>

            <button x-show="!isPlaying" @click="playAudio()"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 mt-4">
                Join and Listen
            </button>
        </div>
    @endif
</div>
