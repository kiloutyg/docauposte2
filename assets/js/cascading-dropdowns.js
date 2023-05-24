// Remove the hardcoded data from your JavaScript file
let zonesData = [];
let productLinesData = [];
let categoriesData = [];
let buttonsData = [];

// Fetch data from the API endpoint
fetch("/api/cascading_dropdown_data")
  .then((response) => response.json())
  .then((data) => {
    zonesData = data.zones;
    productLinesData = data.productLines;
    categoriesData = data.categories;
    buttonsData = data.buttons;

    // Call the function that initializes the cascading dropdowns
    // after the data has been fetched
    initCascadingDropdowns();
    resetDropdowns();
  });

function filterData(data, key, value) {
  return data.filter((item) => item[key] === value);
}

function populateDropdown(dropdown, data) {
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

    dropdown.appendChild(option);
  });
}

function initCascadingDropdowns() {
  const zone = document.getElementById("zone");
  const productline = document.getElementById("productline");
  const category = document.getElementById("category");

  if (zone && productline && category) {
    zone.addEventListener("change", handleZoneChange);
    productline.addEventListener("change", handleProductLineChange);
    category.addEventListener("change", handleCategoryChange);
  } else {
    console.error("One or more elements not found");
  }
  populateDropdown(zone, zonesData);
  resetDropdowns();
}

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

function handleProductLineChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredCategories = filterData(
    categoriesData,
    "product_line_id",
    selectedValue
  );
  populateDropdown(document.getElementById("category"), filteredCategories);
}

function handleCategoryChange(event) {
  const selectedValue = parseInt(event.target.value);
  const filteredButtons = filterData(buttonsData, "category_id", selectedValue);
  populateDropdown(document.getElementById("upload_button"), filteredButtons);
}

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

document.addEventListener("turbo:load", () => {
  // Fetch data from the API endpoint on page load
  fetch("/api/cascading_dropdown_data")
    .then((response) => response.json())
    .then((data) => {
      zonesData = data.zones;
      productLinesData = data.productLines;
      categoriesData = data.categories;
      buttonsData = data.buttons;

      // Initialize the cascading dropdowns and reset them on page load
      initCascadingDropdowns();
      resetDropdowns();
    });
});

document
  .querySelector("#modifyForm")
  .addEventListener("submit", function (event) {
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
    // Get the filename input
    let filenameInput = document.getElementById("upload_filename");

    // Get the selected values

    let buttonValue = parseInt(
      buttonDropdown.options[buttonDropdown.selectedIndex].value,
      10
    );

    // Get the filename value
    let filenameValue = filenameInput.value;

    // Add the values to formData

    formData.append("upload[button]", buttonValue);
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
