<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Listening Party' }}</title>
    <wireui:scripts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/wavesurfer.js@7"></script>
</head>

<body>
    {{ $slot }}
</body>

<style>
    #waveform {
        cursor: pointer;
        position: relative;
    }

    #hover {
        position: absolute;
        left: 0;
        top: 0;
        z-index: 10;
        pointer-events: none;
        height: 100%;
        width: 0;
        mix-blend-mode: overlay;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    #waveform:hover #hover {
        opacity: 1;
    }

    #time,
    #duration {
        position: absolute;
        z-index: 11;
        top: 50%;
        margin-top: -1px;
        transform: translateY(-50%);
        font-size: 11px;
        background: rgba(0, 0, 0, 0.75);
        padding: 2px;
        color: #ddd;
    }

    #time {
        left: 0;
    }

    #duration {
        right: 0;
    }
</style>

</html>