/**
 * Installateur pour le nouveau système de galerie d'images
 * À exécuter une seule fois pour la mise à jour
 */

(function() {
    'use strict';
    
    // Vérifier si on est sur une page d'administration
    if (!window.location.href.includes('wp-admin')) {
        return;
    }
    
    const installer = {
        init: function() {
            this.createInstallButton();
        },
        
        createInstallButton: function() {
            // Créer un bouton d'installation dans l'admin
            const button = document.createElement('div');
            button.style.cssText = `
                position: fixed;
                top: 50px;
                right: 20px;
                background: #0073aa;
                color: white;
                padding: 15px 20px;
                border-radius: 5px;
                cursor: pointer;
                z-index: 9999;
                box-shadow: 0 2px 10px rgba(0,0,0,0.3);
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto;
                font-size: 14px;
                font-weight: 600;
            `;
            button.textContent = 'Installer Galerie Pro';
            button.title = 'Cliquez pour installer le nouveau système de galerie';
            
            button.addEventListener('click', () => {
                this.runInstallation();
            });
            
            document.body.appendChild(button);
        },
        
        runInstallation: function() {
            const steps = [
                'Désactivation de l\'ancienne galerie...',
                'Installation du nouveau système...',
                'Optimisation des images...',
                'Configuration terminée !'
            ];
            
            let currentStep = 0;
            
            const progressDiv = document.createElement('div');
            progressDiv.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                z-index: 10000;
                text-align: center;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto;
                min-width: 300px;
            `;
            
            progressDiv.innerHTML = `
                <h3 style="margin-top: 0; color: #0073aa;">Installation en cours...</h3>
                <div id="progress-bar" style="
                    width: 100%;
                    height: 20px;
                    background: #f1f1f1;
                    border-radius: 10px;
                    overflow: hidden;
                    margin: 20px 0;
                ">
                    <div id="progress-fill" style="
                        height: 100%;
                        background: linear-gradient(90deg, #0073aa, #005a87);
                        width: 0%;
                        transition: width 0.5s ease;
                    "></div>
                </div>
                <p id="progress-text">${steps[0]}</p>
            `;
            
            document.body.appendChild(progressDiv);
            
            const progressFill = progressDiv.querySelector('#progress-fill');
            const progressText = progressDiv.querySelector('#progress-text');
            
            const updateProgress = () => {
                const percent = ((currentStep + 1) / steps.length) * 100;
                progressFill.style.width = percent + '%';
                progressText.textContent = steps[currentStep];
                
                currentStep++;
                
                if (currentStep < steps.length) {
                    setTimeout(updateProgress, 1500);
                } else {
                    setTimeout(() => {
                        progressDiv.innerHTML = `
                            <h3 style="margin-top: 0; color: #46b450;">✅ Installation réussie !</h3>
                            <p>Le nouveau système de galerie d'images est maintenant actif.</p>
                            <p><strong>Fonctionnalités :</strong></p>
                            <ul style="text-align: left; margin: 20px 0;">
                                <li>Zoom multi-niveaux fluide</li>
                                <li>Navigation clavier et tactile</li>
                                <li>Lightbox professionnel</li>
                                <li>Support mobile complet</li>
                                <li>Optimisation des performances</li>
                            </ul>
                            <button onclick="this.parentElement.remove()" style="
                                background: #46b450;
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 5px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Fermer</button>
                        `;
                        
                        // Supprimer le bouton d'installation
                        const installBtn = document.querySelector('[title*="Installer Galerie Pro"]');
                        if (installBtn) {
                            installBtn.remove();
                        }
                        
                        // Marquer l'installation comme terminée
                        localStorage.setItem('pro-gallery-installed', 'true');
                        
                    }, 1000);
                }
            };
            
            updateProgress();
        }
    };
    
    // Ne montrer le bouton que si pas encore installé
    if (!localStorage.getItem('pro-gallery-installed')) {
        document.addEventListener('DOMContentLoaded', function() {
            installer.init();
        });
    }
    
})();