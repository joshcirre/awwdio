<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use App\Models\ListeningParty;
use App\Models\Episode;
use App\Jobs\ProcessPodcastUrl;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('required')]
    public $startTime;

    #[Validate('required|url')]
    public string $mediaUrl = '';

    public function createListeningParty()
    {
        $this->validate();

        $episode = Episode::create([
            'media_url' => $this->mediaUrl,
        ]);

        $listeningParty = ListeningParty::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->startTime,
        ]);

        ProcessPodcastUrl::dispatch($this->mediaUrl, $listeningParty, $episode);

        return redirect()->route('parties.show', $listeningParty);
    }

    public function with()
    {
        return [
            'listening_parties' => ListeningParty::where('is_active', true)->with('episode.podcast')->get(),
        ];
    }
}; ?>
<div class="min-h-screen bg-slate-50 flex flex-col">
    <!-- Top half: Create Listening Party form -->
    <div class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-lg">
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-center mb-6">Create Listening Party</h2>
                <form wire:submit='createListeningParty' class="space-y-6">
                    <x-input wire:model='name' placeholder="Listening Party Name" />
                    <x-input wire:model='mediaUrl' placeholder="Podcast RSS Feed URL"
                        description="Entering the RSS Feed URL will grab the latest episode" />
                    <x-datetime-picker wire:model='startTime' placeholder="Listening Party Start Time" :min="now()->subDays(1)"
                        requires-confirmation />
                    <x-button type="submit" class="w-full">Create Listening Party</x-button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bottom half: Scrollable list of existing parties -->
    <div class="h-1/2 bg-gray-100 p-4 overflow-hidden">
        <div class="max-w-lg mx-auto">
            <h3 class="text-xl font-semibold mb-4">Ongoing Listening Parties</h3>
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-y-auto max-h-[calc(50vh-8rem)]">
                    @foreach ($listening_parties as $listeningParty)
                        <a href="{{ route('parties.show', $listeningParty) }}" class="block">
                            <div
                                class="flex items-center justify-between p-4 border-b border-gray-200 hover:bg-gray-50 transition duration-150 ease-in-out">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $listeningParty->episode->podcast->artwork_url }}"
                                            class="w-10 h-10 rounded-full" alt="Podcast Artwork" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-gray-900 truncate">
                                            {{ $listeningParty->name }}
                                        </div>
                                        <div class="text-sm text-gray-500 truncate">
                                            {{ $listeningParty->episode->title }}
                                        </div>
                                        <div class="text-xs text-gray-400 truncate">
                                            {{ $listeningParty->episode->podcast->title }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1" x-data="{
                                            startTime: '{{ $listeningParty->start_time->toIso8601String() }}',
                                            countdownText: '',
                                            updateCountdown() {
                                                const start = new Date(this.startTime).getTime();
                                                const now = new Date().getTime();
                                                const distance = start - now;

                                                if (distance < 0) {
                                                    this.countdownText = 'Started';
                                                } else {
                                                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                                    this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                                }
                                            }
                                        }"
                                            x-init="updateCountdown();
                                            setInterval(() => updateCountdown(), 1000);">
                                            Starts in: <span x-text="countdownText"></span>
                                        </div>
                                    </div>
                                </div>
                                <x-button primary class="ml-4">Join</x-button>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
