<?php

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public const PAR_PAGE = 9;

    /** Catégorie filtrée, par son slug (?categorie=conseils-tcf). */
    #[Url(except: '')]
    public string $categorie = '';

    #[Url(except: '')]
    public string $q = '';

    public function updated(string $propriete): void
    {
        if (in_array($propriete, ['categorie', 'q'], true)) {
            $this->resetPage();
        }
    }

    public function filtrer(string $categorie = ''): void
    {
        $this->categorie = $categorie;
        $this->resetPage();
    }

    public function reinitialiser(): void
    {
        $this->reset('categorie', 'q');
        $this->resetPage();
    }

    #[Computed]
    public function filtre(): bool
    {
        return $this->categorie !== '' || trim($this->q) !== '';
    }

    /** Le dernier article publié, à la une quand la liste n'est pas filtrée. */
    #[Computed]
    public function aLaUne(): ?Article
    {
        return $this->filtre ? null : Article::publies()->latest('publie_le')->latest('id')->first();
    }

    #[Computed]
    public function articles(): LengthAwarePaginator
    {
        $categorie = Article::categoriePourSlug($this->categorie);
        $terme = trim($this->q);

        return Article::publies()
            ->when($this->aLaUne, fn (Builder $requete, Article $article) => $requete->whereKeyNot($article->id))
            ->when($categorie, fn (Builder $requete, string $categorie) => $requete->where('categorie', $categorie))
            ->when($terme, fn (Builder $requete) => $requete->where(fn (Builder $requete) => $requete
                ->where('titre', 'like', "%{$terme}%")
                ->orWhere('extrait', 'like', "%{$terme}%")))
            ->latest('publie_le')
            ->latest('id')
            ->paginate(self::PAR_PAGE);
    }

    public function render()
    {
        return $this->view(['categories' => Article::CATEGORIES])->title(__('Articles et conseils pour le TCF'));
    }
};
?>

<x-slot:description>{{ __('Méthodes, pièges à éviter et conseils pour réussir le TCF Canada et le TCF Tout public : nos articles épreuve par épreuve.') }}</x-slot:description>
<x-slot:meta>
    <link rel="canonical" href="{{ route('articles') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Edumica">
    <meta property="og:title" content="{{ __('Articles et conseils pour le TCF') }} · Edumica">
    <meta property="og:description" content="{{ __('Méthodes, pièges à éviter et conseils pour réussir le TCF Canada et le TCF Tout public : nos articles épreuve par épreuve.') }}">
    <meta property="og:url" content="{{ route('articles') }}">
</x-slot:meta>

<x-site.page>
    <x-site.hero :titre="__('Articles')" :badge="__('Le blog Edumica')">
        <x-slot:heading>{!! __('Conseils et méthodes pour <span class="text-vert">réussir ton TCF</span>.') !!}</x-slot:heading>
        <x-slot:intro>{{ __('Épreuve par épreuve, les pièges à éviter, les bons réflexes et tout ce qu\'il faut savoir sur les niveaux NCLC et l\'immigration au Canada.') }}</x-slot:intro>
    </x-site.hero>

    <div class="mx-auto flex max-w-[1200px] flex-col gap-8 px-5 py-10 md:gap-12 md:px-10 md:py-20 xl:px-0">
        {{-- À la une --}}
        @if ($this->aLaUne && $this->articles->onFirstPage())
            @php($une = $this->aLaUne)
            <article wire:key="une-{{ $une->id }}" class="group relative grid overflow-hidden rounded-[24px] bg-brume transition duration-300 hover:shadow-[0_28px_60px_rgba(11,46,28,0.1)] md:rounded-[36px] lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)]">
                <div class="aspect-[16/10] overflow-hidden lg:aspect-auto lg:min-h-[420px]">
                    <x-site.couverture-article :article="$une" chargement="eager" class="transition-transform duration-700 ease-ressort group-hover:scale-[1.03]" />
                </div>
                <div class="flex flex-col gap-3.5 p-5 md:gap-5 md:p-10 xl:p-14">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-peche px-3 py-1 text-[13px] font-extrabold text-foret">{{ __('À la une') }}</span>
                        <span class="rounded-full bg-menthe px-3 py-1 text-[13px] font-bold text-foret">{{ $une->categorie }}</span>
                    </div>
                    <h2 class="font-titre text-[24px] leading-[1.12] tracking-[-0.5px] text-balance md:text-[34px] md:tracking-[-1px]">
                        <a href="{{ route('article', $une->slug) }}" wire:navigate class="text-foret after:absolute after:inset-0 group-hover:text-vert">{{ $une->titre }}</a>
                    </h2>
                    <p class="text-base leading-normal text-mousse md:text-lg md:leading-relaxed">{{ $une->extrait }}</p>
                    <div class="mt-auto flex flex-wrap items-center justify-between gap-4 pt-2">
                        <div class="flex items-center gap-2 text-sm font-semibold text-lichen">
                            <time datetime="{{ $une->publie_le->toDateString() }}">{{ $une->publie_le->translatedFormat('j F Y') }}</time>
                            <span aria-hidden="true">·</span>
                            <span class="flex items-center gap-1.5"><x-icone nom="horloge" class="size-4" />{{ __(':minutes min de lecture', ['minutes' => $une->temps_lecture]) }}</span>
                        </div>
                        <span class="flex h-12 items-center gap-2 rounded-2xl bg-foret px-5 text-[15px] font-bold text-white transition-colors group-hover:bg-vert" aria-hidden="true">
                            {{ __('Lire l\'article') }}
                            <x-icone nom="droite" :epaisseur="2.4" class="size-[18px] transition-transform duration-300 group-hover:translate-x-1 rtl:group-hover:-translate-x-1" />
                        </span>
                    </div>
                </div>
            </article>
        @endif

        {{-- Filtres --}}
        <div id="liste" class="flex scroll-mt-6 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <nav aria-label="{{ __('Catégories') }}" class="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] md:mx-0 md:flex-wrap md:overflow-visible md:px-0 md:pb-0">
                @foreach (['' => __('Tous les articles'), ...collect($categories)->mapWithKeys(fn ($nom) => [Article::slugCategorie($nom) => $nom])->all()] as $slug => $libelle)
                    <button type="button" wire:click="filtrer('{{ $slug }}')" wire:key="filtre-{{ $slug ?: 'tous' }}" aria-pressed="{{ $categorie === $slug ? 'true' : 'false' }}"
                        @class(['flex h-11 shrink-0 items-center rounded-full px-4 text-[15px] font-bold whitespace-nowrap transition-colors duration-200',
                            'bg-foret text-white' => $categorie === $slug,
                            'bg-brume text-foret hover:bg-menthe' => $categorie !== $slug])>
                        {{ $libelle }}
                    </button>
                @endforeach
            </nav>

            <label class="relative block lg:w-[300px] lg:shrink-0">
                <span class="sr-only">{{ __('Rechercher un article') }}</span>
                <svg class="pointer-events-none absolute start-4 top-1/2 size-[18px] -translate-y-1/2 text-mousse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" wire:model.live.debounce.350ms="q" placeholder="{{ __('Rechercher un article…') }}"
                    class="h-12 w-full rounded-2xl border-[1.5px] border-ligne bg-white ps-11 pe-4 text-base text-foret placeholder:text-mousse/70 focus:border-vert focus:ring-4 focus:ring-menthe focus:outline-none">
            </label>
        </div>

        {{-- Grille --}}
        <div class="flex flex-col gap-8 transition-opacity duration-200" wire:loading.class="opacity-60" wire:target="filtrer,q,reinitialiser,gotoPage,nextPage,previousPage">
            @if ($this->articles->isEmpty())
                <div class="flex flex-col items-center gap-3 rounded-[24px] border-[1.5px] border-dashed border-ligne px-5 py-14 text-center md:rounded-[28px]">
                    <div class="flex size-14 items-center justify-center rounded-full bg-brume text-vert"><x-icone nom="livre" class="size-6" /></div>
                    <p class="text-lg font-bold">{{ $this->filtre ? __('Aucun article ne correspond à ta recherche.') : __('Les premiers articles arrivent bientôt.') }}</p>
                    @if ($this->filtre)
                        <button type="button" wire:click="reinitialiser" class="text-[15px] font-bold text-vert underline underline-offset-4">{{ __('Voir tous les articles') }}</button>
                    @endif
                </div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 md:gap-6 lg:grid-cols-3">
                    @foreach ($this->articles as $article)
                        <x-site.carte-article :article="$article" wire:key="article-{{ $article->id }}" />
                    @endforeach
                </div>
            @endif

            @if ($this->articles->hasPages())
                @php($pages = $this->articles)
                <nav aria-label="{{ __('Pages des articles') }}" class="flex items-center justify-center gap-1.5" x-on:click="$event.target.closest('button:not([disabled])') && document.getElementById('liste').scrollIntoView()">
                    <button type="button" wire:click="previousPage" @disabled($pages->onFirstPage()) aria-label="{{ __('Page précédente') }}"
                        class="flex size-11 items-center justify-center rounded-full border-[1.5px] border-ligne text-foret hover:bg-brume disabled:pointer-events-none disabled:opacity-40">
                        <x-icone nom="gauche" class="size-5" />
                    </button>
                    @foreach (range(1, $pages->lastPage()) as $page)
                        <button type="button" wire:click="gotoPage({{ $page }})" @if ($page === $pages->currentPage()) aria-current="page" @endif
                            @class(['flex size-11 items-center justify-center rounded-full text-[15px] font-bold tabular-nums', 'bg-foret text-white' => $page === $pages->currentPage(), 'text-foret hover:bg-brume' => $page !== $pages->currentPage()])>
                            {{ $page }}
                        </button>
                    @endforeach
                    <button type="button" wire:click="nextPage" @disabled(! $pages->hasMorePages()) aria-label="{{ __('Page suivante') }}"
                        class="flex size-11 items-center justify-center rounded-full border-[1.5px] border-ligne text-foret hover:bg-brume disabled:pointer-events-none disabled:opacity-40">
                        <x-icone nom="droite" class="size-5" />
                    </button>
                </nav>
            @endif
        </div>
    </div>

    <x-site.appel :titre="__('Passe de la lecture à la pratique.')" :lien-secondaire="route('tests-blancs')" :libelle-secondaire="__('Voir les tests blancs')" />
</x-site.page>
