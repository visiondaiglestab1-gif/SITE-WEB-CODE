// VAPID keys (à générer sur https://web-push-codelab.glitch.me/)
const applicationServerKey = 'BG...'; // Votre clé publique

// Demander la permission
async function requestNotificationPermission() {
    const permission = await Notification.requestPermission();
    if (permission === 'granted') {
        registerServiceWorker();
    }
}

// Enregistrer le service worker
async function registerServiceWorker() {
    if ('serviceWorker' in navigator) {
        const registration = await navigator.serviceWorker.register('/sw.js');
        
        // S'abonner aux notifications
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: new Uint8Array(applicationServerKey)
        });
        
        // Envoyer l'abonnement au serveur
        fetch('/api/save_subscription.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(subscription)
        });
    }
}

// Bouton d'abonnement
document.getElementById('subscribeNotifications').addEventListener('click', requestNotificationPermission);