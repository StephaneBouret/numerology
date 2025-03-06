export default class Loadmore {
    constructor() {
        this.loadmore = null;
        this.elementList = [];
        this.currentItems = 2; // On affiche 2 commentaires par défaut

        this.init();
    }

    init() {
        this.loadmore = document.getElementById('loadMore');
        this.elementList = [...document.querySelectorAll('.comments')];

        // Trier les commentaires du plus récent au plus ancien
        this.elementList.sort((a, b) =>
            new Date(b.dataset.createdAt) - new Date(a.dataset.createdAt)
        );

        if (this.loadmore) {
            this.lengthList = this.elementList.length;
            this.setupLoadMoreButton();
        }
    }

    setupLoadMoreButton() {
        this.toggleLoadMoreVisibility();

        // Ajoute l'événement de chargement de commentaires
        this.loadmore.addEventListener('click', (event) => this.loadMoreComments(event));
    }

    toggleLoadMoreVisibility() {
        // Masque tous les commentaires sauf les 2 premiers
        this.elementList.forEach((comment, index) => {
            comment.style.display = index < 2 ? 'block' : 'none';
        });

        // Afficher ou cacher le bouton "Load More"
        this.loadmore.style.display = this.lengthList > 2 ? 'block' : 'none';
    }

    loadMoreComments(event) {
        const button = event.target;
        button.classList.add('show-loader');

        setTimeout(() => {
            button.classList.remove('show-loader');

            // Afficher les 3 commentaires suivants
            let count = 0;
            for (let i = this.currentItems; i < this.elementList.length; i++) {
                if (this.elementList[i].style.display === 'none') {
                    this.elementList[i].style.display = 'block';
                    // this.elementList[i].parentNode.insertBefore(this.elementList[i], this.elementList[0]); // INSÉRER EN HAUT
                    count++;
                }
                if (count === 3) break; // Afficher un max de 3 commentaires par clic
            }

            this.currentItems += 3;

            // Vérifie si tous les commentaires sont affichés et supprime le bouton
            const hiddenComments = this.elementList.filter(comment => comment.style.display === 'none');
            if (hiddenComments.length === 0) {
                this.loadmore.remove();
            }
        }, 500);
    }
}