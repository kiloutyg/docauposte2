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
  })
  .catch((error) => {
    console.error("Error fetching cascading dropdown data:", error);
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
    option.textContent = item.name;
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
  populateDropdown(document.getElementById("button"), filteredButtons);
}

function resetDropdowns() {
  const zone = document.getElementById("zone");
  const productline = document.getElementById("productline");
  const category = document.getElementById("category");
  const button = document.getElementById("button");

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
    })
    .catch((error) => {
      console.error("Error fetching cascading dropdown data:", error);
    });
});
