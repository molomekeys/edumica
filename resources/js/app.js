document.addEventListener('alpine:init', () => {
    /**
     * Fait apparaître l'élément quand il entre dans l'écran (voir .est-visible dans app.css).
     * Le décalage se règle avec la variable CSS --delai.
     */
    window.Alpine.directive('apparition', (el, _, { cleanup }) => {
        if (!('IntersectionObserver' in window)) {
            el.classList.add('est-visible');
            return;
        }

        const observateur = new IntersectionObserver(
            (entrees) => {
                if (entrees.some((entree) => entree.isIntersecting)) {
                    el.classList.add('est-visible');
                    observateur.disconnect();
                }
            },
            { rootMargin: '0px 0px -8% 0px' },
        );

        observateur.observe(el);
        cleanup(() => observateur.disconnect());
    });

    const format = (secondes) => {
        const s = Math.max(0, Math.round(secondes));
        return `${Math.floor(s / 60)}:${String(s % 60).padStart(2, '0')}`;
    };

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
