<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Premiers articles du blog, sur la préparation au TCF. Rejouable : les articles sont retrouvés par leur slug.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $auteur = User::where('is_admin', true)->first();

        foreach ($this->articles() as $i => $article) {
            Article::updateOrCreate(['slug' => $article['slug']], [
                ...$article,
                'user_id' => $auteur?->id,
                'statut' => Article::PUBLIE,
                'publie_le' => now()->subDays(3 + $i * 5)->setTime(9, 0),
            ]);
        }
    }

    /**
     * @return list<array{titre: string, slug: string, categorie: string, extrait: string, meta_description: string, contenu: string}>
     */
    private function articles(): array
    {
        return [
            [
                'titre' => 'Préparer le TCF Canada en 8 semaines : le plan semaine par semaine',
                'slug' => 'preparer-le-tcf-canada-en-8-semaines',
                'categorie' => 'Conseils TCF',
                'extrait' => "Deux mois, c'est assez pour gagner un ou deux niveaux NCLC si tu t'organises. Voici un plan réaliste, épreuve par épreuve, avec des tests blancs aux bons moments.",
                'meta_description' => 'Un plan de révision de 8 semaines pour le TCF Canada : diagnostic, travail ciblé par épreuve et tests blancs chronométrés.',
                'contenu' => <<<'HTML'
                    <p>Huit semaines, c'est le délai que beaucoup de candidats ont entre l'inscription au TCF Canada et le jour de l'examen. C'est court, mais c'est suffisant pour progresser nettement si chaque séance a un objectif précis. Le piège, c'est de réviser « un peu de tout » sans savoir où tu perds des points.</p>
                    <h2>Semaine 1 : le diagnostic</h2>
                    <p>Commence par mesurer ton niveau réel sur les quatre épreuves. Fais un quiz par épreuve, puis un premier test blanc complet dans les conditions de l'examen : chronomètre, une seule écoute, pas de dictionnaire.</p>
                    <ul>
                        <li>Note ton score estimé et ton niveau NCLC pour chaque épreuve.</li>
                        <li>Compare-le au niveau exigé par ton programme d'immigration.</li>
                        <li>Repère l'épreuve où l'écart est le plus grand : c'est ta priorité.</li>
                    </ul>
                    <h2>Semaines 2 à 5 : le travail ciblé</h2>
                    <p>Consacre environ la moitié de ton temps à ton épreuve la plus faible, et répartis le reste entre les trois autres. Pour les compréhensions, l'entraînement sur des questions de difficulté croissante est le plus rentable. Pour les expressions, écris et parle chaque jour, même dix minutes.</p>
                    <blockquote><p>Une heure par jour pendant cinq semaines vaut mieux que dix heures le dimanche. La régularité fait la différence au TCF.</p></blockquote>
                    <h2>Semaines 6 et 7 : les tests blancs</h2>
                    <p>Passe un test blanc complet par semaine, idéalement à la même heure que ta convocation. Analyse chaque erreur : manque de vocabulaire, piège de reformulation, gestion du temps ? Chaque type d'erreur appelle une correction différente.</p>
                    <h2>Semaine 8 : la consolidation</h2>
                    <p>La dernière semaine n'est pas le moment d'apprendre de nouvelles notions. Relis tes fiches, refais les questions que tu avais ratées et dors bien la veille. Le jour J, arrive en avance avec ta pièce d'identité et ta convocation.</p>
                    HTML,
            ],
            [
                'titre' => 'Compréhension orale : 7 pièges qui font perdre des points',
                'slug' => 'comprehension-orale-7-pieges',
                'categorie' => 'Compréhension orale',
                'extrait' => "Une seule écoute, des questions qui reformulent, des distracteurs bien construits… Voici les pièges les plus fréquents de l'épreuve et comment les déjouer.",
                'meta_description' => 'Les pièges classiques de la compréhension orale du TCF et les réflexes pour les éviter : lecture des questions, reformulations, distracteurs.',
                'contenu' => <<<'HTML'
                    <p>La compréhension orale du TCF compte 39 questions, de la plus simple à la plus difficile. Chaque document n'est entendu qu'une seule fois : la moindre seconde d'inattention coûte cher. Bonne nouvelle, la plupart des erreurs viennent de quelques pièges bien identifiés.</p>
                    <h2>Les pièges de lecture</h2>
                    <ol>
                        <li><strong>Ne pas lire la question avant l'écoute.</strong> Profite du temps de lecture pour savoir ce que tu cherches : un lieu, une intention, une opinion ?</li>
                        <li><strong>Choisir le mot entendu.</strong> La bonne réponse reformule presque toujours ; la réponse qui reprend un mot exact du document est souvent un distracteur.</li>
                        <li><strong>Confondre le sujet et le détail.</strong> « Quel est le thème principal ? » n'appelle pas le premier exemple cité.</li>
                    </ol>
                    <h2>Les pièges d'écoute</h2>
                    <ol start="4">
                        <li><strong>Décrocher après une phrase difficile.</strong> Accepte de ne pas tout comprendre et reste concentré sur la suite.</li>
                        <li><strong>Ignorer les connecteurs.</strong> « Pourtant », « en revanche », « finalement » changent souvent le sens de tout le message.</li>
                        <li><strong>Négliger le ton.</strong> Pour les questions d'intention (se plaindre, conseiller, refuser), l'intonation compte autant que les mots.</li>
                        <li><strong>Revenir sur une question passée.</strong> Une fois la question suivante lancée, choisis et avance.</li>
                    </ol>
                    <blockquote><p>La bonne réponse dit la même chose avec d'autres mots. Entraîne-toi à reconnaître les synonymes et les tournures équivalentes.</p></blockquote>
                    <h2>Comment s'entraîner</h2>
                    <p>Écoute chaque jour un document authentique court : radio, podcast, annonce. Résume-le en une phrase sans le réécouter. Puis, sur Edumica, enchaîne les quiz de compréhension orale : la correction détaillée t'explique pourquoi chaque distracteur était faux.</p>
                    HTML,
            ],
            [
                'titre' => 'Expression écrite : réussir les trois tâches en 60 minutes',
                'slug' => 'expression-ecrite-reussir-les-trois-taches',
                'categorie' => 'Expression écrite',
                'extrait' => "Message, article, texte argumentatif : les trois tâches n'attendent pas la même chose. Voici comment répartir ton heure et ce que regardent les correcteurs.",
                'meta_description' => "Méthode pour l'expression écrite du TCF Canada : gestion du temps, structure de chaque tâche et critères d'évaluation.",
                'contenu' => <<<'HTML'
                    <p>L'expression écrite dure 60 minutes pour trois tâches de difficulté croissante. La première erreur des candidats est de passer trop de temps sur la tâche 1 et de bâcler la tâche 3, qui demande le plus de réflexion.</p>
                    <h2>Répartir ton temps</h2>
                    <ul>
                        <li><strong>Tâche 1</strong> (message court, 60 à 120 mots) : environ 10 minutes.</li>
                        <li><strong>Tâche 2</strong> (article ou récit, 120 à 150 mots) : environ 20 minutes.</li>
                        <li><strong>Tâche 3</strong> (texte argumentatif à partir de deux documents, 120 à 180 mots) : environ 30 minutes.</li>
                    </ul>
                    <p>Garde toujours deux ou trois minutes par tâche pour te relire : accords, conjugaisons, ponctuation.</p>
                    <h2>Ce que regardent les correcteurs</h2>
                    <p>Ta copie est notée sur 20. Les correcteurs évaluent si tu réponds à la consigne, la cohérence de ton texte, l'étendue de ton vocabulaire et la correction grammaticale. Un texte simple mais bien construit vaut mieux qu'un texte ambitieux truffé d'erreurs.</p>
                    <blockquote><p>Respecte le nombre de mots demandé : trop court, tu perds des points ; beaucoup trop long, tu perds du temps pour la tâche suivante.</p></blockquote>
                    <h2>Une structure qui marche pour la tâche 3</h2>
                    <ol>
                        <li>Une phrase d'introduction qui présente le débat.</li>
                        <li>Un paragraphe qui résume la position du premier document.</li>
                        <li>Un paragraphe pour le second document, avec un connecteur d'opposition.</li>
                        <li>Ton avis personnel, justifié par un exemple.</li>
                    </ol>
                    <p>Prépare à l'avance une petite banque de connecteurs (« d'une part… d'autre part », « néanmoins », « c'est pourquoi ») : ils rendent ton texte fluide sans effort le jour J.</p>
                    HTML,
            ],
            [
                'titre' => 'Scores NCLC : quel niveau viser pour Entrée express ?',
                'slug' => 'scores-nclc-quel-niveau-viser-entree-express',
                'categorie' => 'Immigration Canada',
                'extrait' => 'NCLC 7, NCLC 9… Ces chiffres pèsent lourd dans ton score CRS. On fait le point sur les niveaux à viser et sur la façon dont tes résultats au TCF sont convertis.',
                'meta_description' => 'Comprendre la conversion des résultats du TCF Canada en niveaux NCLC et le niveau à viser pour Entrée express.',
                'contenu' => <<<'HTML'
                    <p>Pour immigrer au Canada par Entrée express, ton niveau de français est traduit en NCLC (Niveaux de compétence linguistique canadiens), de 1 à 12. Chaque épreuve du TCF Canada reçoit son propre niveau : c'est la plus faible qui limite souvent ton dossier.</p>
                    <h2>Comment tes résultats sont convertis</h2>
                    <p>Les compréhensions sont notées de 0 à 699 points, les expressions de 0 à 20. IRCC publie un barème qui fait correspondre chaque plage de score à un niveau NCLC. Sur Edumica, la page des scores NCLC te permet de convertir tes résultats en un coup d'œil.</p>
                    <h2>Les seuils à connaître</h2>
                    <ul>
                        <li><strong>NCLC 7</strong> dans les quatre épreuves : c'est le minimum demandé par le programme des travailleurs qualifiés (fédéral).</li>
                        <li><strong>NCLC 9</strong> et plus : chaque niveau supplémentaire rapporte des points CRS, et le français en langue seconde donne des points bonus.</li>
                        <li>Certaines rondes d'invitation ciblent les francophones : un bon niveau en français peut faire la différence.</li>
                    </ul>
                    <blockquote><p>Les exigences évoluent régulièrement. Vérifie toujours les conditions à jour sur le site officiel d'IRCC avant de fixer ton objectif.</p></blockquote>
                    <h2>Fixer ton objectif</h2>
                    <p>Pars du niveau exigé par ton programme, puis vise un niveau au-dessus dans tes épreuves fortes pour gagner des points. Un test blanc te donne une estimation épreuve par épreuve : c'est le meilleur point de départ pour savoir où concentrer tes efforts.</p>
                    <p>N'oublie pas enfin que tes résultats sont valables deux ans pour IRCC : programme ton examen en fonction de la date de dépôt de ton dossier.</p>
                    HTML,
            ],
            [
                'titre' => 'Compréhension écrite : lire vite sans rater l’essentiel',
                'slug' => 'comprehension-ecrite-lire-vite',
                'categorie' => 'Compréhension écrite',
                'extrait' => '60 minutes pour 39 questions : en compréhension écrite, la gestion du temps compte autant que le niveau. Trois techniques de lecture pour avancer sans stress.',
                'meta_description' => 'Techniques de lecture pour la compréhension écrite du TCF : lecture en diagonale, repérage et gestion du temps sur 39 questions.',
                'contenu' => <<<'HTML'
                    <p>La compréhension écrite propose des documents de plus en plus longs et complexes : affiches, courriels, articles de presse, textes d'opinion. Avec un peu plus d'une minute trente par question en moyenne, tu ne peux pas tout lire mot à mot.</p>
                    <h2>Lire la question d'abord</h2>
                    <p>Avant de lire le document, lis la question et les choix de réponse. Tu sais alors ce que tu cherches et tu lis avec un objectif. Pour une affiche ou une annonce, la réponse tient souvent en une ligne.</p>
                    <h2>Trois techniques de lecture</h2>
                    <ul>
                        <li><strong>Le survol</strong> : titre, premier et dernier paragraphe, pour saisir le sujet et la conclusion.</li>
                        <li><strong>Le repérage</strong> : chercher un mot-clé, une date, un chiffre, sans lire le reste.</li>
                        <li><strong>La lecture fine</strong> : réservée au passage qui contient la réponse, pour vérifier les nuances.</li>
                    </ul>
                    <blockquote><p>Les premières questions sont les plus faciles : va vite au début pour garder du temps pour les textes longs de la fin.</p></blockquote>
                    <h2>Gérer son temps</h2>
                    <p>Fixe-toi des repères : environ 20 minutes pour les 15 premières questions, puis le reste pour les documents plus exigeants. Si une question te bloque, choisis la réponse la plus probable et passe à la suivante : il n'y a pas de points négatifs.</p>
                    <p>Pour t'entraîner, lis chaque jour un article de presse francophone et résume son idée principale en une phrase. C'est exactement la compétence que teste l'épreuve.</p>
                    HTML,
            ],
            [
                'titre' => 'La veille du TCF : la checklist pour arriver serein',
                'slug' => 'la-veille-du-tcf-checklist',
                'categorie' => 'Conseils TCF',
                'extrait' => "Pièce d'identité, horaires, sommeil, stratégie pour chaque épreuve : tout ce qu'il faut vérifier la veille pour ne rien laisser au hasard le jour de l'examen.",
                'meta_description' => 'La checklist de la veille du TCF Canada : documents, organisation, révisions légères et stratégie pour le jour J.',
                'contenu' => <<<'HTML'
                    <p>Tu as travaillé pendant des semaines : ne laisse pas un oubli ou une mauvaise nuit gâcher ta performance. La veille de l'examen sert à préparer le terrain, pas à réviser en urgence.</p>
                    <h2>Les documents</h2>
                    <ul>
                        <li>Ta pièce d'identité valide, celle utilisée à l'inscription.</li>
                        <li>Ta convocation, imprimée ou sur ton téléphone selon les consignes du centre.</li>
                        <li>L'adresse exacte du centre et l'heure d'arrivée demandée.</li>
                    </ul>
                    <h2>Les révisions légères</h2>
                    <p>Relis tes fiches de connecteurs et tes structures pour l'expression écrite et orale. Écoute un document court pour te mettre dans l'oreille, sans chercher à apprendre de nouvelles choses.</p>
                    <blockquote><p>La veille, une bonne nuit de sommeil vaut plus que trois heures de révisions supplémentaires.</p></blockquote>
                    <h2>Ta stratégie pour le jour J</h2>
                    <ol>
                        <li><strong>Compréhension orale</strong> : lis la question avant l'écoute, puis avance sans regret.</li>
                        <li><strong>Compréhension écrite</strong> : va vite sur les premières questions.</li>
                        <li><strong>Expression écrite</strong> : surveille ta montre et garde du temps pour la tâche 3.</li>
                        <li><strong>Expression orale</strong> : parle clairement, développe tes réponses et n'aie pas peur des silences courts.</li>
                    </ol>
                    <p>Et surtout : prévois un peu de marge sur le trajet. Arriver en avance, c'est déjà commencer l'examen dans le calme.</p>
                    HTML,
            ],
        ];
    }
}
