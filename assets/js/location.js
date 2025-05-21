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
    function getComponentName(componentType) {
        for (const component of place.address_components || []) {
            if (component.types.includes(componentType)) {
                return SHORT_NAME_ADDRESS_COMPONENT_TYPES.has(componentType) ?
                    component.short_name :
                    component.long_name;
            }
        }
        return '';
    }

    function getComponentText(componentType) {
        if (componentType === 'adress') {
            return `${getComponentName('street_number')} ${getComponentName('route')}`;
        } else if (componentType === 'city') {
            return getComponentName('locality') || getComponentName('administrative_area_level_1');
        } else if (componentType === 'postalCode') {
            return getComponentName('postal_code'); // Capture directe du code postal
        }
        return '';
    }

    for (const componentType of ADDRESS_COMPONENT_TYPES_IN_FORM) {
        const inputElement = getFormInputElement(componentType);
        if (inputElement) {
            inputElement.value = getComponentText(componentType);

            // Déclenche les événements 'input' et 'change'
            inputElement.dispatchEvent(new Event('input', { bubbles: true }));
            inputElement.dispatchEvent(new Event('change', { bubbles: true }));
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

    autocomplete = new Autocomplete(getFormInputElement('adress'), {
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

        const input = getFormInputElement('adress');
        input.blur(); // Pour forcer la fermeture

        // Supprime/masque les suggestions après un court délai
        setTimeout(() => {
            const pacContainers = document.querySelectorAll('.pac-container');
            pacContainers.forEach(container => {
                container.parentNode?.removeChild(container); // supprime complètement
            });
        }, 200);

        document.getElementById('additional-fields').style.display = 'block';
    });
}

function setupAutocompleteTrigger() {
    const input = getFormInputElement('adress');

    input.addEventListener('input', () => {
        if (input.value.length >= 3 && !initialized) {
            initAutocomplete();
        }
    });

    input.addEventListener('blur', () => {
        if (!autocomplete?.getPlace?.()) {
            // Réinitialisation si aucune sélection faite
            getFormInputElement('city').value = '';
            getFormInputElement('postalCode').value = '';
            document.getElementById('additional-fields').style.display = 'none';
        }
    });
}

setupAutocompleteTrigger();