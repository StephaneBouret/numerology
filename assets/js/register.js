import { 
    evaluatePasswordStrength,
    updateEntropy,
    bindPasswordGenerator
 } from "./modules/passwordUtils.js";

// Variables booléennes
let firstname = false;
let lastname = false;
let email = false;
let adress = false;
let postalCode = false
let city = false;
let phone = false;
let rgpd = false;
let pass = false;

// Fonction pour initialiser la validation des champs
function initializeValidation() {
    // Liste des champs et des fonctions de validation
    const fields = [{
            id: "#registration_form_firstname",
            handler: checkFirstname
        },
        {
            id: "#registration_form_lastname",
            handler: checkLastname
        },
        {
            id: "#registration_form_email",
            handler: checkEmail
        },
        {
            id: "#registration_form_adress",
            handler: checkAdress
        },
        {
            id: "#registration_form_postalCode",
            handler: checkPostalCode
        },
        {
            id: "#registration_form_phone",
            handler: checkPhone
        },
        {
            id: "#registration_form_city",
            handler: checkCity
        },
        {
            id: "#registration_form_agreeTerms",
            handler: checkRgpd
        },
        {
            id: "#registration_form_plainPassword",
            handler: checkPass
        },
    ];

    fields.forEach(field => {
        const element = document.querySelector(field.id);
        if (element) {
            // Supprimer les écouteurs précédents (au cas où)
            element.removeEventListener("input", field.handler);
            element.removeEventListener("change", field.handler);

            // Ajouter les deux types d'écouteurs
            element.addEventListener("input", field.handler);
            element.addEventListener("change", field.handler);
        }
    });

    // Validation initiale
    checkAll();
}

// Observer les changements du DOM
document.addEventListener("DOMContentLoaded", () => {
    initializeValidation(); // Initialisation lors du chargement de la page

    // Génération du mot de passe
    const generateBtn = document.querySelector("#generate-password");
    const passwordInput = document.querySelector("#registration_form_plainPassword");

    bindPasswordGenerator(generateBtn, passwordInput);

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.type === "childList") {
                // Si le formulaire est ajouté ou mis à jour
                const form = document.querySelector("#registration_form");
                if (form && mutation.target.contains(form)) {
                    initializeValidation();
                }
            }
        });
    });

    // Observer les modifications du DOM
    observer.observe(document.body, {
        childList: true,
        subtree: true, // Observer tous les noeuds enfants
    })
});

function checkFirstname() {
    firstname = this.value.length > 2;
    checkAll();
}

function checkLastname() {
    lastname = this.value.length > 1;
    checkAll();
}

function checkEmail() {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    email = emailRegex.test(this.value);
    checkAll();
}

function checkAdress() {
    adress = this.value.length > 1;
    checkAll();
}

function checkPostalCode() {
    const postalCodeValue = this.value; // Obtenez la valeur actuelle
    const postalCodeRegex = /^((0[1-9])|([1-8][0-9])|(9[0-8])|(2A)|(2B)) *([0-9]{3})?$/i;
    postalCode = postalCodeRegex.test(postalCodeValue);
    checkAll();
}

function checkCity() {
    const cityValue = this.value; // Obtenez la valeur actuelle
    const cityRegex = /^\s*\p{L}{1}[\p{L}\p{N} '-.=#/]*$/gmu;
    city = cityRegex.test(cityValue);
    checkAll();
}

function checkPhone() {
    const phoneRegex = /(?:([+]\d{1,4})[-.\s]?)?(?:[(](\d{1,3})[)][-.\s]?)?(\d{1,4})[-.\s]?(\d{1,4})[-.\s]?(\d{1,9})/g;
    phone = phoneRegex.test(this.value);
    checkAll();
}

function checkRgpd() {
    rgpd = this.checked;
    checkAll();
}

function checkAll() {
    const submitBtn = document.querySelector("#submit-button");
    submitBtn.setAttribute("disabled", "disabled");    
    if (email && firstname && lastname && adress && postalCode && city && phone && pass && rgpd) {
        submitBtn.removeAttribute("disabled");
    }
}

function checkPass() {
    // On récupère le mot de passe tapé
    let mdp = this.value;

    // On récupère l'élément d'affichage de l'entropie
    let entropyElement = document.querySelector("#entropy");

    // On évalue la force du mot de passe
    let entropy = evaluatePasswordStrength(mdp);

    pass = updateEntropy(entropyElement, entropy);

    checkAll();
}