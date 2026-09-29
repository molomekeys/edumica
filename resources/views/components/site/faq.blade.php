{{-- Accordéon de questions fréquentes. $questions : liste de [question, réponse] ; $ouverte : -1 pour tout fermer. --}}
@props(['questions', 'prefixe' => 'faq', 'ouverte' => 0])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2.5 md:gap-3']) }} x-data="{ ouverte: @js($ouverte) }">
    @foreach ($questions as $i => [$question, $reponse])
        <div class="rounded-2xl bg-brume px-4 md:rounded-[20px] md:px-7">
            <h3>
                <button type="button" @click="ouverte = ouverte === {{ $i }} ? null : {{ $i }}" :aria-expanded="ouverte === {{ $i }}" aria-controls="{{ $prefixe }}-{{ $i }}"
                    class="flex min-h-14 w-full items-center justify-between gap-3 text-left text-base font-bold text-foret md:min-h-[68px] md:text-lg">
                    {{ $question }}
                    <x-icone nom="bas" class="size-5 shrink-0 transition-transform duration-200" x-bind:class="ouverte === {{ $i }} && 'rotate-180'" />
                </button>
            </h3>
            <div id="{{ $prefixe }}-{{ $i }}" x-show="ouverte === {{ $i }}" x-collapse @if ($i !== $ouverte) x-cloak @endif>
                <p class="pb-4 text-[15px] leading-[1.55] text-mousse md:pb-6 md:text-base md:leading-relaxed">{{ $reponse }}</p>
            </div>
        </div>
    @endforeach
</div>
