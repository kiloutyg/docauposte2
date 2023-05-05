window.addEventListener("turbo:load", () => {
  const deleteZoneButtons = document.querySelectorAll(".delete-zone");
  const deleteProductLineButtons = document.querySelectorAll(
    ".delete-productline"
  );
  const deleteCategoryButtons = document.querySelectorAll(".delete-category");
  const deleteButtonButtons = document.querySelectorAll(".delete-button");
  const deleteUserButtons = document.querySelectorAll(".delete-user");
  const deleteUploadButtons = document.querySelectorAll(".delete-upload");

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
  deleteUploadButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      confirmationHandler(
        event,
        "Êtes vous sûr de vouloir supprimer ce Document?"
      );
    });
  });
});
