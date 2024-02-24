<div {{ $attributes->merge(['class' => 'skill']) }}>

    <div class="outer">
        <div class="inner text-sm text-slate-600">
            {{ $percentge }}%
        </div>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-0 top-0" viewBox="0 0 160 160">
        <circle cx="80" cy="80" r="70" stroke-linecap="round" transform="rotate(-90 80 80)" />
    </svg>

    <style>
        @keyframes anim {
            to {
                stroke-dashoffset: {{ 472 - ((472 * $percentge) / 100) * 0.93 }};
            }
        }
    </style>
</div>
