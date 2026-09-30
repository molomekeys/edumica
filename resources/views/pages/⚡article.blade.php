<?php

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public Article $article;

    public function mount(Article $article): void
    {
        // Brouillons et articles programmés restent introuvables sur le site.
        abort_unless($article->estVisible(), 404);

        $this->article = $article->load('auteur:id,name');
    }

    /** Trois articles à lire ensuite : même catégorie d'abord, complétés par les plus récents. */
    #[Computed]
    public function lies(): Collection
    {
        $memeCategorie = Article::publies()
            ->whereKeyNot($this->article->id)
            ->where('categorie', $this->article->categorie)
            ->latest('publie_le')
            ->limit(3)
            ->get();

        if ($memeCategorie->count() === 3) {
            return $memeCategorie;
        }

        return $memeCategorie->concat(Article::publies()
            ->whereKeyNot([$this->article->id, ...$memeCategorie->modelKeys()])
            ->latest('publie_le')
            ->limit(3 - $memeCategorie->count())
            ->get());
    }

    public function render()
    {
        return $this->view(['lecture' => $this->article->contenuAvecSommaire()])
            ->title($this->article->meta_titre ?: $this->article->titre);
    }
};
?>

@php
    $url = route('article', $article->slug);
    $description = $article->meta_description ?: $article->extrait;
    $couverture = $article->urlCouverture();
    $auteur = $article->auteur?->name ?? __('L\'équipe Edumica');
    $donneesStructurees = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'headline' => $article->titre,
        'description' => $description,
        'image' => $couverture ? url($couverture) : null,
        'datePublished' => $article->publie_le->toIso8601String(),
        'dateModified' => $article->updated_at->toIso8601String(),
        'author' => ['@type' => 'Person', 'name' => $auteur],
        'publisher' => ['@type' => 'Organization', 'name' => 'Edumica'],
        'mainEntityOfPage' => $url,
        'articleSection' => $article->categorie,
        'inLanguage' => 'fr',
    ]);
@endphp

<x-slot:description>{{ $description }}</x-slot:description>
<x-slot:meta>
    <link rel="canonical" href="{{ $url }}">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="Edumica">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="{{ $article->meta_titre ?: $article->titre }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $url }}">
    @if ($couverture)
        <meta property="og:image" content="{{ url($couverture) }}">
    @endif
    <meta property="article:published_time" content="{{ $article->publie_le->toIso8601String() }}">
    <meta property="article:modified_time" content="{{ $article->updated_at->toIso8601String() }}">
    <meta property="article:section" content="{{ $article->categorie }}">
    <meta name="twitter:card" content="{{ $couverture ? 'summary_large_image' : 'summary' }}">
    <script type="application/ld+json">{!! json_encode($donneesStructurees, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
</x-slot:meta>

<x-site.page>
    <div x-data="{
            progression: 0,
            actif: null,
            maj() {
                const corps = this.$refs.corps.getBoundingClientRect();
                const parcours = corps.height - innerHeight * 0.5;
                this.progression = parcours > 0 ? Math.min(1, Math.max(0, -corps.top / parcours)) : 1;
            },
            init() {
                this.maj();
                const observateur = new IntersectionObserver((entrees) => entrees.forEach((entree) => entree.isIntersecting && (this.actif = entree.target.id)), { rootMargin: '0px 0px -75% 0px' });
                this.$refs.corps.querySelectorAll('h2[id]').forEach((titre) => observateur.observe(titre));
            },
        }" @scroll.window.passive="maj()" @resize.window.passive="maj()">

        {{-- Barre de progression de lecture --}}
        <div class="fixed inset-x-0 top-0 z-40 h-1 bg-transparent" role="progressbar" aria-label="{{ __('Progression de la lecture') }}" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="Math.round(progression * 100)">
            <div class="h-full origin-left bg-vert transition-transform duration-100 ease-linear rtl:origin-right" :style="`transform: scaleX(${progression})`" style="transform: scaleX(0)"></div>
        </div>

        {{-- En-tête --}}
        <section class="px-3 pt-1 md:px-10 md:pt-2">
            <div class="relative isolate mx-auto grid max-w-[1360px] gap-6 overflow-hidden rounded-[28px] bg-menthe px-5 pt-6 pb-5 md:gap-10 md:rounded-[36px] md:p-14 lg:grid-cols-[minmax(0,1fr)_minmax(0,500px)] lg:items-center lg:gap-14 xl:px-20 xl:py-16">
                <x-arcs class="-top-[120px] -right-[120px] size-[300px] lg:hidden" />
                <x-arcs class="-bottom-[260px] -left-[200px] hidden size-[560px] lg:block" />

                <div class="flex flex-col gap-4 md:gap-5">
                    <nav aria-label="{{ __('Fil d\'Ariane') }}">
                        <ol class="flex flex-wrap items-center gap-1.5 text-[13px] font-semibold text-mousse-fonce md:text-sm">
                            <li><a href="{{ route('accueil') }}" wire:navigate class="text-mousse-fonce">{{ __('Accueil') }}</a></li>
                            <li aria-hidden="true">/</li>
                            <li><a href="{{ route('articles') }}" wire:navigate class="text-mousse-fonce">{{ __('Articles') }}</a></li>
                            <li aria-hidden="true">/</li>
                            <li><a href="{{ route('articles', ['categorie' => Article::slugCategorie($article->categorie)]) }}" wire:navigate class="text-mousse-fonce">{{ $article->categorie }}</a></li>
                        </ol>
                    </nav>

                    <h1 class="font-titre text-[28px] leading-[1.08] tracking-[-0.8px] text-balance md:text-[44px] md:leading-[1.04] md:tracking-[-1.5px]" lang="fr">{{ $article->titre }}</h1>

                    <p class="max-w-[640px] text-[17px] leading-normal text-mousse-fonce md:text-xl md:leading-[1.55]" lang="fr">{{ $article->extrait }}</p>

                    <div class="flex items-center gap-3 pt-1">
                        <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-foret text-sm font-extrabold text-menthe" aria-hidden="true">
                            {{ $article->auteur?->initiales() ?? 'E' }}
                        </div>
                        <div class="flex flex-col text-sm leading-snug md:text-[15px]">
                            <span class="font-bold">{{ $auteur }}</span>
                            <span class="font-semibold text-mousse-fonce">
                                <time datetime="{{ $article->publie_le->toDateString() }}">{{ $article->publie_le->translatedFormat('j F Y') }}</time>
                                · {{ __(':minutes min de lecture', ['minutes' => $article->temps_lecture]) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="aspect-[16/10] overflow-hidden rounded-[20px] shadow-[0_24px_60px_rgba(11,46,28,0.12)] md:rounded-[28px]">
                    <x-site.couverture-article :article="$article" chargement="eager" />
                </div>
            </div>
        </section>

        {{-- Corps --}}
        <div class="mx-auto max-w-[1200px] px-5 py-8 md:px-10 md:py-16 lg:grid lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-14 xl:gap-20 xl:px-0">
            <aside class="hidden lg:block">
                <div class="sticky top-8 flex flex-col gap-6">
                    @if ($lecture['sommaire'])
                        <nav aria-label="{{ __('Sommaire') }}" class="flex flex-col gap-1 rounded-3xl bg-brume p-5">
                            <div class="mb-1 text-[13px] font-extrabold tracking-[0.8px] text-mousse uppercase">{{ __('Sommaire') }}</div>
                            @foreach ($lecture['sommaire'] as $section)
                                <a href="#{{ $section['id'] }}" lang="fr"
                                    class="relative rounded-xl py-2 ps-3 pe-2 text-[15px] leading-snug font-semibold transition-colors"
                                    :class="actif === @js($section['id']) ? 'bg-white text-vert' : 'text-foret hover:bg-white/60'">
                                    {{ $section['titre'] }}
                                </a>
                            @endforeach
                        </nav>
                    @endif
                    <div class="flex flex-col gap-3">
                        <div class="text-[13px] font-extrabold tracking-[0.8px] text-mousse uppercase">{{ __('Partager') }}</div>
                        <x-site.partage :url="$url" :titre="$article->titre" />
                    </div>
                </div>
            </aside>

            <div class="min-w-0">
                @if ($lecture['sommaire'])
                    <details class="group mb-8 rounded-[20px] bg-brume lg:hidden">
                        <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-5 text-base font-bold [&::-webkit-details-marker]:hidden">
                            {{ __('Sommaire') }}
                            <x-icone nom="bas" class="size-5 transition-transform duration-300 group-open:rotate-180" />
                        </summary>
                        <ol class="flex flex-col px-5 pb-4">
                            @foreach ($lecture['sommaire'] as $section)
                                <li><a href="#{{ $section['id'] }}" lang="fr" class="flex min-h-11 items-center gap-3 text-[15px] font-semibold text-foret"><span class="w-5 text-mousse tabular-nums">{{ $loop->iteration }}.</span>{{ $section['titre'] }}</a></li>
                            @endforeach
                        </ol>
                    </details>
                @endif

                <div x-ref="corps" class="contenu-article max-w-[70ch]" lang="fr" dir="ltr">
                    {!! $lecture['html'] !!}
                </div>

                <div class="mt-12 flex max-w-[70ch] flex-col gap-4 border-t-[1.5px] border-ligne pt-8 md:flex-row md:items-center md:justify-between">
                    <p class="text-base font-bold md:text-lg">{{ __('Cet article t\'a aidé ? Partage-le.') }}</p>
                    <x-site.partage :url="$url" :titre="$article->titre" />
                </div>
            </div>
        </div>
    </div>

    {{-- Articles liés --}}
    @if ($this->lies->isNotEmpty())
        <section class="bg-brume">
            <div class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:gap-9 md:px-10 md:py-20 xl:px-0">
                <div class="flex items-end justify-between gap-4">
                    <h2 class="font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-[40px] md:leading-[1.05] md:tracking-[-1.2px]">{{ __('À lire aussi') }}</h2>
                    <a href="{{ route('articles') }}" wire:navigate class="hidden shrink-0 items-center gap-1.5 text-base font-bold text-vert sm:flex">
                        {{ __('Tous les articles') }}<x-icone nom="droite" :epaisseur="2.4" class="size-[18px]" />
                    </a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 md:gap-6 lg:grid-cols-3">
                    @foreach ($this->lies as $lie)
                        <x-site.carte-article :article="$lie" wire:key="lie-{{ $lie->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <div @class(['pt-8 md:pt-16' => $this->lies->isNotEmpty()])>
        <x-site.appel :titre="__('Mets ces conseils en pratique.')" :lien-secondaire="route('tests-blancs')" :libelle-secondaire="__('Passer un test blanc')" />
    </div>
</x-site.page>
