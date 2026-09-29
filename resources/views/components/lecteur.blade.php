{{-- Lecteur audio « une seule écoute » (fichier audio, sinon voix de synthèse sur la transcription). --}}
@props(['question', 'ecoutee' => false, 'surDebut' => 'null', 'fond' => 'bg-menthe'])

@php($barres = [34, 18, 26, 10, 14, 14, 34, 10, 24, 10, 14, 26, 26, 14, 24, 14, 26, 10, 14, 24, 10, 26, 10, 24, 10, 18, 30, 26, 18, 14])

<div {{ $attributes->class(['flex flex-col gap-2.5 rounded-[20px] p-4', $fond]) }}
    x-data="lecteur(@js(['source' => $question->urlAudio(), 'transcription' => $question->transcription, 'duree' => $question->duree_audio ?? 30, 'ecoutee' => $ecoutee]), {{ $surDebut }})">
    <div class="flex items-center gap-3.5">
        <button type="button" @click="jouer()" :disabled="enLecture || fini" :aria-label="fini ? 'Enregistrement déjà écouté' : 'Écouter l\'enregistrement'"
            class="flex size-14 shrink-0 items-center justify-center rounded-full bg-foret text-white transition-opacity disabled:opacity-40" aria-label="Écouter l'enregistrement">
            <svg x-show="!enLecture && !fini" class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5l12 7-12 7z"/></svg>
            <svg x-show="enLecture" x-cloak class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
            <svg x-show="fini" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
        </button>
        <div class="flex h-9 flex-1 items-center gap-[3px] overflow-hidden" aria-hidden="true">
            @foreach ($barres as $i => $hauteur)
                <div class="w-1 shrink-0 rounded-sm transition-colors" style="height: {{ $hauteur }}px"
                    :class="progression > {{ $i / count($barres) }} ? 'bg-foret' : 'bg-foret/25'"></div>
            @endforeach
        </div>
    </div>
    <div class="flex justify-between gap-3 text-[13px] text-mousse-fonce">
        <span class="font-bold" x-text="fini ? 'Écoute terminée · lecture bloquée' : (enLecture ? 'Écoute en cours…' : 'Une seule écoute, comme à l\'examen')">Une seule écoute, comme à l'examen</span>
        <span class="shrink-0 whitespace-nowrap tabular-nums" x-text="libelle"></span>
    </div>
</div>
