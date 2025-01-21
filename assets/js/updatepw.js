let pass = false;

document.querySelector("#update_password_user_form_newPassword_first").addEventListener("input", checkPass);

function checkAll(){
    document.querySelector("#submit-update-pw").setAttribute("disabled", "disabled");    
    if(pass){
        document.querySelector("#submit-update-pw").removeAttribute("disabled");
    }
}

const PasswordStrength = {
    STRENGTH_VERY_WEAK: 'Très faible',
    STRENGTH_WEAK: 'Faible',
    STRENGTH_MEDIUM: 'Moyen',
    STRENGTH_STRONG: 'Fort',
    STRENGTH_VERY_STRONG: 'Très fort',
}

function checkPass(){
    // On récupère le mot de passe tapé
    let mdp = this.value;

    // On récupère l'élément d'affichage de l'entropie
    let entropyElement = document.querySelector("#entropy");

    // On évalue la force du mot de passe
    let entropy = evaluatePasswordStrength(mdp);

    entropyElement.classList.remove("text-very-weak", "text-weak", "text-medium", "text-strong", "text-very-strong");

    // On attribue la couleur en fonction de l'entropie
    switch(entropy){
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

function evaluatePasswordStrength(password){
    // On calcule la longueur du mot de passe
    let length = password.length;

    // Si le mot de passe est vide
    if(!length){
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }

    // On crée un objet qui contiendra les caractères et leur nombre
    let passwordChars = {};

    for(let index = 0; index < password.length; index++){
        let charCode = password.charCodeAt(index);
        passwordChars[charCode] = (passwordChars[charCode] || 0) + 1;
    }

    // Compte le nombre de caractères différents dans le mot de passe
    let chars = Object.keys(passwordChars).length;

    // On initialise les variables des types de caractères
    let control = 0, digit = 0, upper = 0, lower = 0, symbol = 0, other = 0;

    for(let [chr, count] of Object.entries(passwordChars)){
        chr = Number(chr);
        if(chr < 32 || chr === 127){
            // Caractère de contrôle
            control = 33;
        }else if(chr >= 48 && chr <= 57){
            // Chiffres
            digit = 10;
        }else if(chr >= 65 && chr <= 90){
            // Majuscules
            upper = 26;
        }else if(chr >= 97 && chr <= 122){
            // Minuscules
            lower = 26;
        }else if(chr >= 128){
            // Autres caractères 
            other = 128;
        }else{
            // Symboles
            symbol = 33;
        }
    }

    // On calcule le pool de caractères
    let pool = control + digit + upper + lower + other + symbol;

    // Formule de calcul de l'entropie
    let entropy = chars * Math.log2(pool) + (length - chars) * Math.log2(chars);

    if(entropy >= 120){
        return PasswordStrength.STRENGTH_VERY_STRONG;
    }else if(entropy >= 100){
        return PasswordStrength.STRENGTH_STRONG;
    }else if(entropy >= 80){
        return PasswordStrength.STRENGTH_MEDIUM;
    }else if(entropy >= 60){
        return PasswordStrength.STRENGTH_WEAK;
    }else{
        return PasswordStrength.STRENGTH_VERY_WEAK;
    }
}