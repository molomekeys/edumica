<?php

namespace App\Support;

/**
 * Questions fréquentes, par thème. Les réponses entre crochets restent à compléter.
 */
class Faq
{
    /** Thème => [ancre, [question, réponse]...]. */
    public const THEMES = [
        'Le TCF' => ['tcf', [
            ['Est-ce un test officiel ?', "Non. Seul le TCF passé dans un centre agréé donne un résultat officiel. Edumica sert à t'entraîner."],
            ['Quelle différence entre TCF Canada et TCF Tout public ?', "Le TCF Canada est exigé par IRCC pour l'immigration au Canada, ses résultats se lisent en NCLC. Le TCF Tout public sert pour les études, le travail ou un projet personnel, et donne un niveau CECRL."],
            ['Combien de temps dure le TCF Canada ?', "Environ 2 h 45 pour les 4 épreuves : 35 minutes de compréhension orale, 60 minutes de compréhension écrite, 60 minutes d'expression écrite et 12 minutes d'expression orale."],
            ['Combien de temps mes résultats sont-ils valables ?', "Pour IRCC, les résultats d'un test de langue doivent dater de moins de deux ans au moment où tu déposes ta demande. Vérifie toujours la règle de ton programme."],
            ['Quel NCLC dois-je viser ?', "Cela dépend de ton programme d'immigration. Beaucoup demandent au moins le NCLC 7 dans les 4 compétences, mais certains acceptent moins et un score plus élevé rapporte souvent des points. Consulte la page de ton programme sur le site d'IRCC."],
        ]],
        'Les quiz' => ['quiz', [
            ['Les quiz sont-ils vraiment gratuits ?', 'Oui. Les quiz sont gratuits, sans carte bancaire, et tu peux en faire autant que tu veux.'],
            ['Comment se passe un quiz ?', "Un quiz, c'est 10 questions au format de l'épreuve, avec un chrono. Chaque réponse est corrigée et expliquée tout de suite, et tu retrouves ton récapitulatif à la fin."],
            ['Pourquoi une seule écoute en compréhension orale ?', "Parce que c'est la règle de l'examen. T'entraîner dans les mêmes conditions t'évite les mauvaises surprises le jour J. La transcription est disponible après la correction."],
            ['Y a-t-il des quiz pour les épreuves d\'expression ?', "Les quiz d'expression écrite et orale arrivent bientôt. En attendant, chaque page d'épreuve présente les tâches, des exemples de sujets et nos conseils."],
        ]],
        'Les tests blancs' => ['tests-blancs', [
            ['Mon niveau estimé est-il fiable ?', "C'est une estimation d'entraînement, calculée sur des épreuves au format de l'examen. Elle te donne une tendance solide pour savoir où tu en es, pas un résultat officiel."],
            ["Combien de temps dure l'accès à un test blanc ?", '[DURÉE D\'ACCÈS À UN TEST BLANC]'],
            ['Puis-je mettre un test blanc en pause ?', "[RÉPONSE : PAUSE POSSIBLE ENTRE LES ÉPREUVES ?] À l'intérieur d'une épreuve, le chrono continue, comme à l'examen."],
            ['Comment sont corrigées les épreuves d\'expression ?', '[RÉPONSE : MODE DE CORRECTION DE L\'EXPRESSION ÉCRITE ET ORALE]'],
        ]],
        'Compte et paiement' => ['compte', [
            ['Faut-il créer un compte ?', '[RÉPONSE : QUIZ SANS COMPTE ? COMPTE POUR LES TESTS BLANCS ?]'],
            ['Quels moyens de paiement acceptez-vous ?', '[MOYENS DE PAIEMENT ACCEPTÉS]'],
            ['Puis-je être remboursé ?', 'Les conditions de remboursement sont détaillées dans nos conditions générales de vente. [RÉSUMÉ DE LA POLITIQUE DE REMBOURSEMENT]'],
            ['Comment supprimer mes données ?', 'Écris-nous depuis la page contact : nous supprimons ton compte et tes données dans les délais prévus par notre politique de confidentialité.'],
        ]],
    ];

    /** Les questions affichées sur la page d'accueil. */
    public const ACCUEIL = [
        ['Le TCF', 0], ['Les tests blancs', 0], ['Le TCF', 1], ['Les tests blancs', 1], ['Compte et paiement', 0],
    ];

    /** @return list<array{0: string, 1: string}> */
    public static function theme(string $theme): array
    {
        return self::THEMES[$theme][1];
    }

    /** @return list<array{0: string, 1: string}> */
    public static function accueil(): array
    {
        return array_map(fn (array $ref) => self::THEMES[$ref[0]][1][$ref[1]], self::ACCUEIL);
    }
}
