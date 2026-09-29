<?php

use App\Models\Epreuve;
use App\Models\Question;
use App\Support\GuideEpreuve;
use App\Support\Nclc;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public Epreuve $epreuve;

    public function mount(Epreuve $epreuve): void
    {
        $this->epreuve = $epreuve;
    }

    /** @return array<string, mixed> */
    #[Computed]
    public function guide(): array
    {
        return GuideEpreuve::pour($this->epreuve->code);
    }

    /** Une question d'exemple, pour les épreuves qui ont des quiz. */
    #[Computed]
    public function exemple(): ?Question
    {
        return $this->epreuve->questions()->first();
    }

    #[Computed]
    public function autres()
    {
        return Epreuve::whereKeyNot($this->epreuve->id)->orderBy('ordre')->get();
    }

    public function render()
    {
        return $this->view()->title($this->epreuve->nom);
    }
};
?>

@php
    $guide = $this->guide;
    $exemple = $this->exemple;
    $code = $epreuve->code;
    $surVingt = in_array($code, ['eo', 'ee']);
@endphp

<x-site.page>
    <x-site.hero :titre="$epreuve->nom" :badge="$epreuve->format" :fil="[route('epreuves') => 'Épreuves']">
        <x-slot:intro>{{ $guide['accroche'] }}</x-slot:intro>
        <x-slot:actions>
            @if ($exemple)
                <a href="{{ route('quiz', $epreuve) }}" wire:navigate class="flex h-14 items-center justify-center rounded-2xl bg-foret px-[30px] text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">Faire un quiz gratuit</a>
            @else
                <a href="{{ route('tests-blancs') }}" wire:navigate class="flex h-14 items-center justify-center rounded-2xl bg-foret px-[30px] text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">Passer un test blanc</a>
            @endif
            <a href="#conseils" class="flex h-[52px] items-center justify-center gap-2 rounded-2xl border-[1.5px] border-foret px-[26px] text-base font-bold text-foret hover:bg-foret/5 md:h-[58px] md:gap-2.5 md:text-lg">
                <x-icone nom="boussole" class="size-[18px] md:size-5" />Nos conseils
            </a>
        </x-slot:actions>
        <x-slot:aside class="grid grid-cols-2 gap-2.5 md:gap-3">
            @foreach ($guide['chiffres'] as $i => [$valeur, $legende])
                <div class="flex flex-col gap-1 rounded-2xl bg-white p-4 md:rounded-[22px] md:p-6">
                    @if ($i === 0)
                        <div class="mb-1 flex size-10 items-center justify-center rounded-full bg-vert text-white md:size-12"><x-icone :nom="$epreuve->icone" class="size-5 md:size-6" /></div>
                    @endif
                    <div class="font-titre text-2xl leading-none whitespace-nowrap md:text-[32px]">{{ $valeur }}</div>
                    <div class="text-[13px] text-lichen md:text-[15px]">{{ $legende }}</div>
                </div>
            @endforeach
        </x-slot:aside>
    </x-site.hero>

    {{-- Déroulé --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
        <div class="flex flex-col gap-2.5">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ $guide['titre_etapes'] }}</h2>
            <p class="max-w-[720px] text-base leading-normal text-mousse md:text-lg md:leading-relaxed">{{ $guide['intro_etapes'] }}</p>
        </div>

        <ol class="grid gap-3.5 md:gap-5 lg:grid-cols-3">
            @foreach ($guide['etapes'] as $i => [$repere, $meta, $titre, $texte, $sujet])
                <li class="flex flex-col gap-3 rounded-[20px] border-[1.5px] border-ligne p-5 md:gap-4 md:rounded-[28px] md:p-8">
                    <div class="flex items-center justify-between gap-3">
                        <div @class(['rounded-full px-3 py-1.5 text-[13px] font-extrabold', 'bg-vert text-white' => $i < 2, 'bg-peche text-foret' => $i === 2])>{{ $repere }}</div>
                        <div class="text-right text-[13px] font-bold text-mousse">{{ $meta }}</div>
                    </div>
                    <h3 class="text-[19px] font-bold md:text-[22px]">{{ $titre }}</h3>
                    <p class="text-[15px] leading-normal text-mousse md:text-base md:leading-relaxed">{{ $texte }}</p>
                    @if ($sujet)
                        <div class="mt-auto flex flex-col gap-1.5 rounded-2xl bg-brume p-4">
                            <div class="text-[13px] font-extrabold tracking-[0.8px] text-mousse uppercase">Exemple de sujet</div>
                            <p class="text-[15px] leading-normal font-medium">« {{ $sujet }} »</p>
                        </div>
                    @endif
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Critères et pièges --}}
    <section class="bg-brume">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-3.5 px-5 py-10 md:gap-5 md:px-10 md:py-24 lg:flex-row xl:px-0">
            <div class="flex flex-1 flex-col gap-4 rounded-[20px] bg-white p-5 md:gap-6 md:rounded-[28px] md:p-10">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-4xl">Ce qui est évalué</h2>
                <ul class="flex flex-col gap-3">
                    @foreach ($guide['evalue'] as $point)
                        <li class="flex items-start gap-3 text-base md:text-lg">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-brume text-vert"><x-icone nom="coche" :epaisseur="2.6" class="size-4" /></span>
                            <span class="pt-0.5">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="flex flex-col gap-4 rounded-[20px] bg-peche-clair p-5 md:gap-6 md:rounded-[28px] md:p-10 lg:w-[440px] lg:shrink-0">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-4xl">Pièges à éviter</h2>
                <ul class="flex flex-col gap-3">
                    @foreach ($guide['pieges'] as $piege)
                        <li class="flex items-start gap-3 text-base">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-peche text-foret"><x-icone nom="croix" :epaisseur="2.6" class="size-4" /></span>
                            <span class="pt-0.5">{{ $piege }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- Conseils --}}
    <section id="conseils" class="mx-auto flex max-w-[1200px] scroll-mt-4 flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
        <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Nos conseils pour gagner des points</h2>
        <div class="grid gap-3.5 md:grid-cols-2 md:gap-5">
            @foreach ($guide['conseils'] as $i => [$titre, $texte])
                <div class="flex gap-3.5 rounded-[20px] bg-brume p-5 md:gap-5 md:rounded-3xl md:p-8">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-vert font-extrabold text-white md:size-12 md:font-titre md:text-xl">{{ $i + 1 }}</div>
                    <div class="flex flex-col gap-1 pt-1 md:gap-2 md:pt-2">
                        <h3 class="text-[17px] font-bold md:text-xl">{{ $titre }}</h3>
                        <p class="text-[15px] leading-normal text-mousse md:text-base md:leading-relaxed">{{ $texte }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Exemple de question --}}
    @if ($exemple)
        @php($lettres = ['A', 'B', 'C', 'D', 'E', 'F'])
        <section class="px-3 md:px-10">
            <div class="relative isolate mx-auto flex max-w-[1360px] flex-col gap-5 overflow-hidden rounded-[28px] bg-menthe px-5 py-7 md:rounded-[36px] md:p-14 lg:flex-row lg:gap-16 xl:px-20" x-data="{ reponse: false }">
                <x-arcs class="-right-[200px] -bottom-[260px] size-[560px]" />
                <div class="flex flex-col gap-4 lg:w-[380px] lg:shrink-0">
                    <div class="self-start rounded-full bg-foret/8 px-3 py-1.5 text-[13px] font-semibold">Exemple de question</div>
                    <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-4xl md:leading-[1.05]">À toi de jouer.</h2>
                    <p class="text-base leading-normal text-mousse-fonce md:text-lg">
                        @if ($exemple->transcription)
                            Dans le quiz, tu écoutes l'enregistrement une seule fois. Ici, lis sa transcription puis choisis ta réponse.
                        @else
                            Lis le document, choisis ta réponse, puis vérifie avec la correction.
                        @endif
                    </p>
                </div>

                <div class="flex flex-1 flex-col gap-3.5 rounded-[20px] bg-white p-4 md:rounded-[28px] md:p-7">
                    @if ($exemple->transcription)
                        <div class="rounded-2xl bg-brume p-4 text-[15px] leading-[1.6] whitespace-pre-line">{{ $exemple->transcription }}</div>
                    @elseif ($exemple->support)
                        <div class="rounded-2xl bg-brume p-4 text-[15px] leading-[1.6] whitespace-pre-line">{{ $exemple->support }}</div>
                    @endif

                    <h3 class="font-titre text-xl leading-[1.2] tracking-[-0.5px]">{{ $exemple->enonce }}</h3>

                    <ul class="flex flex-col gap-2">
                        @foreach ($exemple->choix as $i => $libelle)
                            @php($estBonne = $i === $exemple->bonne_reponse)
                            <li class="flex min-h-12 items-center gap-3 rounded-[14px] border-[1.5px] border-ligne px-3.5 text-[15px] transition-colors"
                                @if ($estBonne) :class="reponse && 'border-2 border-vert bg-brume font-bold'" @endif>
                                <span class="flex size-[26px] shrink-0 items-center justify-center rounded-lg bg-brume text-[13px] font-bold"
                                    @if ($estBonne) :class="reponse && 'bg-vert text-white'" @endif>{{ $lettres[$i] }}</span>
                                <span class="flex-1 py-2">{{ $libelle }}</span>
                                @if ($estBonne)
                                    <span x-show="reponse" x-cloak class="text-[13px] text-vert">Bonne réponse</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    <div x-show="reponse" x-collapse x-cloak>
                        <div class="flex flex-col gap-2 rounded-2xl border-[1.5px] border-ligne p-4">
                            <div class="text-[15px] font-extrabold">Pourquoi ?</div>
                            <p class="text-[15px] leading-[1.55] text-mousse">{{ $exemple->explication }}</p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2.5 sm:flex-row">
                        <button type="button" @click="reponse = !reponse" :aria-expanded="reponse"
                            class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] border-[1.5px] border-foret text-base font-bold text-foret hover:bg-foret/5"
                            x-text="reponse ? 'Masquer la réponse' : 'Voir la réponse'">Voir la réponse</button>
                        <a href="{{ route('quiz', $epreuve) }}" wire:navigate class="flex h-[52px] flex-1 items-center justify-center rounded-[14px] bg-foret text-base font-bold text-white hover:bg-vert hover:text-white">Faire le quiz complet</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Scores --}}
    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-10 md:px-10 md:py-24 xl:px-0">
        <div class="flex flex-col gap-2.5 md:flex-row md:items-end md:justify-between md:gap-6">
            <div class="flex flex-col gap-2.5">
                <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">Lire ton score</h2>
                <p class="max-w-[640px] text-base leading-normal text-mousse md:text-lg">
                    {{ $surVingt ? 'Ta production est notée de 0 à 20.' : 'Ton score va de 100 à 699.' }} Voici à quels niveaux il correspond.
                </p>
            </div>
            <a href="{{ route('scores') }}" wire:navigate class="shrink-0 self-start border-b-2 border-foret py-3 text-base font-bold text-foret md:self-auto">Voir tout le barème</a>
        </div>

        <div class="grid gap-3.5 md:grid-cols-2 md:gap-5">
            @foreach ([
                ['Niveau CECRL', GuideEpreuve::cecrl($code), ''],
                ['Niveau NCLC', array_map(fn ($p) => $p[$code], Nclc::BAREME), 'NCLC '],
            ] as [$titre, $paliers, $prefixe])
                <div class="flex flex-col gap-3 rounded-[20px] border-[1.5px] border-ligne p-5 md:rounded-[28px] md:p-8">
                    <h3 class="text-[17px] font-bold md:text-xl">{{ $titre }}</h3>
                    <ul class="flex flex-col">
                        @foreach ($paliers as $niveau => $plage)
                            <li class="flex items-center justify-between gap-3 border-t-[1.5px] border-ligne py-2.5 text-[15px] first:border-t-0 md:text-base">
                                <span class="font-bold">{{ $prefixe }}{{ $niveau }}</span>
                                <span class="tabular-nums text-mousse">{{ Nclc::plage($plage) }}{{ $surVingt ? ' / 20' : '' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
        <p class="text-[13px] leading-normal text-mousse md:text-sm">Correspondances indicatives d'après les barèmes publiés. Sous le NCLC 5, le résultat ne permet pas d'obtenir de niveau NCLC.</p>
    </section>

    {{-- Autres épreuves --}}
    <section class="bg-brume">
        <div class="mx-auto flex max-w-[1200px] flex-col gap-4 px-5 py-10 md:gap-8 md:px-10 md:py-20 xl:px-0">
            <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-4xl">Les autres épreuves</h2>
            <div class="grid gap-2.5 md:grid-cols-3 md:gap-4">
                @foreach ($this->autres as $autre)
                    <a href="{{ route('epreuve', $autre) }}" wire:navigate wire:key="autre-{{ $autre->id }}"
                        class="group flex min-h-[76px] items-center gap-3.5 rounded-[18px] bg-white py-3 pr-4 pl-3 text-foret transition-colors hover:bg-menthe hover:text-foret md:flex-col md:items-start md:gap-4 md:rounded-[22px] md:p-6">
                        <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-vert text-white md:size-14">
                            <x-icone :nom="$autre->icone" class="size-[22px] md:size-6" />
                        </div>
                        <div class="flex flex-1 flex-col gap-0.5">
                            <div class="text-[17px] font-bold md:text-xl">{{ $autre->nom }}</div>
                            <div class="text-sm text-mousse">{{ $autre->format }}</div>
                        </div>
                        <x-icone nom="droite" class="size-5 shrink-0 md:hidden" />
                        <div class="hidden font-bold text-vert md:block">Découvrir <span aria-hidden="true" class="inline-block transition-transform group-hover:translate-x-0.5">→</span></div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="pt-10 md:pt-16">
        @if ($exemple)
            <x-site.appel :titre="'Prêt pour un quiz de '.mb_strtolower($epreuve->nom).' ?'" :lien="route('quiz', $epreuve)" />
        @else
            <x-site.appel titre="Mesure ton niveau dans les conditions de l'examen." :lien="route('tests-blancs')" libelle="Découvrir les tests blancs" />
        @endif
    </div>
</x-site.page>
