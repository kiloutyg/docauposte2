// docauposte2/assets/js/cascading-dropdowns.js

import { getEntityData } from './server-variable.js';
import { filterData, populateDropdown, resetDropdowns, preselectValues } from './dropdown-utils.js';

let zonesData = null;
let productLinesData = null;
let categoriesData = null;
let buttonsData = null;

/**
 * Initializes the cascading dropdown system when the Turbo page loads.
 * This event listener fetches entity data from the server and sets up the dropdown
 * functionality for zone, product line, category, and button selections.
 * 
 * @function
 * @listens turbo:load - Turbo framework page load event
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Fetches entity data (zones, product lines, categories, buttons) from the server
 * 2. Assigns the fetched data to global variables for use throughout the application
 * 3. Initializes the cascading dropdown functionality
 * 4. Resets all dropdown elements to their default state
 * 5. Preselects dropdown values if server-provided IDs are available
 * 6. Handles any errors that occur during data fetching (currently silent)
 * 
 * @requires getEntityData - Function that fetches entity data from the server
 * @requires initCascadingDropdowns - Function that sets up dropdown event listeners
 * @requires resetDropdowns - Function that resets dropdown elements to default state
 * @requires preselectDropdownValues - Function that preselects values based on server data
 */
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




/**
 * Initializes cascading dropdown functionality for zone, product line, category, and button selections.
 * Sets up event listeners to handle dependent dropdown filtering and population based on user selections.
 * Each dropdown selection filters and populates the next dropdown in the hierarchy while resetting
 * subsequent dependent dropdowns.
 * 
 * @function initCascadingDropdowns
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves DOM elements for all four dropdown menus
 * 2. Populates the zone dropdown with initial data
 * 3. Sets up change event listeners for each dropdown to handle cascading behavior:
 *    - Zone selection filters and populates product lines
 *    - Product line selection filters and populates categories
 *    - Category selection filters and populates buttons
 * 4. Resets dependent dropdowns when parent selections change
 * 
 * @requires zonesData - Global variable containing zones data array
 * @requires productLinesData - Global variable containing product lines data array
 * @requires categoriesData - Global variable containing categories data array
 * @requires buttonsData - Global variable containing buttons data array
 */
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





/**
 * Preselects dropdown values based on server-provided IDs and populates dependent dropdowns
 * with filtered data. This function handles the cascading relationship between zone, product line,
 * category, and button dropdowns by filtering and populating each dropdown based on the
 * previously selected values from the server.
 * 
 * @function preselectDropdownValues
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves DOM elements for all four dropdown menus
 * 2. Preselects the zone dropdown using server-provided zoneIdFromServer
 * 3. If a zone is preselected, filters and populates product lines for that zone
 * 4. If a product line is preselected, filters and populates categories for that product line
 * 5. If a category is preselected, filters and populates buttons for that category
 * 
 * @requires zoneIdFromServer - Global variable containing the zone ID from server
 * @requires productLineIdFromServer - Global variable containing the product line ID from server
 * @requires categoryIdFromServer - Global variable containing the category ID from server
 * @requires buttonIdFromServer - Global variable containing the button ID from server
 * @requires zonesData - Global variable containing zones data array
 * @requires productLinesData - Global variable containing product lines data array
 * @requires categoriesData - Global variable containing categories data array
 * @requires buttonsData - Global variable containing buttons data array
 */
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
      defaultText: 'Choisissez d\'abord une Zone',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }

  if (productLineIdFromServer && categoryDropdown) {
    const filteredCategories = filterData(categoriesData, "product_line_id", parseInt(productLineIdFromServer));
    populateDropdown(categoryDropdown, filteredCategories, {
      selectedId: categoryIdFromServer,
      defaultText: 'Choisissez d\'abord une Ligne',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }

  if (categoryIdFromServer && buttonDropdown) {
    const filteredButtons = filterData(buttonsData, "category_id", parseInt(categoryIdFromServer));
    populateDropdown(buttonDropdown, filteredButtons, {
      selectedId: buttonIdFromServer,
      defaultText: 'Choisissez d\'abord une Catégorie',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }
}
