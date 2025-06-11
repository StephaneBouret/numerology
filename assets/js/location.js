"use strict";

// Importation du composant étendu de la librairie Google Maps
import {
    APILoader
} from 'https://unpkg.com/@googlemaps/extended-component-library@0.6';

// Champs concernés
const SHORT_NAME_ADDRESS_COMPONENT_TYPES = new Set(['street_number', 'postal_code']);
const ADDRESS_COMPONENT_TYPES_IN_FORM = ['adress', 'city', 'postalCode'];

function getFormInputElement(componentType) {
    return document.getElementById(`registration_form_${componentType}`);
}

function fillInAddress(place) {
    function getComponentName(type) {
        for (const component of place.address_components || []) {
            if (component.types.includes(type)) {
                return SHORT_NAME_ADDRESS_COMPONENT_TYPES.has(type) ?
                    component.short_name :
                    component.long_name;
            }
        }
        return '';
    }

    function getComponentText(type) {
        if (type === 'adress') {
            return `${getComponentName('street_number')} ${getComponentName('route')}`;
        } else if (type === 'city') {
            return getComponentName('locality') || getComponentName('administrative_area_level_1');
        } else if (type === 'postalCode') {
            return getComponentName('postal_code'); // Capture directe du code postal
        }
        return '';
    }

    for (const type of ADDRESS_COMPONENT_TYPES_IN_FORM) {
        const input = getFormInputElement(type);
        if (input) {
            input.value = getComponentText(type);

            // Déclenche les événements 'input' et 'change'
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }
}

// Initialisation différée de l'autocomplétion
let autocomplete;
let initialized = false;

async function initAutocomplete() {
    if (initialized) return;
    initialized = true;

    const {
        Autocomplete
    } = await APILoader.importLibrary('places');

    const input = getFormInputElement('adress');
    input.setAttribute('autocomplete', 'off'); // désactive l'autofill natif

    autocomplete = new Autocomplete(input, {
        fields: ['address_components', 'geometry', 'name'],
        types: ['address'],
        // componentRestrictions: { country: 'fr' }
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            window.alert(`Aucun détail pour : '${place.name}'`);
            return;
        }

        fillInAddress(place);

        // Active les champs ville et code postal
        const cityInput = getFormInputElement('city');
        const postalInput = getFormInputElement('postalCode');

        cityInput.disabled = false;
        postalInput.disabled = false;

        document.getElementById('additional-fields').style.display = 'block';
        input.blur(); // Pour forcer la fermeture

        // Supprime/masque les suggestions après un court délai
        setTimeout(() => {
            document.querySelectorAll('.pac-container').forEach(c => c.remove());
        }, 200);
    });
}

function setupAutocompleteTrigger() {
    const addressInput = getFormInputElement('adress');
    const cityInput = getFormInputElement('city');
    const postalInput = getFormInputElement('postalCode');

    // Désactive les champs jusqu'à la sélection
    cityInput.disabled = true;
    postalInput.disabled = true;

    addressInput.addEventListener('input', () => {
        if (addressInput.value.length >= 3 && !initialized) {
            initAutocomplete();
        }
    });

    addressInput.addEventListener('blur', () => {
        if (!autocomplete?.getPlace?.()) {
            // Réinitialisation si aucune sélection faite
            cityInput.value = '';
            postalInput.value = '';
            cityInput.disabled = true;
            postalInput.disabled = true;
            document.getElementById('additional-fields').style.display = 'none';
        }
    });
}

setupAutocompleteTrigger();