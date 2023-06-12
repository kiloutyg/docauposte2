// Remove the hardcoded data from your JavaScript file
let incidentsZonesData = [];
let incidentsProductLinesData = [];
let incidentsCategoriesData = [];

// Fetch data from the API endpoint
fetch("/api/incidents_cascading_dropdown_data")
  .then((response) => response.json())
  .then((data) => {
    incidentsZonesData = data.zones;
    incidentsProductLinesData = data.productLines;
    incidentsCategoriesData = data.incidentsCategories;

    // Call the function that initializes the cascading dropdowns
    // after the data has been fetched
    initCascadingDropdowns();
    resetDropdowns();
    preselectValues();
  });

// Existing filterData and populateDropdown functions here...
function filterData(data, key, value) {
  return data.filter((item) => item[key] === value);
}

function populateDropdown(dropdown, data, selectedId) {
  dropdown.innerHTML = "";
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.selected = true;
  defaultOption.disabled = true;
  defaultOption.hidden = true;
  defaultOption.textContent = "Selectionner une option";
  dropdown.appendChild(defaultOption);

  data.forEach((item) => {
    const option = document.createElement("option");
    option.value = item.id;

    // Split the item name by '.' and capitalize the first word
    let nameParts = item.name.split(".");
    if (nameParts.length > 0) {
      nameParts[0] =
        nameParts[0].charAt(0).toUpperCase() + nameParts[0].slice(1);
    }
    option.textContent = nameParts[0]; // Use only the first part after the split.

    // If this option should be selected, set the 'selected' attribute
    if (item.id === selectedId) {
      option.selected = true;
    }
    dropdown.appendChild(option);
  });
}

function initCascadingDropdowns() {
  const zone = document.getElementById("incidents_zone");
  const productline = document.getElementById("incident_productline");
  const incidentsCategory = document.getElementById(
    "incidents_incidentsCategory"
  );

  if (zone && productline && incidentsCategory) {
    zone.addEventListener("change", handleIncidentsZoneChange);
    populateDropdown(incidentsCategory, incidentsCategoriesData); // Populate 'category' dropdown here
    populateDropdown(zone, incidentsZonesData);
    resetDropdowns();
  }
}

function handleIncidentsZoneChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredProductLines = filterData(
    incidentsProductLinesData,
    "zone_id",
    selectedValue
  );
  populateDropdown(
    document.getElementById("incident_productline"),
    filteredProductLines
  );
}

function resetDropdowns() {
  const zone = document.getElementById("incidents_zone");
  const productline = document.getElementById("incident_productline");
  const incidentsCategory = document.getElementById(
    "incidents_incidentsCategory"
  );

  if (zone) zone.selectedIndex = 0;
  if (productline) productline.selectedIndex = 0;
  if (incidentsCategory) incidentsCategory.selectedIndex = 0;
}

// Existing turbo:load event listener and preselectValues function here...

document.addEventListener("turbo:load", () => {
  // Fetch data from the API endpoint on page load
  fetch("/api/incidents_cascading_dropdown_data")
    .then((response) => response.json())
    .then((data) => {
      incidentsZonesData = data.zones;
      incidentsProductLinesData = data.productLines;

      // Initialize the cascading dropdowns and reset them on page load
      initCascadingDropdowns();
      resetDropdowns();
      preselectValues();
    });
});

function preselectValues() {
  const zoneDropdown = document.getElementById("incidents_zone");
  const productLineDropdown = document.getElementById("incident_productline");

  // Preselect zone
  if (zoneDropdown && zoneIdFromServer) {
    const filteredProductLines = filterData(
      incidentsProductLinesData,
      "zone_id",
      parseInt(zoneIdFromServer)
    );
    populateDropdown(zoneDropdown, incidentsZonesData, zoneIdFromServer);
    if (productLineDropdown) {
      populateDropdown(
        productLineDropdown,
        filteredProductLines,
        productLineIdFromServer
      );
    }
  }

  // Preselect product line
  if (productLineDropdown && productLineIdFromServer) {
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
      xhr.open("POST", "/incident/incident_incidentsCategory_creation");
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

    let productLineDropdown = document.getElementById("incident_productline");
    // Get the name input
    let nameInput = document.getElementById("incident_name");

    // Get the selected values
    if (productLineDropdown) {
      let productlineValue = parseInt(
        productLineDropdown.options[productLineDropdown.selectedIndex].value,
        10
      );
      formData.append("incidents[productline]", productlineValue);
    }

    // Get the name value
    let nameValue = nameInput.value;

    // Add the values to formData
    if (nameValue) {
      formData.append("incidents[name]", nameValue);
    }

    // Get the incident ID from the URL
    let form = document.getElementById("modifyIncidentForm");
    let actionUrl = form.getAttribute("action");
    let incidentId = actionUrl.split("/").pop();

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
