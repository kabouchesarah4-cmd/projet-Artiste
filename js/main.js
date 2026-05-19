// MAIN.JS — Scripts qui s'appliquent sur toutes les pages
// (navigation, scroll, menu hamburger, formulaire contact)


// 1. EFFET SCROLL SUR LE HEADER
// Quand je scroll vers le bas, j'ajoute la classe "scrolle"
// au header → ça déclenche le fond semi-transparent en CSS

const header = document.getElementById('header');

// Cette fonction est appelée à chaque pixel de scroll
window.addEventListener('scroll', function() {

    if (window.scrollY > 50) {
        // Je suis à plus de 50px du haut → fond semi-transparent
        header.classList.add('scrolle');
    } else {
        // Je suis tout en haut → transparent
        header.classList.remove('scrolle');
    }

});


// 2. MENU HAMBURGER (mobile)
//
// Quand je clique sur les 3 traits,
// j'affiche/cache le menu en ajoutant la classe "ouverte"

const hamburger = document.getElementById('hamburger');
const navLiens  = document.getElementById('nav-liens');

// Si le hamburger existe sur cette page (il existe toujours, mais au cas où)
if (hamburger && navLiens) {

    hamburger.addEventListener('click', function() {
        // toggle = ajoute la classe si elle n'est pas là, la retire si elle est là
        hamburger.classList.toggle('actif');
        navLiens.classList.toggle('ouverte');
    });

    // Fermer le menu si on clique sur un lien
    navLiens.querySelectorAll('a').forEach(function(lien) {
        lien.addEventListener('click', function() {
            hamburger.classList.remove('actif');
            navLiens.classList.remove('ouverte');
        });
    });

}


// 3. ANIMATION D'APPARITION AU SCROLL
// Les éléments apparaissent progressivement quand on scroll
// jusqu'à eux (au lieu d'être déjà là dès le chargement)
// Je sélectionne tous les éléments que je veux animer
const elementsAnimes = document.querySelectorAll(
    '.apercu-carte, .expo-item, .section-header, .presentation-texte, .apropos-texte'
);

// CSS : je cache ces éléments au départ (à ajouter dans style.css si tu veux)
// Ici je le fais directement en JS pour simplifier
elementsAnimes.forEach(function(el) {
    el.style.opacity    = '0';
    el.style.transform  = 'translateY(30px)';
    el.style.transition = 'opacity 0.7s ease, transform 0.7s ease';
});

// IntersectionObserver = surveille quand un élément entre dans l'écran
const observateur = new IntersectionObserver(function(entries) {

    entries.forEach(function(entry) {
        if (entry.isIntersecting) {
            // L'élément est visible → je l'anime
            entry.target.style.opacity   = '1';
            entry.target.style.transform = 'translateY(0)';
            // Je l'observe plus (l'animation ne se rejoue pas)
            observateur.unobserve(entry.target);
        }
    });

}, {
    threshold: 0.1 // Déclenche quand 10% de l'élément est visible
});

// Je commence à observer chaque élément
elementsAnimes.forEach(function(el) {
    observateur.observe(el);
});


// 4. FORMULAIRE DE CONTACT
// En Phase 1, le formulaire ne peut pas envoyer de vrai email
// (pas de backend). Je simule juste une confirmation.
// En Phase 2 avec PHP, on remplacera ça par un vrai envoi.

const formulaire     = document.getElementById('formulaire-contact');
const confirmation   = document.getElementById('formulaire-confirmation');

if (formulaire && confirmation) {

    formulaire.addEventListener('submit', function(event) {
        // Empêche le rechargement de la page (comportement par défaut)
        event.preventDefault();

        // Je cache le bouton envoyer
        const bouton = formulaire.querySelector('button[type="submit"]');
        bouton.textContent = 'Envoi en cours...';
        bouton.disabled = true;

        // Je simule un délai d'envoi (1.5 secondes)
        setTimeout(function() {
            // J'affiche le message de confirmation
            confirmation.classList.add('visible');
            // Je vide le formulaire
            formulaire.reset();
            // Je remet le bouton
            bouton.textContent = 'Envoyer le message →';
            bouton.disabled = false;
        }, 1500);

    });

}


// 5. CURSEUR PERSONNALISÉ (optionnel, effet luxe)
// Un petit point doré suit le curseur sur le site
// Crée l'élément curseur
const curseur = document.createElement('div');
curseur.style.cssText = `
  position: fixed;
  width: 6px;
  height: 6px;
  background: #c9a84c;
  border-radius: 50%;
  pointer-events: none;
  z-index: 9999;
  transform: translate(-50%, -50%);
  transition: transform 0.1s ease, width 0.3s ease, height 0.3s ease;
`;
document.body.appendChild(curseur);

// Le curseur suit la souris
document.addEventListener('mousemove', function(e) {
    curseur.style.left = e.clientX + 'px';
    curseur.style.top  = e.clientY + 'px';
});

// Le curseur s'agrandit quand on survole un lien ou bouton
document.querySelectorAll('a, button').forEach(function(el) {
    el.addEventListener('mouseenter', function() {
        curseur.style.width     = '20px';
        curseur.style.height    = '20px';
        curseur.style.opacity   = '0.5';
    });
    el.addEventListener('mouseleave', function() {
        curseur.style.width     = '6px';
        curseur.style.height    = '6px';
        curseur.style.opacity   = '1';
    });
});