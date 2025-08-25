<?php
/**
 * FALLBACK - Checkout B2B via shortcode si le template ne fonctionne pas
 */

add_shortcode('checkout_b2b', 'display_checkout_b2b_shortcode');

function display_checkout_b2b_shortcode() {
    if (!WC()->cart || WC()->cart->is_empty()) {
        return '<p>Votre panier est vide. <a href="' . wc_get_page_permalink('shop') . '">Retourner à la boutique</a></p>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* CSS critique inline pour s'assurer que ça fonctionne */
    .checkout-b2b-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .checkout-steps {
        display: flex;
        justify-content: center;
        margin-bottom: 40px;
        background: #fff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }
    
    .step {
        display: flex;
        align-items: center;
        margin: 0 20px;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .step.active .step-number {
        background: #5899E2;
        color: white;
    }
    
    .step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e3e8ee;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        margin-right: 12px;
    }
    
    .checkout-content {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 40px;
        align-items: start;
    }
    
    .checkout-section {
        display: none;
        background: #fff;
        border-radius: 12px;
        padding: 32px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }
    
    .checkout-section.active {
        display: block;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-control {
        padding: 12px 16px;
        border: 2px solid #e3e8ee;
        border-radius: 8px;
        width: 100%;
        font-size: 15px;
    }
    
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn-primary {
        background: #5899E2;
        color: white;
    }
    
    .section-footer {
        margin-top: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid #e3e8ee;
    }
    
    @media (max-width: 992px) {
        .checkout-content {
            grid-template-columns: 1fr;
            gap: 30px;
        }
    }
    </style>
    
    <div class="checkout-b2b-container">
        <div style="background: #28a745; color: white; padding: 15px; text-align: center; margin-bottom: 20px; border-radius: 8px;">
            ✅ CHECKOUT B2B FALLBACK ACTIF - Ce système fonctionne en mode test
        </div>
        
        <form method="post" class="checkout woocommerce-checkout checkout-b2b-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
            
            <!-- Étapes du checkout -->
            <div class="checkout-steps">
                <div class="step active" data-step="1">
                    <span class="step-number">1</span>
                    <span class="step-title">Informations entreprise</span>
                </div>
                <div class="step" data-step="2">
                    <span class="step-number">2</span>
                    <span class="step-title">Livraison</span>
                </div>
                <div class="step" data-step="3">
                    <span class="step-number">3</span>
                    <span class="step-title">Paiement</span>
                </div>
            </div>

            <div class="checkout-content">
                <!-- Colonne gauche: Formulaires -->
                <div class="checkout-forms">
                    
                    <!-- ÉTAPE 1: Informations entreprise -->
                    <div class="checkout-section active" id="step-1">
                        <h2>🏢 Informations entreprise</h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="billing_first_name">Prénom</label>
                                <input type="text" class="form-control" name="billing_first_name" id="billing_first_name" />
                            </div>

                            <div class="form-group">
                                <label for="billing_last_name">Nom *</label>
                                <input type="text" class="form-control" name="billing_last_name" id="billing_last_name" required />
                            </div>

                            <div class="form-group full-width">
                                <label for="billing_company">Nom de l'entreprise *</label>
                                <input type="text" class="form-control" name="billing_company" id="billing_company" required />
                            </div>

                            <div class="form-group full-width">
                                <label for="billing_address_1">Adresse *</label>
                                <input type="text" class="form-control" name="billing_address_1" id="billing_address_1" required />
                            </div>

                            <div class="form-group">
                                <label for="billing_postcode">Code postal *</label>
                                <input type="text" class="form-control" name="billing_postcode" id="billing_postcode" pattern="[0-9]{5}" maxlength="5" required />
                            </div>

                            <div class="form-group">
                                <label for="billing_city">Ville *</label>
                                <input type="text" class="form-control" name="billing_city" id="billing_city" required />
                            </div>

                            <div class="form-group">
                                <label for="billing_phone">Téléphone *</label>
                                <input type="tel" class="form-control" name="billing_phone" id="billing_phone" required />
                            </div>

                            <div class="form-group">
                                <label for="billing_siret">SIRET *</label>
                                <input type="text" class="form-control" name="billing_siret" id="billing_siret" pattern="[0-9]{14}" maxlength="14" placeholder="14 chiffres" required />
                            </div>

                            <div class="form-group full-width">
                                <label for="billing_email">E-mail *</label>
                                <input type="email" class="form-control" name="billing_email" id="billing_email" required />
                            </div>
                        </div>

                        <div class="section-footer">
                            <button type="button" class="btn btn-primary" onclick="showStep(2)">
                                Continuer →
                            </button>
                        </div>
                    </div>

                    <!-- ÉTAPE 2: Livraison -->
                    <div class="checkout-section" id="step-2">
                        <h2>🚛 Mode de livraison</h2>
                        
                        <div style="margin: 20px 0;">
                            <label style="display: block; padding: 15px; border: 2px solid #e3e8ee; border-radius: 8px; margin-bottom: 10px; cursor: pointer;">
                                <input type="radio" name="shipping_method" value="delivery" checked style="margin-right: 10px;">
                                <strong>Livraison à domicile</strong> - 19,00 € TTC<br>
                                <small>Livraison sous 1-3 jours ouvrés</small>
                            </label>
                            
                            <label style="display: block; padding: 15px; border: 2px solid #e3e8ee; border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="shipping_method" value="pickup" style="margin-right: 10px;">
                                <strong>Retrait à l'atelier</strong> - Gratuit<br>
                                <small>Lun-Ven 8h-17h30, Sam 8h-12h</small>
                            </label>
                        </div>

                        <div class="section-footer">
                            <button type="button" class="btn" onclick="showStep(1)" style="background: #6c757d; color: white;">
                                ← Retour
                            </button>
                            <button type="button" class="btn btn-primary" onclick="showStep(3)">
                                Continuer →
                            </button>
                        </div>
                    </div>

                    <!-- ÉTAPE 3: Paiement -->
                    <div class="checkout-section" id="step-3">
                        <h2>💳 Paiement & Validation</h2>
                        
                        <div style="margin: 20px 0; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px;">
                                <input type="checkbox" name="terms" required>
                                J'accepte les conditions générales de vente *
                            </label>
                        </div>

                        <div class="section-footer">
                            <button type="button" class="btn" onclick="showStep(2)" style="background: #6c757d; color: white;">
                                ← Retour
                            </button>
                            <button type="submit" name="woocommerce_checkout_place_order" class="btn" style="background: #28a745; color: white; font-size: 16px; padding: 16px 32px;">
                                🔒 Valider ma commande
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Colonne droite: Résumé -->
                <div class="checkout-summary">
                    <div style="background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);">
                        <h3>🛒 Résumé de commande</h3>
                        
                        <?php 
                        // Afficher le résumé WooCommerce
                        woocommerce_order_review(); 
                        ?>
                    </div>
                </div>
            </div>

            <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
        </form>
    </div>
    
    <script>
    function showStep(step) {
        // Masquer toutes les sections
        document.querySelectorAll('.checkout-section').forEach(section => {
            section.classList.remove('active');
        });
        
        // Afficher la section demandée
        document.getElementById('step-' + step).classList.add('active');
        
        // Mettre à jour les indicateurs
        document.querySelectorAll('.step').forEach(s => {
            s.classList.remove('active');
        });
        document.querySelector('.step[data-step="' + step + '"]').classList.add('active');
        
        // Scroll vers le haut
        document.querySelector('.checkout-b2b-container').scrollIntoView({behavior: 'smooth'});
    }
    
    // Validation basique du SIRET
    document.addEventListener('DOMContentLoaded', function() {
        const siretField = document.getElementById('billing_siret');
        if (siretField) {
            siretField.addEventListener('input', function() {
                const siret = this.value.replace(/\s/g, '');
                if (siret.length === 14 && /^\d+$/.test(siret)) {
                    this.style.borderColor = '#28a745';
                } else {
                    this.style.borderColor = '#dc3545';
                }
            });
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}
?>