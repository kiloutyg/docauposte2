// docauposte2/assets/js/incident-cascading-dropdowns.js

import { getEntityData } from './server-variable.js';
import { filterData, populateDropdown, resetDropdowns, preselectValues } from './dropdown-utils.js';

let incidentZoneData = null;
let incidentProductLinesData = null;
let incidentsCategoriesData = null;

document.addEventListener("turbo:load", () => {
  getEntityData()
    .then((data) => {
      incidentZoneData = data.zones;
      incidentProductLinesData = data.productLines;
      incidentsCategoriesData = data.incidentsCategories;

      initCascadingDropdowns();
      resetDropdowns(
        document.getElementById("incident_zone"),
        document.getElementById("incident_productLine"),
        document.getElementById("incidents_incidentsCategory")
      );
      preselectDropdownValues();
    })
    .catch((error) => {
      // console.log('Error fetching entity data:', error);
    });
});

function initCascadingDropdowns() {
  const zoneDropdown = document.getElementById("incident_zone");
  const productLineDropdown = document.getElementById("incident_productLine");
  const incidentsCategoryDropdown = document.getElementById("incidents_incidentsCategory");

  if (zoneDropdown && productLineDropdown && incidentsCategoryDropdown) {
    populateDropdown(zoneDropdown, incidentZoneData, {
      defaultText: 'Sélectionner une Zone',
    });

    populateDropdown(incidentsCategoryDropdown, incidentsCategoriesData, {
      defaultText: 'Sélectionner une Catégorie d\'Incident',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });

    zoneDropdown.addEventListener("change", (event) => {
      const selectedValue = parseInt(event.target.value);
      const filteredProductLines = filterData(incidentProductLinesData, "zone_id", selectedValue);

      populateDropdown(productLineDropdown, filteredProductLines, {
        defaultText: 'Sélectionner une Ligne',
        textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
      });

      // Reset dependent dropdowns
      resetDropdowns(productLineDropdown);
    });
  }
}

function preselectDropdownValues() {
  const zoneDropdown = document.getElementById("incident_zone");
  const productLineDropdown = document.getElementById("incident_productLine");

  preselectValues([
    {
      dropdown: zoneDropdown,
      data: incidentZoneData,
      id: zoneIdFromServer,
      options: { defaultText: 'Sélectionner une Zone' },
    },
  ]);

  if (zoneIdFromServer && productLineDropdown) {
    const filteredProductLines = filterData(incidentProductLinesData, "zone_id", parseInt(zoneIdFromServer));
    populateDropdown(productLineDropdown, filteredProductLines, {
      selectedId: productLineIdFromServer,
      defaultText: 'Sélectionner une Ligne',
      textFormatter: (text) => text.split(".")[0].charAt(0).toUpperCase() + text.split(".")[0].slice(1),
    });
  }

  if (productLineIdFromServer && productLineDropdown) {
    productLineDropdown.value = productLineIdFromServer;
  }
}





document.addEventListener("turbo:load", function () {
  let createIncidentsCategoryButton = document.getElementById(
    "create_incident_incidentsCategory"
  );

  if (createIncidentsCategoryButton) {
    createIncidentsCategoryButton.addEventListener("click", function (e) {
      e.preventDefault();

      let incidentsCategoryName = document
        .getElementById("incident_incidentsCategory_name")
        .value.trim();

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "/docauposte/incident/incident_incidentsCategory_creation");
      xhr.setRequestHeader("Content-Type", "application/json");

      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
          // Parse the JSON response
          let response = JSON.parse(xhr.responseText);

          // Show the message to the user
          alert(response.message);

          // Check if the operation was successful
          if (response.success) {
            // Clear the input field after a successful submission
            document.getElementById("incident_incidentsCategory_name").value =
              "";

            // Force a reload of the page
            location.reload();
          } else {
            // Handle failure, e.g. show error message
            console.error(response.message);
          }
        } else {
          // Handle other HTTP errors
          console.error("The request failed!");
        }
      };

      xhr.onerror = function () {
        // Handle total failure of the request
        console.error("The request could not be made!");
      };

      xhr.send(
        JSON.stringify({
          incident_incidentsCategory_name: incidentsCategoryName,
        })
      );
    });
  }
});




let modifyIncidentForm = document.querySelector("#modifyIncidentForm");
if (modifyIncidentForm) {
  modifyIncidentForm.addEventListener("submit", function (event) {
    event.preventDefault();

    // Create a new FormData object
    let formData = new FormData();

    // Get the file input element
    let fileInput = document.querySelector("#incident_file");

    // Get the CSRF token
    let csrfTokenInput = document.querySelector("#incident__token");

    // Get the CSRF token value
    let csrfTokenValue = csrfTokenInput.value;

    // Add the CSRF token to formData
    formData.append("incident[_token]", csrfTokenValue);

    if (fileInput.files.length > 0) {
      // A file was selected
      let file = fileInput.files[0];

      // Add the file to formData
      formData.append("incident[file]", file);
    }

    // Get the dropdown elements

    let incidentProductLineDropdown = document.getElementById(
      "incident_productLine"
    );
    // Get the name input
    let nameInput = document.getElementById("incident_name");

    // Get the selected values
    if (incidentProductLineDropdown) {
      let productlineValue = parseInt(
        incidentProductLineDropdown.options[
          incidentProductLineDropdown.selectedIndex
        ].value,
        10
      );
      formData.append("incident[productline]", productlineValue);
    }

    // Get the name value
    let nameValue = nameInput.value;

    // Add the values to formData
    if (nameValue) {
      formData.append("incident[name]", nameValue);
    }

    // Get the incident ID from the URL
    let form = document.getElementById("modifyIncidentForm");
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
        // Handle fetch errors here...
      });
  });
}
