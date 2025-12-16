import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["totalGlobal"] // Cible le montant total en bas de page

    // Se connecte à l'URL pour ajouter/augmenter
    async augmenter(event) {
        event.preventDefault();
        const url = event.currentTarget.href;
        
        await this.miseAJour(url, event.currentTarget);
    }

    // Se connecte à l'URL pour diminuer
    async diminuer(event) {
        event.preventDefault();
        const url = event.currentTarget.href;
        
        await this.miseAJour(url, event.currentTarget);
    }

    // Se connecte à l'URL pour supprimer
    async supprimer(event) {
        event.preventDefault();
        const url = event.currentTarget.href;
        const ligne = event.currentTarget.closest('tr'); // La ligne du tableau

        if(!confirm("Voulez-vous vraiment retirer cet article ?")) return;

        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await response.json();

            if (data.supprime) {
                ligne.remove(); // On retire la ligne HTML
                this.verifierPanierVide();
            }
            
            this.mettreAJourTotal(data.nouveauTotalGlobal);

        } catch (error) {
            console.error('Erreur:', error);
            window.location.reload(); // En cas d'erreur, on recharge proprement
        }
    }

    async miseAJour(url, elementDeclencheur) {
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            
            // 1. SI LE SERVEUR DIT NON (Stock insuffisant -> Code 400)
            if (!response.ok) {
                const data = await response.json();
                // On affiche le message d'erreur envoyé par le contrôleur
                alert(data.erreur || "Une erreur est survenue"); 
                return; // On arrête tout, on ne met pas à jour l'affichage
            }

            // 2. SI C'EST BON (Code 200)
            const data = await response.json();
            
            const ligne = elementDeclencheur.closest('tr');

            if (data.supprime) {
                ligne.remove();
                this.verifierPanierVide();
            } else {
                ligne.querySelector('[data-panier-target="quantite"]').innerText = data.nouvelleQuantite;
                ligne.querySelector('[data-panier-target="totalLigne"]').innerText = data.nouveauTotalLigne + ' €';
            }

            this.mettreAJourTotal(data.nouveauTotalGlobal);

        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    mettreAJourTotal(montant) {
        if(this.hasTotalGlobalTarget) {
            this.totalGlobalTarget.innerText = montant + ' €';
        }
    }

    // Si le tableau est vide, on recharge la page pour afficher le message "Panier vide"
    verifierPanierVide() {
        const tbody = document.querySelector('tbody');
        if (tbody && tbody.children.length === 0) {
            window.location.reload();
        }
    }
}