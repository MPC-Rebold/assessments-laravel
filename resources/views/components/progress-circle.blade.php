<div {{ $attributes->merge(['class' => 'relative']) }}>

    <div class="outer">
        <div class="inner text-sm text-slate-600">
            {{ $percentage }}%
        </div>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-0 top-0" viewBox="0 0 160 160">
        <circle cx="80" cy="80" r="70" stroke-linecap="round" transform="rotate(-90 80 80)"
            style="
            fill: none;
            stroke-width: 12.5%;
            stroke-dasharray: 472;
            stroke-dashoffset: 472;
            stroke: #47695b;
            animation: anim{{ $percentage }} 0.5s ease-out forwards;" />
    </svg>

    <style>
        @keyframes anim{{ $percentage }} {
            to {
                stroke-dashoffset: {{ 472 - ((472 * $percentage) / 100) * 0.93 }};
            }
        }
    </style>
</div>
