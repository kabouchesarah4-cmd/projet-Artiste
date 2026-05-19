
// LIGHTBOX.JS — Gère la fenêtre qui s'ouvre quand on clique
// sur une œuvre dans la galerie
// Ce fichier est chargé UNIQUEMENT sur galerie.html



// Je récupère tous les éléments dont j'ai besoin
const cartes           = document.querySelectorAll('.galerie-carte');
const lightbox         = document.getElementById('lightbox');
const lightboxBackdrop = document.getElementById('lightbox-backdrop');
const lightboxImage    = document.getElementById('lightbox-image');
const lightboxTitre    = document.getElementById('lightbox-titre');
const lightboxDetails  = document.getElementById('lightbox-details');
const btnFermer        = document.getElementById('lightbox-fermer');
const btnPrev          = document.getElementById('lightbox-prev');
const btnNext          = document.getElementById('lightbox-next');

// Index de l'œuvre actuellement affichée dans la lightbox
let indexActuel = 0;

// Je convertis la NodeList en tableau pour pouvoir naviguer dedans
let cartesVisibles = Array.from(cartes);



// OUVRIR LA LIGHTBOX
// Appelée quand on clique sur une carte


function ouvrirLightbox(index) {
    indexActuel = index;

    // Je récupère les infos de la carte cliquée
    const carte   = cartesVisibles[index];
    const image   = carte.querySelector('.galerie-image');
    const titre   = carte.getAttribute('data-titre');
    const details = carte.getAttribute('data-details');

    // Je copie le style de fond de la carte dans la lightbox
    // (les vraies images fonctionneront pareil.)
    lightboxImage.style.cssText = image.style.cssText;
    lightboxImage.style.width   = '100%';
    lightboxImage.style.height  = '100%';

    // Je mets le titre et les détails
    lightboxTitre.textContent   = titre   || '';
    lightboxDetails.textContent = details || '';

    // J'affiche la lightbox et le fond sombre
    lightbox.classList.add('ouverte');
    lightboxBackdrop.classList.add('ouverte');

    // J'empêche le scroll de la page en arrière-plan
    document.body.style.overflow = 'hidden';
}



// FERMER LA LIGHTBOX


function fermerLightbox() {
    lightbox.classList.remove('ouverte');
    lightboxBackdrop.classList.remove('ouverte');

    // Je réactive le scroll
    document.body.style.overflow = '';
}



// NAVIGUER (œuvre suivante / précédente)


function allerA(direction) {
    // direction = +1 (suivante) ou -1 (précédente)
    indexActuel = indexActuel + direction;

    // Si je vais trop loin, je reviens au début (boucle)
    if (indexActuel >= cartesVisibles.length) {
        indexActuel = 0;
    }
    if (indexActuel < 0) {
        indexActuel = cartesVisibles.length - 1;
    }

    ouvrirLightbox(indexActuel);
}



// ÉVÉNEMENTS : ce qui se passe quand on clique/presse


// Clic sur une carte → ouvre la lightbox
cartes.forEach(function(carte, index) {
    carte.addEventListener('click', function() {
        // Recalcule les cartes visibles (après filtrage)
        cartesVisibles = Array.from(document.querySelectorAll('.galerie-carte:not(.masque)'));
        const vraiIndex = cartesVisibles.indexOf(carte);
        if (vraiIndex !== -1) {
            ouvrirLightbox(vraiIndex);
        }
    });
});

// Clic sur le bouton "fermer"
btnFermer.addEventListener('click', fermerLightbox);

// Clic sur le fond sombre → ferme aussi
lightboxBackdrop.addEventListener('click', fermerLightbox);

// Flèche précédente
btnPrev.addEventListener('click', function() {
    allerA(-1);
});

// Flèche suivante
btnNext.addEventListener('click', function() {
    allerA(+1);
});

// Touches clavier : Échap = ferme, ← = précédent, → = suivant
document.addEventListener('keydown', function(event) {
    if (!lightbox.classList.contains('ouverte')) return; // Lightbox fermée → on ignore

    if (event.key === 'Escape')     fermerLightbox();
    if (event.key === 'ArrowLeft')  allerA(-1);
    if (event.key === 'ArrowRight') allerA(+1);
});



// FILTRES PAR CATÉGORIE
// Quand on clique sur "Peinture", seules les peintures restent


const filtresBtns = document.querySelectorAll('.filtre-btn');

filtresBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {

        // Retire "actif" de tous les boutons, le met sur celui cliqué
        filtresBtns.forEach(function(b) { b.classList.remove('actif'); });
        btn.classList.add('actif');

        const filtre = btn.getAttribute('data-filtre'); // "tout", "peinture", etc.

        cartes.forEach(function(carte) {
            const categorie = carte.getAttribute('data-categorie');

            if (filtre === 'tout' || categorie === filtre) {
                // Cette carte correspond au filtre → je l'affiche
                carte.classList.remove('masque');
            } else {
                // Cette carte ne correspond pas → je la cache
                carte.classList.add('masque');
            }
        });

    });
});