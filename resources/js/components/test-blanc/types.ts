/** Question d'un test blanc en cours : ni bonne réponse, ni explication. */
export type QuestionTest = {
    index: number;
    id: number;
    epreuve_id: number;
    categorie: string;
    enonce: string;
    support: string | null;
    audio: string | null;
    transcription: string | null;
    duree_audio: number;
    choix: string[];
};

export type EpreuveTest = {
    id: number;
    nom: string;
    icone: string;
};

/** Questions regroupées par épreuve puis par catégorie ; « position » : rang dans le test. */
export type Section = {
    epreuve: EpreuveTest | undefined;
    categories: { nom: string; questions: { position: number; question: QuestionTest }[] }[];
};

export const LETTRES = ['A', 'B', 'C', 'D', 'E', 'F'];
