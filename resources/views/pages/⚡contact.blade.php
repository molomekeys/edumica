<?php

use App\Mail\MessageContact;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public const SUJETS = ['Question sur le TCF', 'Quiz et tests blancs', 'Paiement et compte', 'Signaler une erreur', 'Autre'];

    /** Nombre de messages autorisés par adresse IP et par heure. */
    public const LIMITE = 3;

    public string $nom = '';

    public string $email = '';

    public string $sujet = '';

    public string $contenu = '';

    /** Champ piège, invisible pour les humains. */
    public string $site = '';

    public bool $envoye = false;

    protected function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:160'],
            'sujet' => ['required', Rule::in(self::SUJETS)],
            'contenu' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nom.required' => 'Indique ton nom.',
            'email.required' => 'Indique ton adresse e-mail.',
            'email.email' => "Cette adresse e-mail n'est pas valide.",
            'sujet.required' => 'Choisis un sujet.',
            'sujet.in' => 'Choisis un sujet dans la liste.',
            'contenu.required' => 'Écris ton message.',
            'contenu.min' => 'Ton message est un peu court : 10 caractères au minimum.',
            'contenu.max' => 'Ton message est trop long : 3000 caractères au maximum.',
        ];
    }

    public function envoyer(): void
    {
        $donnees = $this->validate();

        if ($this->site !== '') {
            $this->envoye = true;

            return;
        }

        $cle = 'contact:'.request()->ip();

        if (RateLimiter::tooManyAttempts($cle, self::LIMITE)) {
            $this->addError('contenu', 'Tu as déjà envoyé plusieurs messages. Réessaie dans une heure.');

            return;
        }

        RateLimiter::hit($cle, 3600);

        Mail::to(config('mail.contact'))->send(new MessageContact($donnees['nom'], $donnees['email'], $donnees['sujet'], $donnees['contenu']));

        $this->reset('nom', 'email', 'sujet', 'contenu');
        $this->envoye = true;
    }

    public function render()
    {
        return $this->view()->title('Contact');
    }
};
?>

<x-site.page>
    <x-site.hero titre="Contact">
        <x-slot:heading>Une question&nbsp;? <span class="text-vert">Écris-nous.</span></x-slot:heading>
        <x-slot:intro>Sur le TCF, les quiz, les tests blancs ou ton compte : on lit chaque message et on te répond par e-mail.</x-slot:intro>
    </x-site.hero>

    <section class="mx-auto flex max-w-[1200px] flex-col gap-5 px-5 py-10 md:px-10 md:py-24 lg:flex-row lg:items-start lg:gap-20 xl:px-0">
        {{-- Formulaire --}}
        <div class="flex-1 rounded-[20px] border-[1.5px] border-ligne p-5 md:rounded-[28px] md:p-10">
            @if ($envoye)
                <div role="status" class="flex flex-col items-center gap-4 py-8 text-center">
                    <div class="flex size-16 items-center justify-center rounded-full bg-vert text-white"><x-icone nom="coche" :epaisseur="2.6" class="size-8" /></div>
                    <h2 class="font-titre text-2xl leading-tight tracking-[-0.5px] md:text-[32px]">Message envoyé.</h2>
                    <p class="max-w-sm text-base leading-normal text-mousse">Merci ! On te répond à l'adresse indiquée, en général sous [DÉLAI DE RÉPONSE].</p>
                    <button type="button" wire:click="$set('envoye', false)" class="mt-2 flex h-[52px] items-center justify-center rounded-2xl border-[1.5px] border-foret px-6 text-base font-bold text-foret hover:bg-foret/5">Écrire un autre message</button>
                </div>
            @else
                <form wire:submit="envoyer" class="flex flex-col gap-5" novalidate>
                    <h2 class="font-titre text-2xl leading-tight tracking-[-0.5px] md:text-[32px]">Ton message</h2>

                    <div class="grid gap-5 md:grid-cols-2">
                        @foreach ([['nom', 'Ton nom', 'text', 'name'], ['email', 'Ton adresse e-mail', 'email', 'email']] as [$champ, $libelle, $type, $auto])
                            <div class="flex flex-col gap-2">
                                <label for="{{ $champ }}" class="text-[15px] font-bold">{{ $libelle }}</label>
                                <input id="{{ $champ }}" type="{{ $type }}" wire:model="{{ $champ }}" autocomplete="{{ $auto }}" @error($champ) aria-invalid="true" aria-describedby="{{ $champ }}-erreur" @enderror
                                    @class(['h-14 rounded-2xl border-[1.5px] bg-white px-4 text-base text-foret outline-none focus:border-foret', 'border-peche bg-peche-clair' => $errors->has($champ), 'border-ligne' => ! $errors->has($champ)])>
                                @error($champ) <p id="{{ $champ }}-erreur" class="text-sm font-semibold">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>

                    <fieldset class="flex flex-col gap-2">
                        <legend class="mb-2 text-[15px] font-bold">Sujet</legend>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($this::SUJETS as $choix)
                                <label wire:key="sujet-{{ $loop->index }}" @class([
                                    'flex h-11 cursor-pointer items-center rounded-full px-4 text-[15px] font-bold transition-colors has-focus-visible:ring-2 has-focus-visible:ring-foret',
                                    'bg-foret text-white' => $sujet === $choix,
                                    'border-[1.5px] border-ligne bg-white text-foret hover:border-foret' => $sujet !== $choix,
                                ])>
                                    <input type="radio" wire:model.live="sujet" value="{{ $choix }}" class="sr-only">{{ $choix }}
                                </label>
                            @endforeach
                        </div>
                        @error('sujet') <p class="text-sm font-semibold">{{ $message }}</p> @enderror
                    </fieldset>

                    <div class="flex flex-col gap-2">
                        <label for="contenu" class="text-[15px] font-bold">Ton message</label>
                        <textarea id="contenu" wire:model="contenu" rows="6" maxlength="3000" @error('contenu') aria-invalid="true" aria-describedby="contenu-erreur" @enderror
                            @class(['rounded-2xl border-[1.5px] bg-white px-4 py-3.5 text-base leading-normal text-foret outline-none focus:border-foret', 'border-peche bg-peche-clair' => $errors->has('contenu'), 'border-ligne' => ! $errors->has('contenu')])></textarea>
                        @error('contenu') <p id="contenu-erreur" class="text-sm font-semibold">{{ $message }}</p> @enderror
                    </div>

                    <div class="hidden" aria-hidden="true">
                        <label for="site">Ne pas remplir</label>
                        <input id="site" type="text" wire:model="site" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <p class="text-[13px] leading-normal text-mousse md:max-w-[340px]">Tes informations servent uniquement à te répondre. <a href="{{ route('confidentialite') }}" wire:navigate class="font-bold text-foret underline underline-offset-2">Confidentialité</a></p>
                        <button type="submit" wire:loading.attr="disabled" class="flex h-14 items-center justify-center rounded-2xl bg-foret px-8 text-[17px] font-bold text-white hover:bg-vert disabled:opacity-60">
                            <span wire:loading.remove wire:target="envoyer">Envoyer</span>
                            <span wire:loading wire:target="envoyer">Envoi…</span>
                        </button>
                    </div>
                </form>
            @endif
        </div>

        {{-- À côté --}}
        <aside class="flex flex-col gap-3.5 lg:w-[380px] lg:shrink-0">
            <div class="flex flex-col gap-3 rounded-[20px] bg-brume p-5 md:rounded-3xl md:p-7">
                <h2 class="text-[17px] font-bold md:text-xl">Avant d'écrire</h2>
                <p class="text-[15px] leading-normal text-mousse">La réponse est peut-être déjà là :</p>
                <ul class="flex flex-col">
                    @foreach ([
                        [route('faq').'#tcf', 'TCF Canada ou Tout public ?'],
                        [route('scores'), 'Quel score pour quel NCLC ?'],
                        [route('tests-blancs'), 'Comment se passe un test blanc ?'],
                        [route('faq').'#compte', 'Paiement et compte'],
                    ] as [$url, $libelle])
                        <li class="border-t-[1.5px] border-ligne first:border-t-0">
                            <a href="{{ $url }}" wire:navigate class="flex min-h-12 items-center justify-between gap-3 text-[15px] font-semibold text-foret">{{ $libelle }}<x-icone nom="droite" class="size-5 shrink-0" /></a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="flex flex-col gap-2 rounded-[20px] bg-peche-clair p-5 md:rounded-3xl md:p-7">
                <h2 class="text-[17px] font-bold md:text-xl">Une erreur dans une question&nbsp;?</h2>
                <p class="text-[15px] leading-normal">Choisis « Signaler une erreur » et indique l'épreuve et l'énoncé : on corrige vite.</p>
            </div>
            <p class="px-1 text-[13px] leading-normal text-mousse">Edumica est une préparation indépendante. Pour ton inscription au TCF ou tes résultats officiels, contacte ton centre d'examen.</p>
        </aside>
    </section>
</x-site.page>
