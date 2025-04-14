// Define the confirmation handler once outside both event listeners
const confirmationHandler = (event, message) => {
  const confirmed = confirm(message);
  if (!confirmed) {
    event.preventDefault();
  }
};

// Utility function to attach confirmations to elements
const attachConfirmation = (selector, message) => {
  document.querySelectorAll(selector).forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(event, message);
    });
  });
};

// Main initialization function
const initConfirmations = () => {
  // Define all your button types and messages
  const confirmations = [
    { selector: ".delete-zone", message: "Êtes vous sûr de vouloir supprimer cette Zone?" },
    { selector: ".delete-productLine", message: "Êtes vous sûr de vouloir supprimer cette Ligne?" },
    { selector: ".delete-category", message: "Êtes vous sûr de vouloir supprimer cette categorie?" },
    { selector: ".delete-button", message: "Êtes vous sûr de vouloir supprimer ce Bouton?" },
    { selector: ".delete-user", message: "Êtes vous sûr de vouloir supprimer cet Utilisateur?" },
    { selector: ".definitively-delete-user", message: "Êtes vous sûr de vouloir supprimer cet Utilisateur? Celà supprimera également tous les incidents et documents liés à cet utilisateur." },
    { selector: ".delete-upload", message: "Êtes vous sûr de vouloir supprimer ce Document?" },
    { selector: ".delete-incident", message: "Êtes vous sûr de vouloir supprimer cet Incident?" },
    { selector: ".delete-incidentCategory", message: "Êtes vous sûr de vouloir supprimer ce Type d'Incident?" },
    { selector: ".delete-department", message: "Êtes vous sûr de vouloir supprimer ce Service?" },
    { selector: ".submit-approval", message: "Êtes vous sûr de vouloir soumettre ce formulaire de validation?" },
    { selector: ".submit-disapproval-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-upload-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-incident-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-views-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".download-non-validated-upload", message: "CE DOCUMENT N'A PAS ENCORE ÉTÉ VALIDÉ. CONTINUER DE TOUTE MANIÈRE? EN CAS DE QUESTION, CONTACTER VOTRE RESPONSABLE." },
    { selector: ".download-refused-upload", message: "CE DOCUMENT A ÉTÉ REFUSÉ. CONTINUER DE TOUTE MANIÈRE? EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE." },
    { selector: ".download-refused-but-old", message: "LE NOUVEAU DOCUMENT A ÉTÉ REFUSÉ, LE PRÉCÉDENT SERA AFFICHÉ. EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE." },
    { selector: ".download-non-validated-but-old", message: "CE DOCUMENT N'A PAS ENCORE ÉTÉ VALIDÉ. LE PRÉCÉDENT SERA AFFICHÉ. EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE." },
    { selector: ".delete-team", message: "Êtes vous sûr de vouloir supprimer cette Equipe?" },
    { selector: ".delete-uap", message: "Êtes vous sûr de vouloir supprimer cet UAP?" },
    { selector: ".delete-operator", message: "Êtes vous sûr de vouloir supprimer cet Opérateur?" },
    { selector: ".submit-operator-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".delete-products", message: "Êtes vous sûr de vouloir supprimer ce Produit?" }
  ];

  // Attach all confirmations
  confirmations.forEach(conf => {
    attachConfirmation(conf.selector, conf.message);
  });
};

// Initialize on both regular page load and turbo frame loads
window.addEventListener("turbo:load", initConfirmations);
window.addEventListener("turbo:frame-load", initConfirmations);