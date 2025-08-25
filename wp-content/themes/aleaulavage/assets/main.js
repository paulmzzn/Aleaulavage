// Webpack Imports

( function () {
	'use strict';

	// Focus input if Searchform is empty
	[].forEach.call( document.querySelectorAll( '.search-form' ), ( el ) => {
		el.addEventListener( 'submit', function ( e ) {
			var search = el.querySelector( 'input' );
			if ( search.value.length < 1 ) {
				e.preventDefault();
				search.focus();
			}
		} );
	} );

	// Initialize Popovers: https://getbootstrap.com/docs/5.0/components/popovers
	var popoverTriggerList = [].slice.call( document.querySelectorAll( '[data-bs-toggle="popover"]' ) );
	var popoverList = popoverTriggerList.map( function ( popoverTriggerEl ) {
		return new bootstrap.Popover( popoverTriggerEl, {
			trigger: 'focus',
		} );
	} );
} )();


// Code pour le slider
document.addEventListener("DOMContentLoaded", function() {
    const nextButton = document.querySelector(".next-button");
    const prevButton = document.querySelector(".prev-button");
    const slider = document.querySelector(".subcategory-bubbles");
    let index = 0;

    // Fonction pour faire défiler les éléments du slider
    function moveSlider() {
        const slides = document.querySelectorAll(".subcategory-bubble");
        const slideWidth = slides[0].offsetWidth;
        slider.style.transform = `translateX(-${index * slideWidth}px)`;
    }

    // Gérer le bouton suivant
    nextButton.addEventListener("click", function() {
        index++;
        if (index >= document.querySelectorAll(".subcategory-bubble").length) {
            index = 0; // Revenir au début
        }
        moveSlider();
    });

    // Gérer le bouton précédent
    prevButton.addEventListener("click", function() {
        index--;
        if (index < 0) {
            index = document.querySelectorAll(".subcategory-bubble").length - 1; // Aller à la fin
        }
        moveSlider();
    });
});


document.addEventListener("DOMContentLoaded", function() {
    var button = document.createElement("span");
    button.classList.add("show-more");
    button.textContent = "Voir plus";

    var desc = document.querySelector(".term-description");
    if (desc) {
        desc.after(button);
        button.addEventListener("click", function() {
            desc.classList.toggle("expanded");
            button.textContent = desc.classList.contains("expanded") ? "Voir moins" : "Voir plus";
        });
    }
});

document.addEventListener("DOMContentLoaded", function() {
  var sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.addEventListener('mouseenter', function() {
      sidebar.classList.add('sidebar-expanded');
    });
    sidebar.addEventListener('mouseleave', function() {
      sidebar.classList.remove('sidebar-expanded');
    });
  }
});

// Fonction pour faire défiler vers la description complète - déclaration globale
window.scrollToFullDescription = function() {
    console.log('scrollToFullDescription appelée');
    const fullDescription = document.getElementById('full-description');
    console.log('Element trouvé:', fullDescription);
    if (fullDescription) {
        fullDescription.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    } else {
        console.log('Element full-description non trouvé');
        // Fallback: chercher par classe
        const fallback = document.querySelector('.category-description-full');
        if (fallback) {
            console.log('Fallback trouvé, scroll vers fallback');
            fallback.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
};
