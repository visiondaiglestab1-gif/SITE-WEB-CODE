/**
 * script.js - Fonctions JavaScript pour VISION D'AIGLES Tabernacle
 * Inclut le support AMR, les statistiques et les animations
 */

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    initMobileMenu();
    initSmoothScroll();
    initHeaderAnimation();
    initPlyrPlayers();
    initAmrSupport();
    initScrollAnimations();
    initContactForm();
});

/**
 * Menu mobile
 */
function initMobileMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const navLinks = document.getElementById('nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            navLinks.classList.toggle('active');
        });

        // Fermer le menu quand on clique sur un lien
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
            });
        });

        // Fermer le menu en cliquant ailleurs
        document.addEventListener('click', function(event) {
            if (!navLinks.contains(event.target) && !menuToggle.contains(event.target)) {
                navLinks.classList.remove('active');
            }
        });
    }
}

/**
 * Défilement fluide pour les ancres
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const target = document.querySelector(targetId);
            if (target) {
                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Animation du header au scroll
 */
function initHeaderAnimation() {
    const header = document.querySelector('header');
    if (!header) return;

    window.addEventListener('scroll', () => {
        if (window.scrollY > 100) {
            header.style.background = 'linear-gradient(135deg, #0a1a2f 0%, #1e3c72 100%)';
            header.style.padding = '0.5rem 0';
        } else {
            header.style.background = 'linear-gradient(135deg, #1e3c72 0%, #2a5298 100%)';
            header.style.padding = '1rem 0';
        }
    });
}

/**
 * Initialisation des lecteurs Plyr pour l'audio
 */
function initPlyrPlayers() {
    if (typeof Plyr === 'undefined') {
        console.warn('Plyr non chargé');
        return;
    }

    const players = Plyr.setup('.plyr', {
        controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'download'],
        settings: ['speed'],
        speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
        i18n: {
            restart: 'Recommencer',
            rewind: 'Reculer {seektime}s',
            play: 'Lecture',
            pause: 'Pause',
            fastForward: 'Avancer {seektime}s',
            seek: 'Chercher',
            played: 'Lecture en cours',
            buffered: 'Tampon',
            currentTime: 'Temps actuel',
            duration: 'Durée',
            volume: 'Volume',
            mute: 'Muet',
            unmute: 'Son',
            download: 'Télécharger',
            enterFullscreen: 'Plein écran',
            exitFullscreen: 'Quitter le plein écran',
            speed: 'Vitesse',
            normal: 'Normale'
        },
        listeners: {
            play: function(event) {
                const sermonId = this.elements.container.dataset.sermonId;
                if (sermonId) {
                    incrementPlay(sermonId);
                }
            }
        }
    });

    // Gestionnaire pour les téléchargements Plyr
    players.forEach(player => {
        player.on('download', function() {
            const sermonId = this.elements.container.dataset.sermonId;
            if (sermonId) {
                incrementDownload(sermonId);
            }
        });
    });
}

/**
 * Support spécifique pour les fichiers AMR
 */
function initAmrSupport() {
    // Détecter les sources AMR et ajouter un lecteur alternatif si nécessaire
    document.querySelectorAll('audio source[type="audio/amr"], source[src$=".amr"]').forEach(source => {
        const audio = source.closest('audio');
        if (audio && !audio.classList.contains('amr-processed')) {
            audio.classList.add('amr-processed');
            
            // Vérifier si le navigateur supporte AMR
            const canPlayAMR = audio.canPlayType('audio/amr') !== '';
            
            if (!canPlayAMR) {
                // Créer un lecteur alternatif
                createAmrFallbackPlayer(audio);
            }
        }
    });
}

/**
 * Crée un lecteur de fallback pour AMR
 */
function createAmrFallbackPlayer(audioElement) {
    const sermonCard = audioElement.closest('.sermon-card');
    if (!sermonCard) return;

    const sermonId = sermonCard.dataset.id;
    const audioContainer = audioElement.parentElement;
    const amrSource = audioElement.querySelector('source[type="audio/amr"], source[src$=".amr"]');
    
    if (!amrSource) return;

    const amrUrl = amrSource.src;
    
    // Cacher le lecteur standard
    audioElement.style.display = 'none';
    
    // Créer le conteneur du lecteur AMR
    const amrContainer = document.createElement('div');
    amrContainer.className = 'amr-player-container';
    amrContainer.innerHTML = `
        <div class="amr-controls">
            <button class="amr-play-btn" data-id="${sermonId}">
                <i class="fas fa-play"></i>
            </button>
            <div class="amr-progress-container">
                <input type="range" class="amr-progress" value="0" min="0" max="100" step="0.1">
                <span class="amr-time">00:00</span>
            </div>
        </div>
        <div class="format-indicator">
            <span class="format-badge format-amr">AMR</span>
        </div>
    `;
    
    audioContainer.appendChild(amrContainer);
    
    // Initialiser le lecteur AMR
    initAmrPlayer(amrContainer, amrUrl, sermonId);
}

/**
 * Initialise un lecteur AMR individuel
 */
function initAmrPlayer(container, audioUrl, sermonId) {
    const playBtn = container.querySelector('.amr-play-btn');
    const progressBar = container.querySelector('.amr-progress');
    const timeDisplay = container.querySelector('.amr-time');
    
    let audio = null;
    let isPlaying = false;
    let progressInterval = null;
    
    playBtn.addEventListener('click', function() {
        if (!audio) {
            // Créer un nouvel élément audio
            audio = new Audio(audioUrl);
            
            audio.addEventListener('loadedmetadata', function() {
                progressBar.max = 100;
                updateTimeDisplay();
            });
            
            audio.addEventListener('timeupdate', function() {
                if (audio.duration) {
                    const percent = (audio.currentTime / audio.duration) * 100;
                    progressBar.value = percent;
                    updateTimeDisplay();
                }
            });
            
            audio.addEventListener('play', function() {
                isPlaying = true;
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
                incrementPlay(sermonId);
                
                // Mettre à jour la progression en continu
                if (progressInterval) clearInterval(progressInterval);
                progressInterval = setInterval(() => {
                    if (audio && !audio.paused) {
                        const percent = (audio.currentTime / audio.duration) * 100;
                        progressBar.value = percent;
                        updateTimeDisplay();
                    }
                }, 100);
            });
            
            audio.addEventListener('pause', function() {
                isPlaying = false;
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
                if (progressInterval) {
                    clearInterval(progressInterval);
                    progressInterval = null;
                }
            });
            
            audio.addEventListener('ended', function() {
                isPlaying = false;
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
                progressBar.value = 0;
                updateTimeDisplay();
                if (progressInterval) {
                    clearInterval(progressInterval);
                    progressInterval = null;
                }
            });
            
            audio.addEventListener('error', function(e) {
                console.error('Erreur lecture AMR:', e);
                alert('Erreur lors de la lecture du fichier AMR');
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            });
            
            audio.play().catch(e => {
                console.error('Erreur lecture:', e);
                alert('Impossible de lire ce fichier audio');
            });
            
        } else {
            if (audio.paused) {
                audio.play();
            } else {
                audio.pause();
            }
        }
    });
    
    // Gestion du seek
    progressBar.addEventListener('input', function() {
        if (audio && audio.duration) {
            const seekTime = (this.value / 100) * audio.duration;
            audio.currentTime = seekTime;
        }
    });
    
    // Mettre à jour l'affichage du temps
    function updateTimeDisplay() {
        if (audio && audio.duration) {
            const current = audio.currentTime || 0;
            const minutes = Math.floor(current / 60);
            const seconds = Math.floor(current % 60);
            timeDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
    }
}

/**
 * Animation des cartes au scroll
 */
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Appliquer l'animation aux cartes
    document.querySelectorAll('.sermon-card, .event-card, .pastor-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });
}

/**
 * Validation du formulaire de contact
 */
function initContactForm() {
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            const name = this.querySelector('input[name="name"]').value.trim();
            const email = this.querySelector('input[name="email"]').value.trim();
            const message = this.querySelector('textarea[name="message"]').value.trim();
            
            if (!name || !email || !message) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                return;
            }
            
            if (!isValidEmail(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide');
                return;
            }
        });
    }
}

/**
 * Valide une adresse email
 */
function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Incrémente le compteur de lectures
 */
function incrementPlay(sermonId) {
    if (!sermonId) return;
    
    fetch('api/increment_play.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: sermonId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour l'affichage du compteur
            const playElement = document.querySelector(`.play-count-${sermonId}`);
            if (playElement) {
                const currentCount = parseInt(playElement.textContent) || 0;
                playElement.textContent = currentCount + 1;
            }
        }
    })
    .catch(err => console.error('Erreur compteur lectures:', err));
}

/**
 * Incrémente le compteur de téléchargements
 */
function incrementDownload(sermonId) {
    if (!sermonId) return;
    
    fetch('api/increment_download.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id: sermonId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour l'affichage du compteur
            const downloadElement = document.querySelector(`.download-count-${sermonId}`);
            if (downloadElement) {
                const currentCount = parseInt(downloadElement.textContent) || 0;
                downloadElement.textContent = currentCount + 1;
            }
        }
    })
    .catch(err => console.error('Erreur compteur téléchargements:', err));
    
    return true; // Permet au téléchargement de continuer
}

/**
 * Gestionnaire pour les téléchargements manuels
 */
document.addEventListener('click', function(e) {
    const downloadLink = e.target.closest('.download-link');
    if (downloadLink) {
        const sermonCard = downloadLink.closest('.sermon-card');
        if (sermonCard) {
            const sermonId = sermonCard.dataset.id;
            if (sermonId) {
                incrementDownload(sermonId);
            }
        }
    }
});

// Exporter les fonctions pour utilisation globale
window.incrementPlay = incrementPlay;
window.incrementDownload = incrementDownload;