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
    incidentsCategoriesData = data.incidentCategories;

    // Call the function that initializes the cascading dropdowns
    // after the data has been fetched
    initCascadingDropdowns();
    resetDropdowns();
    preselectValues();
  });

// Existing filterData and populateDropdown functions here...

function initCascadingDropdowns() {
  const zone = document.getElementById("incidents_zone");
  const productline = document.getElementById("incidents_productline");
  const incidentCategory = document.getElementById(
    "incidents_incidentCategory"
  );

  if (zone && productline && incidentCategory) {
    zone.addEventListener("change", handleIncidentsZoneChange);
    populateDropdown(incidentCategory, incidentsCategoriesData); // Populate 'category' dropdown here
  } else {
    console.error("One or more elements not found");
  }
  populateDropdown(zone, incidentsZonesData);
  resetDropdowns();
}

function handleIncidentsZoneChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredProductLines = filterData(
    incidentsProductLinesData,
    "zone_id",
    selectedValue
  );
  populateDropdown(
    document.getElementById("incidents_productline"),
    filteredProductLines
  );
}

function resetDropdowns() {
  const zone = document.getElementById("incidents_zone");
  const productline = document.getElementById("incidents_productline");
  const incidentCategory = document.getElementById(
    "incidents_incidentCategory"
  );

  if (zone) zone.selectedIndex = 0;
  if (productline) productline.selectedIndex = 0;
  if (incidentCategory) incidentCategory.selectedIndex = 0;
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
  const productLineDropdown = document.getElementById("incidents_productline");

  // Preselect zone
  if (zoneIdFromServer && zoneDropdown) {
    const filteredProductLines = filterData(
      incidentsProductLinesData,
      "zone_id",
      parseInt(zoneIdFromServer)
    );
    populateDropdown(zoneDropdown, incidentsZonesData, zoneIdFromServer);
    populateDropdown(
      productLineDropdown,
      filteredProductLines,
      productLineIdFromServer
    );
  }

  // Preselect product line
  if (productLineIdFromServer && productLineDropdown) {
    productLineDropdown.value = productLineIdFromServer;
  }
}

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

document.addEventListener("turbo:load", function () {
  let createIncidentCategoryButton = document.getElementById(
    "create_incident_incidentCategory"
  );

  if (createIncidentCategoryButton) {
    createIncidentCategoryButton.addEventListener("click", function (e) {
      e.preventDefault();

      let incidentCategoryName = document
        .getElementById("incident_incidentCategory_name")
        .value.trim();

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "/incident/incident_incidentCategory_creation");
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
            document.getElementById("incident_incidentCategory_name").value =
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
          incident_incidentCategory_name: incidentCategoryName,
        })
      );
    });
  }
});
