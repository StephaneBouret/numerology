document.addEventListener('DOMContentLoaded', function () {
    // Sélectionnez le champ contentType
    const contentTypeField = document.querySelector('select[name*="contentType]');
    
    // Sélectionnez les divs à afficher/masquer
    const videoFileDiv = document.querySelector('.field-videoFile');
    const partialFileDiv = document.querySelector('.field-partialFile');

    // Fonction pour mettre à jour l'affichage des divs
    function updateDisplay() {
        const selectedValue = contentTypeField.value;
        
        if (selectedValue === 'Vidéo') {
            videoFileDiv.style.display = 'block';
            partialFileDiv.style.display = 'none';
        } else if (selectedValue === 'Twig' || selectedValue === 'Lien') {
            videoFileDiv.style.display = 'none';
            partialFileDiv.style.display = 'block';
        } else {
            videoFileDiv.style.display = 'none';
            partialFileDiv.style.display = 'none';
        }
    }

    // Appele la fonction initialement pour définir l'état initial
    updateDisplay();

    // Ajoutez un écouteur d'événement pour détecter les changements
    contentTypeField.addEventListener('change', updateDisplay);
})