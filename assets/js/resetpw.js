import { 
    evaluatePasswordStrength,
    updateEntropy,
    bindPasswordGenerator
 } from "./modules/passwordUtils.js";
let pass = false;

document.querySelector("#reset_password_form_plainPassword_first").addEventListener("input", checkPass);

document.addEventListener("DOMContentLoaded", () => {
    // Génération du mot de passe fort en lieu et place de la suggestion Google
    const generateBtn = document.querySelector("#generate-password");
    const passwordInput = document.querySelector("#reset_password_form_plainPassword_first");

    bindPasswordGenerator(generateBtn, passwordInput);
});

function checkAll(){
    document.querySelector("#submit-reset-pw").setAttribute("disabled", "disabled");    
    if(pass){        
        document.querySelector("#submit-reset-pw").removeAttribute("disabled");
    }
}

function checkPass() {
    // On récupère le mot de passe tapé
    let mdp = this.value

    // On récupère l'élément d'affichage de l'entropie
    let entropyElement = document.querySelector("#entropy");

    // On évalue la force du mot de passe
    let entropy = evaluatePasswordStrength(mdp);

    pass = updateEntropy(entropyElement, entropy);

    checkAll();
}