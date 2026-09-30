{{-- Barème NCLC du TCF Canada : tableau sur ordinateur, cartes sur téléphone. La ligne $cible est surlignée. --}}
@props(['cible' => \App\Support\Nclc::CIBLE_PAR_DEFAUT, 'mobile' => true])

@php($codes = ['co', 'ce', 'eo', 'ee'])

<div {{ $attributes }}>
    <div @class(['w-full overflow-hidden rounded-3xl border-[1.5px] border-ligne', 'hidden md:block' => $mobile])>
        <table class="w-full border-collapse text-start text-base">
            <caption class="sr-only">{{ __('Scores du TCF Canada par niveau NCLC') }}</caption>
            <thead>
                <tr class="bg-brume">
                    <th scope="col" class="px-5 py-4 font-bold">NCLC</th>
                    @foreach ($codes as $code)
                        <th scope="col" class="px-5 py-4 font-bold">{{ \App\Support\Nclc::libelleCourt($code) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Support\Nclc::BAREME as $niveau => $plages)
                    <tr wire:key="bareme-{{ $niveau }}" @class(['border-t-[1.5px] border-ligne transition-colors duration-200', 'bg-peche-clair font-bold' => (string) $niveau === $cible, 'hover:bg-brume' => (string) $niveau !== $cible])>
                        <th scope="row" @class(['px-5 py-3.5', 'font-extrabold' => (string) $niveau === $cible, 'font-bold' => (string) $niveau !== $cible])>{{ $niveau }}</th>
                        @foreach ($codes as $code)
                            <td class="px-5 py-3.5 tabular-nums">{{ \App\Support\Nclc::plage($plages[$code]) }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($mobile)
        <ul class="flex flex-col gap-2.5 md:hidden">
            @foreach (\App\Support\Nclc::BAREME as $niveau => $plages)
                <li wire:key="bareme-carte-{{ $niveau }}" @class([
                    'flex flex-col gap-2.5 rounded-[18px] p-4',
                    'bg-peche-clair' => (string) $niveau === $cible,
                    'border-[1.5px] border-ligne' => (string) $niveau !== $cible,
                ])>
                    <div class="flex items-center justify-between">
                        <div class="font-titre text-xl">NCLC {{ $niveau }}</div>
                        @if ((string) $niveau === $cible)
                            <div class="rounded-full bg-peche px-2.5 py-1 text-[13px] font-extrabold">{{ __('Ton objectif') }}</div>
                        @endif
                    </div>
                    <dl class="grid grid-cols-2 gap-x-3 gap-y-1.5 text-[15px]">
                        @foreach ($codes as $code)
                            <div class="flex flex-col">
                                <dt class="text-[13px] text-mousse">{{ \App\Support\Nclc::libelleCourt($code) }}</dt>
                                <dd class="font-bold tabular-nums">{{ \App\Support\Nclc::plage($plages[$code]) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </li>
            @endforeach
        </ul>
    @endif
</div>
