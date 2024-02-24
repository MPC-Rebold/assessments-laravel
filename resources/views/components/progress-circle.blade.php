<div {{ $attributes->merge(['class' => 'relative']) }}>

    <div class="outer">
        <div class="inner text-sm text-slate-600">
            {{ $percentage }}%
        </div>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-0 top-0" viewBox="0 0 160 160">
        <defs>
            <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color:#2D473C;" />
                <stop offset="100%" style="stop-color:#77927D;" />
            </linearGradient>
        </defs>
        <circle cx="80" cy="80" r="70" stroke-linecap="round" transform="rotate(-90 80 80)"
            style="
            fill: none;
            stroke-width: 12.5%;
            stroke-dasharray: 472;
            stroke-dashoffset: 472;
            stroke: url(#gradient);
            animation: anim{{ $percentage }} 500ms ease-out forwards;" />
    </svg>

    <style>
        @keyframes anim{{ $percentage }} {
            to {
                stroke-dashoffset: {{ 472 - ((472 * $percentage) / 100) * 0.93 }};
            }
        }
    </style>
</div>
