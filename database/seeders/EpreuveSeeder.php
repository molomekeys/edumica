<?php

namespace Database\Seeders;

use App\Models\Epreuve;
use Illuminate\Database\Seeder;

/**
 * Les 4 épreuves du TCF et quelques questions d'exemple pour les quiz.
 */
class EpreuveSeeder extends Seeder
{
    public function run(): void
    {
        $epreuves = [
            ['comprehension-orale', 'co', 'Compréhension orale', 'Dialogues, annonces et messages à écouter une seule fois.', '39 questions · 35 min', 'casque'],
            ['comprehension-ecrite', 'ce', 'Compréhension écrite', 'Courriels, articles et documents du quotidien.', '39 questions · 60 min', 'livre'],
            ['expression-ecrite', 'ee', 'Expression écrite', 'Trois rédactions, du message court au texte argumenté.', '3 tâches · 60 min', 'crayon'],
            ['expression-orale', 'eo', 'Expression orale', "Entretien, échange d'informations et point de vue.", '3 tâches · 12 min', 'micro'],
        ];

        foreach ($epreuves as $ordre => [$slug, $code, $nom, $description, $format, $icone]) {
            Epreuve::updateOrCreate(['slug' => $slug], compact('code', 'nom', 'description', 'format', 'icone', 'ordre'));
        }

        $this->questions('co', [
            [
                'enonce' => 'Où se trouve la personne qui parle ?',
                'duree_audio' => 42,
                'transcription' => "— Bonjour, je voudrais un sirop contre la toux, s'il vous plaît.\n— Vous avez une ordonnance ?\n— Oui, la voici. Mon médecin me l'a faite ce matin.",
                'choix' => ['À la gare', 'À la pharmacie', 'À la banque', 'Au cinéma'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as bien repéré les indices du lieu.',
                'explication' => "La personne demande un sirop contre la toux et parle d'une ordonnance : elle est à la pharmacie.",
            ],
            [
                'enonce' => 'Pourquoi Claire laisse-t-elle ce message ?',
                'duree_audio' => 28,
                'transcription' => "Bonjour, c'est Claire, du cabinet dentaire. Votre rendez-vous de jeudi est déplacé à vendredi, même heure. Rappelez-nous si ce n'est pas possible pour vous.",
                'choix' => ['Pour annuler un rendez-vous', 'Pour déplacer un rendez-vous', 'Pour confirmer un paiement', 'Pour prendre des nouvelles'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as bien compris le changement de date.',
                'explication' => 'Claire dit que le rendez-vous « est déplacé à vendredi » : il n\'est pas annulé, il change de jour.',
            ],
            [
                'enonce' => 'Quel est le problème annoncé ?',
                'duree_audio' => 18,
                'transcription' => 'Mesdames et messieurs, le train à destination de Lyon partira avec vingt minutes de retard, voie 7. Nous vous prions de nous excuser pour la gêne occasionnée.',
                'choix' => ['Le train est annulé', 'Le train change de voie', 'Le train part en retard', 'Le train est complet'],
                'bonne_reponse' => 2,
                'feedback' => "Tu as bien repéré l'information clé.",
                'explication' => "L'annonce parle de « vingt minutes de retard ». La voie 7 est simplement indiquée, elle ne change pas.",
            ],
            [
                'enonce' => 'Où et quand les deux amis vont-ils se retrouver ?',
                'duree_audio' => 22,
                'transcription' => "— On se retrouve au café à midi ?\n— Midi, c'est un peu tôt pour moi.\n— Alors disons treize heures, devant la bibliothèque.\n— Parfait, à tout à l'heure !",
                'choix' => ['Au café à midi', 'Devant la bibliothèque à 13 h', 'Au café à 13 h', 'Devant la bibliothèque à midi'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as suivi le changement de programme.',
                'explication' => 'La première proposition (le café à midi) est refusée. La seconde, « treize heures, devant la bibliothèque », est acceptée.',
            ],
            [
                'enonce' => 'Quel temps fera-t-il demain en fin de journée ?',
                'duree_audio' => 20,
                'transcription' => "Demain, le soleil brillera toute la matinée. Mais attention : de fortes pluies sont attendues en fin d'après-midi sur l'ensemble de la région.",
                'choix' => ['Ensoleillé', 'Pluvieux', 'Neigeux', 'Venteux'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as bien fait attention au « mais ».',
                'explication' => "Le soleil concerne la matinée. Pour la fin d'après-midi, on annonce « de fortes pluies ».",
            ],
        ]);

        $this->questions('ce', [
            [
                'enonce' => 'Que doivent apporter les participants ?',
                'support' => "Bonjour à tous,\nLa réunion de lundi est reportée au mercredi 14 h, en salle B. Merci d'apporter vos rapports mensuels.\nSophie",
                'choix' => ['Leur ordinateur', 'Leurs rapports mensuels', 'Un repas', 'Leur badge'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as trouvé la consigne dans le courriel.',
                'explication' => "Sophie écrit « Merci d'apporter vos rapports mensuels ».",
            ],
            [
                'enonce' => 'Pourquoi la piscine ferme-t-elle ?',
                'support' => "Piscine municipale\nFermeture exceptionnelle du 3 au 9 août pour travaux d'entretien. Réouverture le 10 août à 8 h.",
                'choix' => ['Pour des travaux', 'Pour les vacances du personnel', 'Pour une compétition', "Par manque d'eau"],
                'bonne_reponse' => 0,
                'feedback' => 'Tu as repéré la cause de la fermeture.',
                'explication' => "L'affiche indique une fermeture « pour travaux d'entretien ».",
            ],
            [
                'enonce' => 'Quelle condition le propriétaire impose-t-il ?',
                'support' => 'Loue studio meublé, 25 m², centre-ville, proche métro. Libre au 1er septembre. Non-fumeur uniquement.',
                'choix' => ['Avoir un animal', 'Ne pas fumer', 'Être étudiant', "Payer un an d'avance"],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as lu l\'annonce jusqu\'au bout.',
                'explication' => "L'annonce se termine par « Non-fumeur uniquement ».",
            ],
            [
                'enonce' => 'Quel jour peut-on aller à la bibliothèque à 20 h ?',
                'support' => "Horaires de la bibliothèque\nDu mardi au samedi, de 10 h à 19 h. Nocturne le jeudi jusqu'à 21 h. Fermée le dimanche et le lundi.",
                'choix' => ['Le lundi', 'Le jeudi', 'Le samedi', 'Le dimanche'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as bien croisé les horaires.',
                'explication' => "La bibliothèque ferme à 19 h, sauf le jeudi : la nocturne va jusqu'à 21 h.",
            ],
            [
                'enonce' => "Selon le texte, qu'est-ce qui explique ce changement ?",
                'support' => 'De plus en plus de citadins choisissent le vélo pour aller au travail. Selon la mairie, les nouvelles pistes cyclables ont largement encouragé ce changement.',
                'choix' => ["Le prix de l'essence", 'Les nouvelles pistes cyclables', 'La météo', 'Les grèves de transport'],
                'bonne_reponse' => 1,
                'feedback' => 'Tu as trouvé la cause citée dans le texte.',
                'explication' => 'Le texte attribue le changement aux « nouvelles pistes cyclables ».',
            ],
        ]);
    }

    /** @param  list<array<string, mixed>>  $questions */
    private function questions(string $code, array $questions): void
    {
        $epreuve = Epreuve::where('code', $code)->firstOrFail();
        $epreuve->questions()->delete();

        foreach ($questions as $ordre => $question) {
            $epreuve->questions()->create($question + ['ordre' => $ordre]);
        }
    }
}
