// Remove the hardcoded data from your JavaScript file
let zonesData = []; // array to store zone data fetched from API
let productLinesData = []; // array to store product line data fetched from API
let categoriesData = []; // array to store category data fetched from API
let buttonsData = []; // array to store button data fetched from API

// Fetch data from the API endpoint and populate the respective arrays
fetch("/api/cascading_dropdown_data")
  .then((response) => response.json()) // parse the JSON response
  .then((data) => {
    zonesData = data.zones;
    productLinesData = data.productLines;
    categoriesData = data.categories;
    buttonsData = data.buttons;

    // Call the function that initializes the cascading dropdowns
    // after the data has been fetched
    initCascadingDropdowns();
    resetDropdowns();
    preselectValues();
  });

// Function to filter data based on key-value pair
function filterData(data, key, value) {
  return data.filter((item) => item[key] === value);
}

// Function to populate a dropdown with data
function populateDropdown(dropdown, data, selectedId) {
  dropdown.innerHTML = ""; // clear the dropdown options first

  // Create a default option and set its attributes
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.selected = true;
  defaultOption.disabled = true;
  defaultOption.hidden = true;
  defaultOption.textContent = "Selectionner une option";
  dropdown.appendChild(defaultOption);

  // Iterate over the data and create options for each item
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

// Function to initialize the cascading dropdowns
function initCascadingDropdowns() {
  const zone = document.getElementById("zone");
  const productline = document.getElementById("productline");
  const category = document.getElementById("category");

  if (zone && productline && category) {
    zone.addEventListener("change", handleZoneChange);
    productline.addEventListener("change", handleProductLineChange);
    category.addEventListener("change", handleCategoryChange);
    populateDropdown(zone, zonesData);
    resetDropdowns();
  }
}

// Event handler for Zone dropdown change event
function handleZoneChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredProductLines = filterData(
    productLinesData,
    "zone_id",
    selectedValue
  );
  populateDropdown(
    document.getElementById("productline"),
    filteredProductLines
  );
}

// Event handler for Product Line dropdown change event
function handleProductLineChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredCategories = filterData(
    categoriesData,
    "product_line_id",
    selectedValue
  );
  populateDropdown(document.getElementById("category"), filteredCategories);
}

// Event handler for Category dropdown change event
function handleCategoryChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredButtons = filterData(buttonsData, "category_id", selectedValue);
  populateDropdown(document.getElementById("upload_button"), filteredButtons);
}

// Function to reset all dropdowns to their default state
function resetDropdowns() {
  const zone = document.getElementById("zone");
  const productline = document.getElementById("productline");
  const category = document.getElementById("category");
  const button = document.getElementById("upload_button");

  if (zone) zone.selectedIndex = 0;
  if (productline) productline.selectedIndex = 0;
  if (category) category.selectedIndex = 0;
  if (button) button.selectedIndex = 0;
}

// Event listener for Turbo-Links page load event
document.addEventListener("turbo:load", () => {
  // Fetch data from the API endpoint on page load
  fetch("/api/cascading_dropdown_data")
    .then((response) => response.json()) // parse the JSON response
    .then((data) => {
      zonesData = data.zones;
      productLinesData = data.productLines;
      categoriesData = data.categories;
      buttonsData = data.buttons;

      // Initialize the cascading dropdowns and reset them on page load
      initCascadingDropdowns();
      resetDropdowns();
      preselectValues();
    });
});

// Function to preselect values in the dropdowns based on server-side data
function preselectValues() {
  const zoneDropdown = document.getElementById("zone");
  const productLineDropdown = document.getElementById("productline");
  const categoryDropdown = document.getElementById("category");
  const buttonDropdown = document.getElementById("upload_button"); // get the button dropdown element

  // Preselect zone based on server-side data
  if (zoneIdFromServer && zoneDropdown) {
    const filteredProductLines = filterData(
      productLinesData,
      "zone_id",
      parseInt(zoneIdFromServer)
    );
    populateDropdown(zoneDropdown, zonesData, zoneIdFromServer);
    if (productLineDropdown) {
      populateDropdown(
        productLineDropdown,
        filteredProductLines,
        productLineIdFromServer
      );
    }
  }

  // Preselect product line based on server-side data
  if (productLineIdFromServer && productLineDropdown) {
    const filteredCategories = filterData(
      categoriesData,
      "product_line_id",
      parseInt(productLineIdFromServer)
    );
    if (categoryDropdown) {
      populateDropdown(
        categoryDropdown,
        filteredCategories,
        categoryIdFromServer
      );
    }
  }

  // Preselect category based on server-side data
  if (categoryIdFromServer && categoryDropdown) {
    const filteredButtons = filterData(
      buttonsData,
      "category_id",
      parseInt(categoryIdFromServer)
    );
    if (buttonDropdown) {
      populateDropdown(buttonDropdown, filteredButtons, buttonIdFromServer);
    }
  }

  // Preselect button based on server-side data
  if (buttonIdFromServer && buttonDropdown) {
    buttonDropdown.value = buttonIdFromServer;
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
    let uploadId = actionUrl.split("/").pop();

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
