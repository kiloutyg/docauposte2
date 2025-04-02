window.addEventListener("turbo:load", () => {
  const deleteZoneButtons = document.querySelectorAll(".delete-zone");
  const deleteProductLineButtons = document.querySelectorAll(".delete-productLine");
  const deleteCategoryButtons = document.querySelectorAll(".delete-category");
  const deleteButtonButtons = document.querySelectorAll(".delete-button");
  const deleteUserButtons = document.querySelectorAll(".delete-user");
  const deleteUserButtonsDefinitively = document.querySelectorAll(".definitively-delete-user");
  const deleteUploadButtons = document.querySelectorAll(".delete-upload");
  const deleteIncidentButtons = document.querySelectorAll(".delete-incident");
  const deleteIncidentCategoryButtons = document.querySelectorAll(".delete-incidentCategory");
  const deleteDepartmentButtons = document.querySelectorAll(".delete-department");
  const submitApprovalButtons = document.querySelectorAll(".submit-approval");
  const submitDisapprovalModifcationButtons = document.querySelectorAll(".submit-disapproval-modification");
  const submitUploadModifcationButtons = document.querySelectorAll(".submit-upload-modification");
  const submitIncidentModifcationButtons = document.querySelectorAll(".submit-incident-modification");
  const submitViewsModifcationButtons = document.querySelectorAll(".submit-views-modification");
  const downloadNonValidatedUploadButtons = document.querySelectorAll(".download-non-validated-upload");
  const downloadRefusedUploadButtons = document.querySelectorAll(".download-refused-upload");
  const downloadRefusedButOldButtons = document.querySelectorAll(".download-refused-but-old");
  const downloadNonValidatedButOldUploadButtons = document.querySelectorAll(".download-non-validated-but-old");
  const deleteTeamButtons = document.querySelectorAll(".delete-team");
  const deleteUapButtons = document.querySelectorAll(".delete-uap");

  const confirmationHandler = (event, message) => {
    const confirmed = confirm(message);
    if (!confirmed) {
      event.preventDefault();
    }
  };

  deleteZoneButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cette Zone?"
      );
    });
  });

  deleteProductLineButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cette Ligne?"
      );
    });
  });
  deleteCategoryButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cette categorie?"
      );
    });
  });
  deleteButtonButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer ce Bouton?"
      );
    });
  });
  deleteUserButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cet Utilisateur?"
      );
    });
  });
  deleteUserButtonsDefinitively.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cet Utilisateur? Celà supprimera également tous les incidents et documents liés à cet utilisateur."
      );
    });
  });
  deleteUploadButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer ce Document?"
      );
    });
  });
  deleteIncidentButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cet Incident?"
      );
    });
  });
  deleteIncidentCategoryButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer ce Type d'Incident?"
      );
    });
  });
  deleteDepartmentButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer ce Service?"
      );
    });
  });
  submitApprovalButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir soumettre ce formulaire de validation?"
      );
    });
  });
  submitDisapprovalModifcationButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir soumettre ces modifications?"
      );
    });
  });
  submitUploadModifcationButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir soumettre ces modifications?"
      );
    });
  });
  submitIncidentModifcationButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir soumettre ces modifications?"
      );
    });
  });
  submitViewsModifcationButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir soumettre ces modifications?"
      );
    });
  });
  downloadNonValidatedUploadButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "CE DOCUMENT N'A PAS ENCORE ÉTÉ VALIDÉ. CONTINUER DE TOUTE MANIÈRE? EN CAS DE QUESTION, CONTACTER VOTRE RESPONSABLE."
      );
    });
  });
  downloadNonValidatedButOldUploadButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "CE DOCUMENT N'A PAS ENCORE ÉTÉ VALIDÉ. LE PRÉCÉDENT SERA AFFICHÉ. EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE."
      );
    });
  });
  downloadRefusedUploadButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "CE DOCUMENT A ÉTÉ REFUSÉ. CONTINUER DE TOUTE MANIÈRE? EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE."
      );
    });
  });
  downloadRefusedButOldButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "LE NOUVEAU DOCUMENT A ÉTÉ REFUSÉ, LE PRÉCÉDENT SERA AFFICHÉ. EN CAS DE QUESTION, CONTACTEZ VOTRE RESPONSABLE."
      );
    });
  });

  deleteTeamButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cette Equipe?"
      );
    });
  });

  deleteUapButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cet UAP?"
      );
    });
  });
});


window.addEventListener("turbo:frame-load", () => {

  const deleteOperatorsButtons = document.querySelectorAll(".delete-operator");
  const submitOperatorsModificationButtons = document.querySelectorAll(".submit-operator-modification");
  const deleteProductsButtons = document.querySelectorAll(".delete-products");

  const confirmationHandler = (event, message) => {
    const confirmed = confirm(message);
    if (!confirmed) {
      event.preventDefault();
    }
  };

  deleteOperatorsButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer cet Opérateur?"
      );
    });
  });

  submitOperatorsModificationButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir soumettre ces modifications?"
      );
    });
  });

  deleteProductsButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer ce Produit?"
      );
    });
  });

});
