{{-- Accordéon de questions fréquentes. $questions : liste de [question, réponse] ; $ouverte : -1 pour tout fermer. --}}
@props(['questions', 'prefixe' => 'faq', 'ouverte' => 0])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2.5 md:gap-3']) }} x-data="{ ouverte: @js($ouverte) }">
    @foreach ($questions as $i => [$question, $reponse])
        <div class="rounded-2xl px-4 transition-colors duration-300 md:rounded-[20px] md:px-7" :class="ouverte === {{ $i }} ? 'bg-menthe' : 'bg-brume hover:bg-menthe/60'">
            <h3>
                <button type="button" @click="ouverte = ouverte === {{ $i }} ? null : {{ $i }}" :aria-expanded="ouverte === {{ $i }}" aria-controls="{{ $prefixe }}-{{ $i }}"
                    class="flex min-h-14 w-full items-center justify-between gap-3 text-start text-base font-bold text-foret md:min-h-[68px] md:text-lg">
                    {{ __($question) }}
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full transition-colors duration-300" :class="ouverte === {{ $i }} ? 'bg-white' : 'bg-transparent'">
                        <x-icone nom="bas" class="size-5 transition-transform duration-300 ease-ressort" x-bind:class="ouverte === {{ $i }} && 'rotate-180'" />
                    </span>
                </button>
            </h3>
            <div id="{{ $prefixe }}-{{ $i }}" x-show="ouverte === {{ $i }}" x-collapse.duration.350ms @if ($i !== $ouverte) x-cloak @endif>
                <p class="pb-4 text-[15px] leading-[1.55] text-mousse md:pb-6 md:text-base md:leading-relaxed">{{ __($reponse) }}</p>
            </div>
        </div>
    @endforeach
</div>
