// js/audio-player.js - Lecteur audio universel

class UniversalAudioPlayer {
    constructor() {
        this.players = new Map();
        this.init();
    }

    init() {
        // Attendre que Howler soit chargé
        if (!window.Howl) {
            setTimeout(() => this.init(), 100);
            return;
        }
        
        // Initialiser tous les lecteurs
        document.querySelectorAll('.sermon-card').forEach(card => {
            const audioSrc = card.dataset.audio;
            if (audioSrc) {
                this.createPlayer(card, audioSrc);
            }
        });
    }

    createPlayer(card, audioSrc) {
        const sermonId = card.dataset.id;
        const placeholder = card.querySelector('.universal-audio-player-placeholder');
        if (!placeholder) return;

        // Créer le conteneur du lecteur
        const playerContainer = document.createElement('div');
        playerContainer.className = 'universal-audio-player';
        playerContainer.innerHTML = `
            <div class="player-controls">
                <button class="play-btn" data-id="${sermonId}">
                    <i class="fas fa-play"></i>
                </button>
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <div class="time-display">
                        <span class="current-time">0:00</span> / <span class="duration">0:00</span>
                    </div>
                </div>
                <button class="download-btn" data-src="${audioSrc}">
                    <i class="fas fa-download"></i>
                </button>
            </div>
        `;

        // Remplacer le placeholder
        placeholder.parentNode.replaceChild(playerContainer, placeholder);

        // Initialiser Howler
        const sound = new Howl({
            src: [audioSrc],
            html5: true,
            onload: () => {
                const duration = sound.duration();
                const minutes = Math.floor(duration / 60);
                const seconds = Math.floor(duration % 60);
                playerContainer.querySelector('.duration').textContent = 
                    `${minutes}:${seconds.toString().padStart(2, '0')}`;
            },
            onplay: () => {
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
                this.startProgressUpdate(sound, playerContainer);
                
                // Incrémenter le compteur de lectures
                fetch('api/increment_play.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id: sermonId})
                });
                
                // Mettre à jour l'affichage du compteur
                const playCount = card.querySelector(`.play-count-${sermonId}`);
                if (playCount) {
                    playCount.textContent = parseInt(playCount.textContent) + 1;
                }
            },
            onpause: () => {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            },
            onstop: () => {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
                this.resetProgress(playerContainer);
            },
            onend: () => {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
                this.resetProgress(playerContainer);
            }
        });

        this.players.set(sermonId, sound);

        // Gestionnaire du bouton play/pause
        const playBtn = playerContainer.querySelector('.play-btn');
        playBtn.addEventListener('click', () => {
            const sound = this.players.get(sermonId);
            
            // Arrêter tous les autres sons
            this.players.forEach((s, id) => {
                if (id !== sermonId && s.playing()) {
                    s.pause();
                    const otherCard = document.querySelector(`[data-id="${id}"]`);
                    if (otherCard) {
                        otherCard.querySelector('.play-btn i').className = 'fas fa-play';
                    }
                }
            });

            if (sound.playing()) {
                sound.pause();
            } else {
                sound.play();
            }
        });

        // Gestionnaire du téléchargement
        const downloadBtn = playerContainer.querySelector('.download-btn');
        downloadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = audioSrc;
            
            // Incrémenter le compteur de téléchargements
            fetch('api/increment_download.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id: sermonId})
            });
        });

        // Gestionnaire de la barre de progression
        const progressBar = playerContainer.querySelector('.progress-bar');
        const progressFill = playerContainer.querySelector('.progress-fill');
        
        progressBar.addEventListener('click', (e) => {
            const sound = this.players.get(sermonId);
            const rect = progressBar.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            const seekTime = sound.duration() * percent;
            sound.seek(seekTime);
        });
    }

    startProgressUpdate(sound, container) {
        const progressFill = container.querySelector('.progress-fill');
        const currentTimeEl = container.querySelector('.current-time');
        
        const update = () => {
            if (sound.playing()) {
                const seek = sound.seek();
                const duration = sound.duration();
                const percent = (seek / duration) * 100;
                progressFill.style.width = percent + '%';
                
                const minutes = Math.floor(seek / 60);
                const seconds = Math.floor(seek % 60);
                currentTimeEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                
                requestAnimationFrame(update);
            }
        };
        
        requestAnimationFrame(update);
    }

    resetProgress(container) {
        container.querySelector('.progress-fill').style.width = '0%';
        container.querySelector('.current-time').textContent = '0:00';
    }
}

// Initialiser quand la page est chargée
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        window.audioPlayer = new UniversalAudioPlayer();
    }, 500);
});