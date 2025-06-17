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
        document.getElementById("productLine"),
        document.getElementById("category"),
        document.getElementById("upload_button")
      );
      preselectDropdownValues();
    })
    .catch((error) => {
    });
});

function initCascadingDropdowns() {
  const zoneDropdown = document.getElementById("zone");
  const productLineDropdown = document.getElementById("productLine");
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
        defaultText: 'Sélectionner une Ligne',
        textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
      });

      // Reset dependent dropdowns
      resetDropdowns(categoryDropdown, buttonDropdown);
    });

    productLineDropdown.addEventListener("change", (event) => {
      const selectedValue = parseInt(event.target.value);
      const filteredCategories = filterData(categoriesData, "product_line_id", selectedValue);

      populateDropdown(categoryDropdown, filteredCategories, {
        defaultText: 'Sélectionner une Catégorie',
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
  const productLineDropdown = document.getElementById("productLine");
  const categoryDropdown = document.getElementById("category");
  const buttonDropdown = document.getElementById("upload_button");

  console.log('preselect dropdown stuff');
  console.log('zoneIdFromServer', zoneIdFromServer);
  console.log('productLineIdFromServer', productLineIdFromServer)
  console.log('categoryIdFromServer', categoryIdFromServer)
  console.log('buttonIdFromServer', buttonIdFromServer)

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
