<?php

use Livewire\Component;

new class extends Component
{
    /** Les passages entre crochets restent à compléter avant la mise en ligne. Traduits par site.document (lang/ar.json). */
    public const SECTIONS = [
        ['Objet', [
            "Les présentes conditions générales de vente encadrent l'achat des tests blancs proposés sur Edumica par [RAISON SOCIALE]. Tout achat implique leur acceptation.",
        ]],
        ['Services proposés', [
            'Edumica propose deux types de services :',
            [
                'des quiz gratuits, accessibles sans paiement ;',
                "des tests blancs payants, qui reprennent les 4 épreuves du TCF avec leur durée et donnent un niveau estimé CECRL et NCLC, accompagné d'un bilan.",
            ],
            "Ces services sont des entraînements non officiels. Ils ne remplacent pas l'examen et ne donnent aucun résultat officiel.",
        ]],
        ['Prix', [
            'Le prix d\'un test blanc est de [PRIX], [TTC OU HT, DEVISE]. Le prix applicable est celui affiché au moment de la commande.',
            'Nous pouvons modifier nos prix à tout moment, sans effet sur les commandes déjà validées.',
        ]],
        ['Commande et paiement', [
            'La commande est validée après paiement complet. Moyens de paiement acceptés : [MOYENS DE PAIEMENT].',
            'Le paiement est traité par [PRESTATAIRE DE PAIEMENT]. Edumica ne conserve pas tes données bancaires.',
        ]],
        ['Accès au test blanc', [
            'Le test blanc est accessible dès la confirmation du paiement, pendant [DURÉE D\'ACCÈS À UN TEST BLANC].',
            "Une connexion Internet stable et un navigateur à jour sont nécessaires. Une fois une épreuve commencée, son chrono ne s'arrête pas, comme à l'examen.",
        ]],
        ['Droit de rétractation', [
            '[RÈGLES APPLICABLES AU DROIT DE RÉTRACTATION POUR UN CONTENU NUMÉRIQUE FOURNI IMMÉDIATEMENT]',
        ]],
        ['Remboursement', [
            '[POLITIQUE DE REMBOURSEMENT : CAS COUVERTS, DÉLAIS, DÉMARCHE]',
            "En cas de problème technique de notre fait empêchant de passer le test blanc, écris-nous depuis la page contact : nous te proposerons une solution.",
        ]],
        ['Responsabilité', [
            "Les niveaux estimés sont donnés à titre indicatif. Edumica ne garantit pas l'obtention d'un résultat donné au TCF.",
        ]],
        ['Litiges et droit applicable', [
            'Les présentes conditions sont soumises au droit [PAYS]. En cas de litige, une solution amiable sera recherchée en priorité. [COORDONNÉES DU MÉDIATEUR DE LA CONSOMMATION, LE CAS ÉCHÉANT]',
        ]],
    ];

    public function render()
    {
        return $this->view()->title(__('Conditions générales de vente'));
    }
};
?>

<x-site.document :titre="__('Conditions générales de vente')" :intro="__('Les règles qui s\'appliquent quand tu achètes un test blanc sur Edumica.')" :sections="$this::SECTIONS" />
