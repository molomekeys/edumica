document.addEventListener('alpine:init', () => {
    const format = (secondes) => {
        const s = Math.max(0, Math.round(secondes));
        return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
    };

    /**
     * Menu déroulant de l'en-tête : s'ouvre au clic ou au survol (avec un court délai à la sortie
     * pour laisser le temps de rejoindre le panneau), se ferme au clic dehors, à Échap ou quand le focus sort.
     */
    window.Alpine.data('deroulant', () => ({
        ouvert: false,
        survol: false,
        delai: null,
        entrer() {
            clearTimeout(this.delai);
            this.survol = this.ouvert = true;
        },
        sortir() {
            clearTimeout(this.delai);
            this.survol = false;
            this.delai = setTimeout(() => (this.ouvert = false), 150);
        },
        // Sous la souris, le survol a déjà ouvert le panneau : le clic ne doit pas le refermer.
        basculer() {
            clearTimeout(this.delai);
            this.ouvert = this.survol || !this.ouvert;
        },
        fermer(focus = false) {
            this.ouvert = false;
            focus && this.$refs.bouton.focus();
        },
        quitter(event) {
            this.$el.contains(event.relatedTarget) || (this.ouvert = false);
        },
    }));

    /**
     * Compte à rebours du quiz, calé sur une échéance fixée par le serveur.
     */
    window.Alpine.data('chrono', (fin, surFin) => ({
        reste: 0,
        minuteur: null,

        init() {
            this.maj();
            this.minuteur = setInterval(() => this.maj(), 1000);
        },

        maj() {
            this.reste = Math.max(0, fin - Math.floor(Date.now() / 1000));
            if (this.reste === 0) {
                clearInterval(this.minuteur);
                surFin();
            }
        },

        get affichage() {
            const m = String(Math.floor(this.reste / 60)).padStart(2, '0');
            return `${m}:${String(this.reste % 60).padStart(2, '0')}`;
        },

        destroy() {
            clearInterval(this.minuteur);
        },
    }));

    /**
     * Lecteur « une seule écoute ». Lit le fichier audio s'il existe,
     * sinon fait lire la transcription par la synthèse vocale du navigateur.
     * « ecoutee » : l'écoute a déjà eu lieu, le lecteur démarre verrouillé.
     * « surDebut » : appelé au lancement, pour que le verrou survive à la navigation.
     */
    window.Alpine.data('lecteur', ({ source, transcription, duree, ecoutee = false }, surDebut = null) => ({
        duree,
        position: ecoutee ? duree : 0,
        enLecture: false,
        fini: ecoutee,
        audio: null,
        minuteur: null,

        get progression() {
            return this.duree ? this.position / this.duree : 0;
        },

        get libelle() {
            return `${format(this.position)} / ${format(this.duree)}`;
        },

        jouer() {
            if (this.enLecture || this.fini) return;
            this.enLecture = true;
            surDebut?.();

            if (source) {
                this.audio = new Audio(source);
                this.audio.addEventListener('loadedmetadata', () => (this.duree = Math.round(this.audio.duration)));
                this.audio.addEventListener('timeupdate', () => (this.position = this.audio.currentTime));
                this.audio.addEventListener('ended', () => this.terminer());
                this.audio.play();
                return;
            }

            const debut = performance.now();
            this.minuteur = setInterval(() => {
                // La durée est estimée : on reste juste avant la fin tant que la voix parle.
                this.position = Math.min(this.duree - 0.5, (performance.now() - debut) / 1000);
            }, 100);

            if ('speechSynthesis' in window && transcription) {
                const enonce = new SpeechSynthesisUtterance(transcription.replace(/—/g, ''));
                enonce.lang = 'fr-FR';
                enonce.rate = 0.95;
                enonce.addEventListener('end', () => this.terminer());
                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(enonce);
            } else {
                setTimeout(() => this.terminer(), this.duree * 1000);
            }
        },

        terminer() {
            clearInterval(this.minuteur);
            this.position = this.duree;
            this.enLecture = false;
            this.fini = true;
        },

        destroy() {
            clearInterval(this.minuteur);
            this.audio?.pause();
            if ('speechSynthesis' in window) window.speechSynthesis.cancel();
        },
    }));
});
