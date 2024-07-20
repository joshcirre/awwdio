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

<div>
    {{ $listeningParty->name }}
    {{ $listeningParty->start_time }}
    {{ $listeningParty->episode->title }}
    {{ $listeningParty->episode->podcast->title }}
    <img src="{{ $listeningParty->episode->podcast->artwork_url }}" class="size-28" />
</div>
