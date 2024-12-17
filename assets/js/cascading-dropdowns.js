// docauposte2/assets/js/cascading-dropdowns.js

import { getEntityData } from './server-variable.js';
import { filterData, populateDropdown, resetDropdowns, preselectValues } from './dropdown-utils.js';

let zonesData = null;
let productLinesData = null;
let categoriesData = null;
let buttonsData = null;

document.addEventListener("turbo:load", () => {
  getEntityData()
    .then((data) => {
      zonesData = data.zones;
      productLinesData = data.productLines;
      categoriesData = data.categories;
      buttonsData = data.buttons;

      initCascadingDropdowns();
      resetDropdowns(
        document.getElementById("zone"),
        document.getElementById("productline"),
        document.getElementById("category"),
        document.getElementById("upload_button")
      );
      preselectDropdownValues();
    })
    .catch((error) => {
      console.log('Error fetching entity data:', error);
    });
});

function initCascadingDropdowns() {
  const zoneDropdown = document.getElementById("zone");
  const productLineDropdown = document.getElementById("productline");
  const categoryDropdown = document.getElementById("category");
  const buttonDropdown = document.getElementById("upload_button");

  if (zoneDropdown && productLineDropdown && categoryDropdown) {
    populateDropdown(zoneDropdown, zonesData, {
      defaultText: 'Sélectionner une Zone',
    });

    zoneDropdown.addEventListener("change", (event) => {
      const selectedValue = parseInt(event.target.value);
      const filteredProductLines = filterData(productLinesData, "zone_id", selectedValue);

      populateDropdown(productLineDropdown, filteredProductLines, {
        defaultText: 'Sélectionner une ProductLine',
        textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
      });

      // Reset dependent dropdowns
      resetDropdowns(categoryDropdown, buttonDropdown);
    });

    productLineDropdown.addEventListener("change", (event) => {
      const selectedValue = parseInt(event.target.value);
      const filteredCategories = filterData(categoriesData, "product_line_id", selectedValue);

      populateDropdown(categoryDropdown, filteredCategories, {
        defaultText: 'Sélectionner une Categorie',
        textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
      });

      // Reset dependent dropdown
      resetDropdowns(buttonDropdown);
    });

    categoryDropdown.addEventListener("change", (event) => {
      const selectedValue = parseInt(event.target.value);
      const filteredButtons = filterData(buttonsData, "category_id", selectedValue);

      populateDropdown(buttonDropdown, filteredButtons, {
        defaultText: 'Sélectionner un Bouton',
        textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
      });
    });
  }
}

function preselectDropdownValues() {
  const zoneDropdown = document.getElementById("zone");
  const productLineDropdown = document.getElementById("productline");
  const categoryDropdown = document.getElementById("category");
  const buttonDropdown = document.getElementById("upload_button");

  preselectValues([
    {
      dropdown: zoneDropdown,
      data: zonesData,
      id: zoneIdFromServer,
      options: { defaultText: 'Sélectionner une Zone' },
    },
  ]);

  if (zoneIdFromServer && productLineDropdown) {
    const filteredProductLines = filterData(productLinesData, "zone_id", parseInt(zoneIdFromServer));
    populateDropdown(productLineDropdown, filteredProductLines, {
      selectedId: productLineIdFromServer,
      defaultText: 'Sélectionner une Ligne',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }

  if (productLineIdFromServer && categoryDropdown) {
    const filteredCategories = filterData(categoriesData, "product_line_id", parseInt(productLineIdFromServer));
    populateDropdown(categoryDropdown, filteredCategories, {
      selectedId: categoryIdFromServer,
      defaultText: 'Sélectionner une Catégorie',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }

  if (categoryIdFromServer && buttonDropdown) {
    const filteredButtons = filterData(buttonsData, "category_id", parseInt(categoryIdFromServer));
    populateDropdown(buttonDropdown, filteredButtons, {
      selectedId: buttonIdFromServer,
      defaultText: 'Sélectionner un Bouton',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }
}

// Event listener for form submit event
let modifyForm = document.querySelector("#modifyForm");
if (modifyForm) {
  modifyForm.addEventListener("submit", function (event) {
    event.preventDefault();

    // Create a new FormData object
    let formData = new FormData();

    // Get the file input element
    let fileInput = document.querySelector("#upload_file");

    // Get the CSRF token
    let csrfTokenInput = document.querySelector("#upload__token");

    // Get the CSRF token value
    let csrfTokenValue = csrfTokenInput.value;

    // Add the CSRF token to formData
    formData.append("upload[_token]", csrfTokenValue);

    if (fileInput.files.length > 0) {
      // A file was selected
      let file = fileInput.files[0];

      // Add the file to formData
      formData.append("upload[file]", file);
    }

    // Get the dropdown elements

    let buttonDropdown = document.getElementById("upload_button");
    if (buttonDropdown) {
      let buttonValue = parseInt(
        buttonDropdown.options[buttonDropdown.selectedIndex].value,
        10
      ); // Add the values to formData
      formData.append("upload[button]", buttonValue);
    }
    // Get the filename input
    let filenameInput = document.getElementById("upload_filename");

    // Get the selected values

    // Get the filename value
    let filenameValue = filenameInput.value;

    if (filenameValue) {
      formData.append("upload[filename]", filenameValue);
    }

    // Get the upload ID from the URL
    let form = document.getElementById("modifyForm");
    let actionUrl = form.getAttribute("action");

    // Send formData to server...
    fetch(actionUrl, {
      method: "POST",
      body: formData,
    })
      .then((response) => response)
      .catch((error) => {
        then((response) => response.json());
        console.error("Error:", error);
      });
  });
}
