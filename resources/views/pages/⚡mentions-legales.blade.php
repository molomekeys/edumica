<?php

use Livewire\Component;

new class extends Component
{
    /** Les passages entre crochets restent à compléter avant la mise en ligne. */
    public const SECTIONS = [
        ['Éditeur du site', [
            'Le site Edumica est édité par [RAISON SOCIALE], [FORME JURIDIQUE] au capital de [CAPITAL], immatriculée sous le numéro [NUMÉRO D\'IMMATRICULATION].',
            ['Siège social : [ADRESSE DU SIÈGE]', 'Adresse e-mail : [E-MAIL DE CONTACT]', 'Numéro de TVA : [NUMÉRO DE TVA]', 'Directeur ou directrice de la publication : [NOM]'],
        ]],
        ['Hébergement', [
            'Le site est hébergé par [NOM DE L\'HÉBERGEUR], [ADRESSE DE L\'HÉBERGEUR], [TÉLÉPHONE OU SITE DE L\'HÉBERGEUR].',
        ]],
        ['Indépendance vis-à-vis du TCF', [
            "Edumica est une préparation indépendante. Le site n'est ni affilié à France Éducation international, ni approuvé par cet organisme. TCF est une marque de France Éducation international.",
            "Les quiz et tests blancs proposés sont des entraînements non officiels. Les niveaux estimés CECRL et NCLC n'ont aucune valeur officielle : seul le TCF passé dans un centre agréé donne un résultat officiel.",
        ]],
        ['Propriété intellectuelle', [
            "Les contenus du site (textes, questions, explications, enregistrements, illustrations, logo et identité graphique) sont protégés par le droit d'auteur. Ils sont la propriété de [RAISON SOCIALE] ou utilisés avec l'autorisation de leurs auteurs.",
            "Toute reproduction ou diffusion, totale ou partielle, sans autorisation écrite préalable est interdite.",
        ]],
        ['Responsabilité', [
            "Nous faisons tout notre possible pour que les informations du site soient exactes et à jour, notamment sur le format des épreuves et les barèmes. Ces informations peuvent toutefois évoluer : vérifie toujours les exigences officielles auprès de ton centre d'examen et d'IRCC.",
            "Edumica ne peut être tenu responsable d'une décision prise sur la seule base d'un niveau estimé sur le site.",
        ]],
        ['Liens vers d\'autres sites', [
            "Le site peut contenir des liens vers des sites tiers. Nous n'avons pas de contrôle sur leur contenu et ne pouvons être tenus responsables de celui-ci.",
        ]],
        ['Données personnelles et cookies', [
            'Le traitement de tes données personnelles est détaillé dans notre politique de confidentialité.',
        ]],
    ];

    public function render()
    {
        return $this->view()->title('Mentions légales');
    }
};
?>

<x-site.document titre="Mentions légales" intro="Qui édite Edumica, qui l'héberge et les règles d'utilisation du site." :sections="$this::SECTIONS" />
