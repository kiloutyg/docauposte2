// Define the confirmation handler once outside both event listeners
/**
 * Handles the confirmation dialog for a specific action.
 * 
 * @param {Event} event - The event object that triggered the confirmation.
 * @param {string} message - The message to display in the confirmation dialog.
 */
const confirmationHandler = (event, message) => {
  const confirmed = confirm(message);
  if (!confirmed) {
    event.preventDefault();
  }
};

// Utility function to attach confirmations to elements
/**
 * Attaches a confirmation dialog to specific elements.
 * 
 * @param {string} selector - The CSS selector for the elements to attach the confirmation to.
 * @param {string} message - The message to display in the confirmation dialog.
 */
const attachConfirmation = (selector, message) => {
  document.querySelectorAll(selector).forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(event, message);
    });
  });
};

// Main initialization function
/**
 * Initializes the confirmation utility by attaching confirmations to specific elements.
 */
const initConfirmations = () => {
  // Define all your button types and messages
  const confirmations = [
    { selector: ".create-user", message: "Êtes vous sûr de vouloir créer un nouvel Utilisateur?" },

    { selector: ".definitively-delete-user", message: "Êtes vous sûr de vouloir supprimer cet Utilisateur? Celà supprimera également tous les incidents et documents liés à cet utilisateur." },

    { selector: ".delete-button", message: "Êtes vous sûr de vouloir supprimer ce Bouton?" },
    { selector: ".delete-category", message: "Êtes vous sûr de vouloir supprimer cette categorie?" },
    { selector: ".delete-department", message: "Êtes vous sûr de vouloir supprimer ce Service?" },
    { selector: ".delete-incident", message: "Êtes vous sûr de vouloir supprimer cet Incident?" },
    { selector: ".delete-incidentCategory", message: "Êtes vous sûr de vouloir supprimer ce Type d'Incident?" },
    { selector: ".delete-operator", message: "Êtes vous sûr de vouloir supprimer cet Opérateur?" },
    { selector: ".delete-all-inactive-operator", message: "Êtes vous sûr de vouloir supprimer ces Opérateurs définitivement?" },
    { selector: ".delete-productLine", message: "Êtes vous sûr de vouloir supprimer cette Ligne?" },
    { selector: ".delete-products", message: "Êtes vous sûr de vouloir supprimer ce Produit?" },
    { selector: ".delete-shiftLeaders", message: "Êtes vous sûr de vouloir enlever cet utilisateur de la liste des ShiftLeaders?" },
    { selector: ".delete-team", message: "Êtes vous sûr de vouloir supprimer cette Equipe?" },
    { selector: ".delete-uap", message: "Êtes vous sûr de vouloir supprimer cet UAP?" },
    { selector: ".delete-upload", message: "Êtes vous sûr de vouloir supprimer ce Document?" },
    { selector: ".delete-user", message: "Êtes vous sûr de vouloir supprimer cet Utilisateur?" },
    { selector: ".delete-workstation", message: "Êtes vous sûr de vouloir supprimer cette Station de travail?" },
    { selector: ".delete-zone", message: "Êtes vous sûr de vouloir supprimer cette Zone?" },

    { selector: ".download-non-validated-but-old", message: "CE DOCUMENT N'A PAS ENCORE ÉTÉ VALIDÉ. LE PRÉCÉDENT SERA AFFICHÉ. EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE." },
    { selector: ".download-non-validated-upload", message: "CE DOCUMENT N'A PAS ENCORE ÉTÉ VALIDÉ. CONTINUER DE TOUTE MANIÈRE? EN CAS DE QUESTION, CONTACTER VOTRE RESPONSABLE." },
    { selector: ".download-refused-but-old", message: "LE NOUVEAU DOCUMENT A ÉTÉ REFUSÉ, LE PRÉCÉDENT SERA AFFICHÉ. EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE." },
    { selector: ".download-refused-upload", message: "CE DOCUMENT A ÉTÉ REFUSÉ. CONTINUER DE TOUTE MANIÈRE? EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE." },

    { selector: ".modify-user", message: "Êtes vous sûr de vouloir modifier cet Utilisateur?" },

    { selector: ".submit-approval", message: "Êtes vous sûr de vouloir soumettre ce formulaire de validation?" },
    { selector: ".submit-disapproval-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-incident-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-operator-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-upload-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },
    { selector: ".submit-views-modification", message: "Êtes vous sûr de vouloir soumettre ces modifications?" },

    { selector: ".transfer-work", message: "Êtes vous sûr de vouloir transferer le travail de cet utilisateur à un autre utilisateur?" },

  ];

  // Attach all confirmations
  confirmations.forEach(conf => {
    attachConfirmation(conf.selector, conf.message);
  });
};

// Initialize on both regular page load and turbo frame loads
window.addEventListener("turbo:load", initConfirmations);
window.addEventListener("turbo:frame-load", initConfirmations);