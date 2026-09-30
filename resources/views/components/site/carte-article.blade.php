{{-- Carte d'un article dans une grille : toute la carte mène à l'article. --}}
@props(['article', 'niveau' => 'h3'])

<article {{ $attributes->merge(['class' => 'group relative flex flex-col overflow-hidden rounded-[20px] border-[1.5px] border-ligne bg-white transition duration-300 hover:-translate-y-1.5 hover:border-vert/40 hover:shadow-[0_24px_60px_rgba(11,46,28,0.08)] md:rounded-[28px]']) }}>
    <div class="aspect-[16/10] overflow-hidden">
        <x-site.couverture-article :article="$article" class="transition-transform duration-500 ease-ressort group-hover:scale-[1.04]" />
    </div>
    <div class="flex flex-1 flex-col gap-2.5 p-5 md:gap-3 md:p-6">
        <div class="self-start rounded-full bg-menthe px-3 py-1 text-[13px] font-bold text-foret">{{ $article->categorie }}</div>
        <{{ $niveau }} class="text-lg leading-snug font-bold text-balance md:text-xl">
            <a href="{{ route('article', $article->slug) }}" wire:navigate class="text-foret after:absolute after:inset-0 group-hover:text-vert">{{ $article->titre }}</a>
        </{{ $niveau }}>
        <p class="line-clamp-3 text-[15px] leading-normal text-mousse">{{ $article->extrait }}</p>
        <div class="mt-auto flex items-center gap-2 pt-1 text-[13px] font-semibold text-lichen md:text-sm">
            <time datetime="{{ $article->publie_le->toDateString() }}">{{ $article->publie_le->translatedFormat('j F Y') }}</time>
            <span aria-hidden="true">·</span>
            <span class="flex items-center gap-1.5"><x-icone nom="horloge" class="size-4" />{{ __(':minutes min', ['minutes' => $article->temps_lecture]) }}</span>
        </div>
    </div>
</article>
