<?php

use App\Support\Faq;
use Livewire\Component;

new class extends Component
{
    public function render()
    {
        return $this->view(['themes' => Faq::THEMES])->title(__('Questions fréquentes'));
    }
};
?>

<x-site.page>
    <x-site.hero :titre="__('Questions fréquentes')">
        <x-slot:intro>{{ __("Le TCF, les quiz, les tests blancs, ton compte : les réponses aux questions qu'on nous pose le plus souvent.") }}</x-slot:intro>
        <x-slot:actions>
            <nav aria-label="{{ __('Thèmes') }}" class="flex flex-wrap gap-2">
                @foreach ($themes as $theme => [$ancre, $questions])
                    <a href="#{{ $ancre }}" class="flex h-11 items-center gap-2 rounded-full bg-white px-4 text-[15px] font-bold text-foret hover:bg-foret hover:text-white">
                        {{ __($theme) }}<span class="text-[13px] font-semibold opacity-60">{{ count($questions) }}</span>
                    </a>
                @endforeach
            </nav>
        </x-slot:actions>
    </x-site.hero>

    <div class="mx-auto flex max-w-[1200px] flex-col gap-10 px-5 py-10 md:gap-20 md:px-10 md:py-24 xl:px-0">
        @foreach ($themes as $theme => [$ancre, $questions])
            <section id="{{ $ancre }}" class="flex scroll-mt-4 flex-col gap-2.5 lg:flex-row lg:gap-20">
                <h2 class="mb-1 font-titre text-2xl leading-[1.15] tracking-[-0.5px] md:text-4xl md:leading-[1.05] lg:w-[360px] lg:shrink-0">{{ __($theme) }}</h2>
                <x-site.faq :questions="$questions" :prefixe="'faq-'.$ancre" :ouverte="$loop->first ? 0 : -1" class="flex-1" />
            </section>
        @endforeach
    </div>

    {{-- Contact --}}
    <section class="px-3 pb-8 md:px-10 md:pb-16">
        <div class="relative isolate mx-auto flex max-w-[1360px] flex-col gap-4 overflow-hidden rounded-[28px] bg-menthe px-5 py-7 md:flex-row md:items-center md:justify-between md:gap-10 md:rounded-[36px] md:px-14 md:py-14 xl:px-20">
            <x-arcs class="-top-[120px] -right-[120px] size-[300px]" />
            <div class="flex flex-col gap-2">
                <h2 class="font-titre text-[26px] leading-[1.1] tracking-[-0.5px] md:text-[44px] md:leading-[1.05] md:tracking-[-1.5px]">{{ __('Pas trouvé ta réponse ?') }}</h2>
                <p class="text-base text-mousse-fonce md:text-lg">{{ __('Écris-nous, on te répond rapidement.') }}</p>
            </div>
            <a href="{{ route('contact') }}" wire:navigate class="flex h-14 shrink-0 items-center justify-center rounded-2xl bg-foret px-8 text-[17px] font-bold text-white hover:bg-vert hover:text-white md:h-[58px] md:text-lg">{{ __('Nous contacter') }}</a>
        </div>
    </section>
</x-site.page>
