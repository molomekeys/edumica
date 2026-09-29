<?php

namespace App\Support;

/**
 * Contenu éditorial des pages d'épreuve (format TCF Canada).
 */
class GuideEpreuve
{
    /** Paliers CECRL => [code d'épreuve => [score minimal, score maximal]]. */
    public const CECRL = [
        'A1' => ['co' => [100, 199], 'ce' => [100, 199], 'eo' => [1, 3], 'ee' => [1, 3]],
        'A2' => ['co' => [200, 299], 'ce' => [200, 299], 'eo' => [4, 5], 'ee' => [4, 5]],
        'B1' => ['co' => [300, 399], 'ce' => [300, 399], 'eo' => [6, 9], 'ee' => [6, 9]],
        'B2' => ['co' => [400, 499], 'ce' => [400, 499], 'eo' => [10, 13], 'ee' => [10, 13]],
        'C1' => ['co' => [500, 599], 'ce' => [500, 599], 'eo' => [14, 15], 'ee' => [14, 15]],
        'C2' => ['co' => [600, 699], 'ce' => [600, 699], 'eo' => [16, 20], 'ee' => [16, 20]],
    ];

    /**
     * Contenu par code d'épreuve.
     *
     * - duree : durée de l'épreuve le jour de l'examen
     * - chiffres : [valeur, légende]
     * - etapes : [repère, durée ou longueur, titre, texte, exemple de sujet ou null]
     * - evalue : ce que l'épreuve mesure
     * - conseils : [titre, texte]
     * - pieges : erreurs fréquentes
     */
    public const CONTENU = [
        'co' => [
            'duree' => '35 min',
            'accroche' => 'Des dialogues, des annonces et des messages de la vie courante, à écouter une seule fois. Les questions vont du plus simple au plus difficile.',
            'chiffres' => [
                ['39', 'questions à choix multiple'],
                ['35 min', "pour toute l'épreuve"],
                ['1 écoute', 'par enregistrement'],
                ['699', 'score maximal, dès 100'],
            ],
            'titre_etapes' => 'Une difficulté qui monte',
            'intro_etapes' => 'Les enregistrements sont classés par niveau : les premières questions visent A1, les dernières C2. Chaque bonne réponse rapporte plus de points à mesure que le niveau monte.',
            'etapes' => [
                ['A1 – A2', 'Premières questions', 'Le quotidien', 'Messages courts et situations simples : un achat, un rendez-vous, une annonce en gare. On te demande surtout de repérer une information précise.', null],
                ['B1 – B2', 'Milieu de l\'épreuve', 'Conversations et médias', "Échanges plus longs, interviews, extraits de radio. Il faut comprendre l'idée principale, l'opinion ou l'intention de la personne qui parle.", null],
                ['C1 – C2', 'Dernières questions', 'Débats et exposés', 'Discours argumentés, débats, conférences. Le vocabulaire est plus abstrait et les nuances comptent : réserve, ironie, sous-entendu.', null],
            ],
            'evalue' => [
                'Repérer une information précise : un lieu, une heure, un prix',
                "Comprendre l'idée générale d'un échange",
                "Identifier l'intention ou l'opinion de la personne qui parle",
                'Suivre une argumentation et en saisir les nuances',
            ],
            'conseils' => [
                ["Lis la question avant l'écoute", "Tu sais ce que tu cherches avant que l'enregistrement commence : ton attention va directement à l'information utile."],
                ['Ne reste pas bloqué', "Tu n'as qu'une écoute. Si tu as manqué la réponse, choisis la plus probable et passe à la suivante sans laisser de case vide."],
                ['Écoute les mots de liaison', "« Mais », « pourtant », « en fait » annoncent souvent la bonne réponse : c'est là que l'avis de la personne se précise."],
                ['Entraîne ton oreille chaque jour', "Radio, podcasts, journaux télévisés : quinze minutes d'écoute active par jour valent mieux qu'une longue séance par semaine."],
            ],
            'pieges' => [
                "Choisir une réponse parce qu'on y retrouve un mot entendu : c'est souvent le piège.",
                'Confondre ce qui est proposé et ce qui est finalement décidé.',
                'Perdre du temps sur une question difficile au lieu de se concentrer sur la suivante.',
            ],
        ],
        'ce' => [
            'duree' => '60 min',
            'accroche' => "Des courriels, des annonces, des articles et des textes d'opinion. Les documents s'allongent et se complexifient au fil des questions.",
            'chiffres' => [
                ['39', 'questions à choix multiple'],
                ['60 min', "pour toute l'épreuve"],
                ['1 min 30', 'en moyenne par question'],
                ['699', 'score maximal, dès 100'],
            ],
            'titre_etapes' => 'Des textes de plus en plus exigeants',
            'intro_etapes' => "Comme à l'oral, les documents sont classés du plus simple au plus difficile. Tu peux revenir sur une question tant que le temps n'est pas écoulé : garde de la marge pour les textes longs.",
            'etapes' => [
                ['A1 – A2', 'Premières questions', 'Documents du quotidien', 'Panneaux, horaires, petites annonces, messages courts. Il suffit souvent de trouver une information au bon endroit.', null],
                ['B1 – B2', 'Milieu de l\'épreuve', 'Courriels et articles', 'Lettres, courriels, articles de presse. Il faut comprendre le sens général, le but du texte et les informations importantes.', null],
                ['C1 – C2', 'Dernières questions', 'Textes argumentés', "Éditoriaux, essais, textes spécialisés ou littéraires. On attend une lecture fine du point de vue et du ton de l'auteur.", null],
            ],
            'evalue' => [
                'Trouver une information dans un document du quotidien',
                "Comprendre le sens général et le but d'un texte",
                'Distinguer les faits des opinions',
                "Saisir le point de vue et le ton de l'auteur",
            ],
            'conseils' => [
                ['Gère ton temps', "Un peu plus d'une minute et demie par question en moyenne. Va vite sur les premiers documents pour garder du temps pour les textes longs."],
                ['Lis les questions en premier', 'Tu sauras quoi chercher et tu éviteras de relire tout le texte plusieurs fois.'],
                ['Repère la structure', 'Titre, premier et dernier paragraphe, connecteurs : ils donnent le plan du texte avant même de tout lire.'],
                ['Méfie-toi des mots identiques', "La bonne réponse reformule rarement avec les mêmes mots. Cherche le sens, pas l'expression recopiée."],
            ],
            'pieges' => [
                'Lire chaque texte en entier avant de regarder la question.',
                "Répondre avec ses connaissances plutôt qu'avec ce que dit le texte.",
                'Passer trop de temps sur les premiers documents, les plus simples.',
            ],
        ],
        'ee' => [
            'duree' => '60 min',
            'accroche' => 'Trois rédactions en une heure, du message court au texte argumenté. Ta production est notée sur 20.',
            'chiffres' => [
                ['3', 'tâches à rédiger'],
                ['60 min', 'pour les trois'],
                ['60 – 180', 'mots selon la tâche'],
                ['20', 'note maximale, sur 20'],
            ],
            'titre_etapes' => 'Les trois tâches',
            'intro_etapes' => "Tu gères ton heure comme tu veux entre les trois textes. Chaque tâche demande un type d'écrit différent et une longueur précise.",
            'etapes' => [
                ['Tâche 1', '60 à 120 mots', 'Un message', 'Écrire à un ou plusieurs destinataires pour décrire, raconter ou expliquer : inviter un ami, donner des nouvelles, demander un renseignement.', "Tu viens d'emménager dans une nouvelle ville. Écris un message à un ami pour lui décrire ton quartier et l'inviter à venir te voir."],
                ['Tâche 2', '120 à 150 mots', 'Un récit ou un article', 'Raconter une expérience et la commenter pour un blog, un journal ou un forum, en donnant tes impressions.', "Sur un forum, des lecteurs racontent un voyage qui les a marqués. Raconte le tien et explique ce qu'il t'a apporté."],
                ['Tâche 3', '120 à 180 mots', 'Un texte argumenté', 'Comparer deux points de vue présentés dans deux courts documents, puis donner et défendre ta propre position.', 'Faut-il interdire les voitures dans le centre des villes ? Deux documents présentent des avis opposés. Compare-les et donne ton opinion.'],
            ],
            'evalue' => [
                'Réaliser la tâche demandée : destinataire, registre, longueur',
                'Organiser tes idées avec des connecteurs clairs',
                'Utiliser un vocabulaire varié et précis',
                "Maîtriser la grammaire, la conjugaison et l'orthographe",
            ],
            'conseils' => [
                ['Garde du temps pour la tâche 3', "C'est la plus exigeante. Un repère : 10 minutes pour la tâche 1, 20 pour la tâche 2, 30 pour la tâche 3."],
                ['Reste dans la fourchette de mots', "Un texte trop court ne montre pas assez ce que tu sais faire, un texte trop long augmente le risque d'erreurs."],
                ['Adapte le registre', "Tutoie un ami, vouvoie un inconnu, soigne les formules d'ouverture et de clôture. C'est facile à gagner."],
                ['Relis-toi', 'Garde deux ou trois minutes par texte pour les accords, les accents et la ponctuation.'],
            ],
            'pieges' => [
                'Réciter un texte appris par cœur qui ne répond pas exactement à la consigne.',
                'Oublier de comparer les deux documents à la tâche 3 et donner seulement son avis.',
                'Enchaîner les phrases sans connecteurs ni paragraphes.',
            ],
        ],
        'eo' => [
            'duree' => '12 min',
            'accroche' => 'Un face-à-face de 12 minutes avec un examinateur, en trois tâches. Ta prestation est notée sur 20.',
            'chiffres' => [
                ['3', 'tâches à l\'oral'],
                ['12 min', 'de face-à-face'],
                ['2 min', 'de préparation, tâche 2'],
                ['20', 'note maximale, sur 20'],
            ],
            'titre_etapes' => 'Les trois tâches',
            'intro_etapes' => "Les tâches s'enchaînent sans pause. Seule la deuxième laisse un temps de préparation : les deux autres se jouent en direct.",
            'etapes' => [
                ['Tâche 1', '2 min · sans préparation', 'Entretien dirigé', "L'examinateur te pose des questions sur toi : ton parcours, tes habitudes, tes projets. Il s'agit de parler de toi simplement et clairement.", "Présente-toi. Que fais-tu dans la vie ? Pourquoi veux-tu t'installer au Canada ?"],
                ['Tâche 2', '5 min 30 · 2 min de préparation', 'Exercice en interaction', "Tu joues une situation de la vie courante et tu poses des questions à l'examinateur pour obtenir des informations.", "Tu veux t'inscrire dans un club de sport. Pose des questions à l'employé pour connaître les horaires, les tarifs et les activités."],
                ['Tâche 3', '4 min 30 · sans préparation', "Expression d'un point de vue", 'Tu donnes ton opinion sur un sujet de société et tu la défends avec des arguments et des exemples.', "Certains pensent que le télétravail devrait devenir la norme. Qu'en penses-tu ?"],
            ],
            'evalue' => [
                'Te faire comprendre clairement : prononciation, débit',
                'Interagir : poser des questions, relancer, reformuler',
                'Argumenter et nuancer ton point de vue',
                'Utiliser un vocabulaire et une grammaire adaptés',
            ],
            'conseils' => [
                ["Parle jusqu'au bout", "Le temps est court : n'attends pas qu'on te relance, développe tes réponses avec des exemples."],
                ['Prépare ta présentation', 'La tâche 1 se prépare à l\'avance : parcours, métier, loisirs, projet au Canada.'],
                ['Varie tes questions', "En tâche 2, alterne les formes : « est-ce que », l'inversion, les mots interrogatifs. Pense aussi à réagir aux réponses."],
                ['Structure ton avis', 'En tâche 3 : ton opinion, deux arguments avec un exemple chacun, puis une courte conclusion.'],
            ],
            'pieges' => [
                'Répondre par une phrase courte et attendre la question suivante.',
                "Lire une liste de questions en tâche 2 sans écouter les réponses de l'examinateur.",
                "Donner un avis sans l'appuyer sur des exemples concrets.",
            ],
        ],
    ];

    /** @return array<string, mixed> */
    public static function pour(string $code): array
    {
        return self::CONTENU[$code];
    }

    public static function duree(string $code): string
    {
        return self::CONTENU[$code]['duree'];
    }

    /** Palier CECRL => plage de scores, pour une épreuve. */
    public static function cecrl(string $code): array
    {
        return array_map(fn (array $plages) => $plages[$code], self::CECRL);
    }
}
