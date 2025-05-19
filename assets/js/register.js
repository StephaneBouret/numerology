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
    const fields = [
        { id: "#registration_form_firstname", handler: checkFirstname},
        { id: "#registration_form_lastname", handler: checkLastname },
        { id: "#registration_form_email", handler: checkEmail },
        { id: "#registration_form_adress", handler: checkAdress },
        { id: "#registration_form_postalCode", handler: checkPostalCode },
        { id: "#registration_form_phone", handler: checkPhone },
        { id: "#registration_form_city", handler: checkCity },
        { id: "#registration_form_agreeTerms", handler: checkRgpd },
        { id: "#registration_form_plainPassword", handler: checkPass },
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

    const observer = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            if (mutation.type === "childList") {
                // Si le formulaire est ajouté ou mis à jour
                const form = document.querySelector("#registration_form");
                if (form && mutation.target.contains(form)) {
                    console.log("Formulaire détecté ou mis à jour");
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

// document.querySelector("#registration_form_firstname").addEventListener("input", checkFirstname);
// document.querySelector("#registration_form_lastname").addEventListener("input", checkLastname);
// document.querySelector("#registration_form_email").addEventListener("input", checkEmail);
// document.querySelector("#registration_form_adress").addEventListener("input", checkAdress);
// document.querySelector("#registration_form_postalCode").addEventListener("input", checkPostalCode);
// document.querySelector("#registration_form_phone").addEventListener("input", checkPhone);
// document.querySelector("#registration_form_city").addEventListener("input", checkCity);
// document.querySelector("#registration_form_agreeTerms").addEventListener("input", checkRgpd);
// document.querySelector('#registration_form_plainPassword').addEventListener("input", checkPass);

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
    document.querySelector("#submit-button").setAttribute("disabled", "disabled");
    if (email && firstname && lastname && adress && postalCode && city && phone && pass && rgpd) {    
        document.querySelector("#submit-button").removeAttribute("disabled");
    }
}

const PasswordStrength = {
    STRENGTH_VERY_WEAK: 'Très faible',
    STRENGTH_WEAK: 'Faible',
    STRENGTH_MEDIUM: 'Moyen',
    STRENGTH_STRONG: 'Fort',
    STRENGTH_VERY_STRONG: 'Très fort',
}

function checkPass() {
    // On récupère le mot de passe tapé
    let mdp = this.value;

    // On récupère l'élément d'affichage de l'entropie
    let entropyElement = document.querySelector("#entropy");

    // On évalue la force du mot de passe
    let entropy = evaluatePasswordStrength(mdp);

    entropyElement.classList.remove("text-very-weak", "text-weak", "text-medium", "text-strong", "text-very-strong");

    // On attribue la couleur en fonction de l'entropié
    switch (entropy) {
        case 'Très faible':
            entropyElement.classList.add("text-very-weak");
            pass = false;
            break;
        case 'Faible':
            entropyElement.classList.add("text-weak");
            pass = false;
            break;
        case 'Moyen':
            entropyElement.classList.add("text-medium");
            pass = false;
            break;
        case 'Fort':
            entropyElement.classList.add("text-strong");
            pass = true;
            break;
        case 'Très fort':
            entropyElement.classList.add("text-very-strong");
            pass = true;
            break;
        default:
            entropyElement.classList.add("text-very-weak");
            pass = false;
    }

    entropyElement.textContent = entropy;

    checkAll();
}

function evaluatePasswordStrength(password) {
    // On calcule la longueur du mot de passe
    let length = password.length;

    // Si le mot de passe est vide
    if (!length) {
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }

    // On crée un objet qui contiendra les caractères et leur nombre
    let passwordChars = {};

    for (let index = 0; index < password.length; index++) {
        let charCode = password.charCodeAt(index);
        passwordChars[charCode] = (passwordChars[charCode] || 0) + 1;
    }

    // Compte le nombre de caractères différents dans le mot de passe
    let chars = Object.keys(passwordChars).length;

    // On initialise les variables des types de caractères    
    let control = 0,
        digit = 0,
        upper = 0,
        lower = 0,
        symbol = 0,
        other = 0;

    for (let [chr, count] of Object.entries(passwordChars)) {
        chr = Number(chr);
        if (chr < 32 || chr === 127) {
            // Caractère de contrôle
            control = 33;
        } else if (chr >= 48 && chr <= 57) {
            // Chiffres
            digit = 10;
        } else if (chr >= 65 && chr <= 90) {
            // Majuscules
            upper = 26;
        } else if (chr >= 97 && chr <= 122) {
            // Minuscules
            lower = 26;
        } else if (chr >= 128) {
            // Autres caractères
            other = 128;
        } else {
            // Symboles
            symbol = 33;
        }
    }

    // On calcule le pool de caractère
    let pool = control + digit + upper + lower + other + symbol;

    // Formule de calcul de l'entropie
    let entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

    if (entropy >= 120) {
        return PasswordStrength.STRENGTH_VERY_STRONG;
    } else if (entropy >= 100) {
        return PasswordStrength.STRENGTH_STRONG;
    } else if (entropy >= 80) {
        return PasswordStrength.STRENGTH_MEDIUM;
    } else if (entropy >= 60) {
        return PasswordStrength.STRENGTH_WEAK;
    } else {
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }
}