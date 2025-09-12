/**
 * Script d'urgence pour désactiver SPA
 * À utiliser si le SPA cause des problèmes critiques
 */

// Ajouter ?no-spa=1 à l'URL actuelle pour désactiver le SPA
function disableSPAEmergency() {
    const url = new URL(window.location);
    url.searchParams.set('no-spa', '1');
    window.location.href = url.toString();
}

// Créer un bouton d'urgence visible
function createEmergencyButton() {
    const button = document.createElement('button');
    button.innerHTML = '🚨 DÉSACTIVER SPA';
    button.style.cssText = `
        position: fixed !important;
        top: 10px !important;
        left: 10px !important;
        z-index: 999999 !important;
        background: #ff4444 !important;
        color: white !important;
        border: none !important;
        padding: 10px 15px !important;
        border-radius: 5px !important;
        font-weight: bold !important;
        cursor: pointer !important;
        font-size: 12px !important;
    `;
    
    button.onclick = disableSPAEmergency;
    document.body.appendChild(button);
    
    // Auto-remove après 10 secondes
    setTimeout(() => {
        if (button.parentNode) {
            button.parentNode.removeChild(button);
        }
    }, 10000);
}

// Détecter si il y a un problème critique avec SPA
function detectSPAProblems() {
    let problemsDetected = 0;
    
    // Vérifier si les clics redirigent vers l'accueil de façon anormale
    let clickCount = 0;
    let homeRedirects = 0;
    
    document.addEventListener('click', function(e) {
        clickCount++;
        
        setTimeout(() => {
            if (window.location.pathname === '/' || window.location.pathname === '/index.php') {
                homeRedirects++;
            }
            
            // Si plus de 50% des clics mènent à l'accueil, il y a un problème
            if (clickCount > 3 && (homeRedirects / clickCount) > 0.5) {
                console.error('🚨 SPA Problem detected: Too many redirects to home');
                createEmergencyButton();
            }
        }, 1000);
    });
    
    // Détecter les erreurs JavaScript liées au SPA
    window.addEventListener('error', function(e) {
        if (e.message && e.message.includes('spaNavigation')) {
            problemsDetected++;
            if (problemsDetected > 2) {
                console.error('🚨 SPA Problem detected: Multiple JavaScript errors');
                createEmergencyButton();
            }
        }
    });
}

// Activer la détection seulement si SPA est actif
if (window.location.search.includes('debug=1') || localStorage.getItem('spa-debug')) {
    detectSPAProblems();
    console.log('🛡️ SPA Emergency Detection Active');
}