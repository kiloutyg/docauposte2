// document.addEventListener("DOMContentLoaded", () => {
//   const deleteZoneButtons = document.querySelectorAll(".delete-zone");
//   const deleteProductLineButtons = document.querySelectorAll(
//     ".delete-productline"
//   );
//   const deleteCategoryButtons = document.querySelectorAll(".delete-category");
//   const deleteButtonButtons = document.querySelectorAll(".delete-button");
//   const deleteUserButtons = document.querySelectorAll(".delete-user");
//   const deleteUploadButtons = document.querySelectorAll(".delete-upload");

//   const confirmationHandler = (event, message) => {
//     const confirmed = confirm(message);
//     if (!confirmed) {
//       event.preventDefault();
//     }
//   };

//   deleteZoneButtons.forEach((button) => {
//     button.addEventListener("click", (event) => {
//       confirmationHandler(
//         event,
//         "Êtes vous sûr de vouloir supprimer cette Zone?"
//       );
//     });
//   });

//   deleteProductLineButtons.forEach((button) => {
//     button.addEventListener("click", (event) => {
//       confirmationHandler(
//         event,
//         "Êtes vous sûr de vouloir supprimer cette Ligne?"
//       );
//     });
//   });
//   deleteCategoryButtons.forEach((button) => {
//     button.addEventListener("click", (event) => {
//       confirmationHandler(
//         event,
//         "Êtes vous sûr de vouloir supprimer cette categorie?"
//       );
//     });
//   });
//   deleteButtonButtons.forEach((button) => {
//     button.addEventListener("click", (event) => {
//       confirmationHandler(
//         event,
//         "Êtes vous sûr de vouloir supprimer ce Bouton?"
//       );
//     });
//   });
//   deleteUserButtons.forEach((button) => {
//     button.addEventListener("click", (event) => {
//       confirmationHandler(
//         event,
//         "Êtes vous sûr de vouloir supprimer cet Utilisateur?"
//       );
//     });
//   });
//   deleteUploadButtons.forEach((button) => {
//     button.addEventListener("click", (event) => {
//       confirmationHandler(
//         event,
//         "Êtes vous sûr de vouloir supprimer ce Document?"
//       );
//     });
//   });
// });
document.addEventListener("DOMContentLoaded", () => {
  const deleteZoneButtons = document.querySelectorAll(".delete-zone");
  const deleteProductLineButtons = document.querySelectorAll(
    ".delete-productline"
  );
  const deleteCategoryButtons = document.querySelectorAll(".delete-category");
  const deleteButtonButtons = document.querySelectorAll(".delete-button");
  const deleteUserButtons = document.querySelectorAll(".delete-user");
  const deleteUploadButtons = document.querySelectorAll(".delete-upload");

  deleteZoneButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const confirmed = confirm(
        "Êtes vous sûr de vouloir supprimer cette Zone?"
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });

  deleteProductLineButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const confirmed = confirm(
        "Êtes vous sûr de vouloir supprimer cette Ligne?"
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
  deleteCategoryButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const confirmed = confirm(
        "Êtes vous sûr de vouloir supprimer cette Catégorie?"
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
  deleteButtonButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const confirmed = confirm(
        "Êtes vous sûr de vouloir supprimer ce Boutton?"
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
  deleteUserButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const confirmed = confirm(
        "Êtes vous sûr de vouloir supprimer cet Utilisateur?"
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
  deleteUploadButtons.forEach((button) => {
    button.addEventListener("click", (event) => {
      const confirmed = confirm(
        "Êtes vous sûr de vouloir supprimer ce Document?"
      );

      if (!confirmed) {
        event.preventDefault();
      }
    });
  });
});
