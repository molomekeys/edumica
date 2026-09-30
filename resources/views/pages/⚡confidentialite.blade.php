<?php

use Livewire\Component;

new class extends Component
{
    /** Les passages entre crochets restent à compléter avant la mise en ligne. Traduits par site.document (lang/ar.json). */
    public const SECTIONS = [
        ['Qui est responsable de tes données', [
            'Le responsable du traitement est [RAISON SOCIALE], [ADRESSE DU SIÈGE]. Pour toute question sur tes données : [E-MAIL DE CONTACT].',
        ]],
        ['Les données que nous collectons', [
            [
                'Les réponses à tes quiz et tests blancs, pour calculer tes résultats et ton bilan.',
                'Ton nom, ton adresse e-mail et ton message quand tu nous écris depuis la page contact.',
                '[DONNÉES DE COMPTE, SI UN COMPTE EST CRÉÉ]',
                "Des données techniques (adresse IP, type de navigateur) nécessaires au fonctionnement et à la sécurité du site.",
            ],
            "Nous ne collectons pas tes données bancaires : le paiement est traité directement par [PRESTATAIRE DE PAIEMENT].",
        ]],
        ['Pourquoi nous les utilisons', [
            [
                'Te fournir les quiz, les tests blancs et ton bilan.',
                'Répondre à tes messages.',
                'Traiter tes achats et respecter nos obligations comptables.',
                'Protéger le site contre les abus.',
            ],
            'Nous ne vendons pas tes données et ne les utilisons pas à des fins publicitaires.',
        ]],
        ['Combien de temps nous les gardons', [
            '[DURÉES DE CONSERVATION : RÉSULTATS, MESSAGES, DONNÉES DE COMPTE, FACTURES]',
        ]],
        ['Avec qui nous les partageons', [
            'Tes données sont accessibles uniquement à l\'équipe Edumica et à nos prestataires techniques, dans la limite de leur mission :',
            ['Hébergement : [NOM DE L\'HÉBERGEUR]', 'Paiement : [PRESTATAIRE DE PAIEMENT]', 'Envoi des e-mails : [PRESTATAIRE D\'E-MAIL]'],
        ]],
        ['Cookies', [
            "Le site utilise des cookies strictement nécessaires à son fonctionnement, par exemple pour garder ta session active pendant un quiz ou retenir la langue choisie. [COOKIES DE MESURE D'AUDIENCE, LE CAS ÉCHÉANT]",
        ]],
        ['Tes droits', [
            "Tu peux à tout moment demander l'accès à tes données, leur rectification ou leur suppression, t'opposer à leur traitement ou demander leur portabilité.",
            "Pour exercer ces droits, écris-nous depuis la page contact. Tu peux aussi adresser une réclamation à l'autorité de protection des données compétente : [AUTORITÉ COMPÉTENTE].",
        ]],
    ];

    public function render()
    {
        return $this->view()->title(__('Politique de confidentialité'));
    }
};
?>

<x-site.document :titre="__('Confidentialité')" :intro="__('Les données que nous collectons, pourquoi, et comment exercer tes droits.')" :sections="$this::SECTIONS" />
