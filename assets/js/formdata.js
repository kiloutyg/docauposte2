// Add this at the end of your JavaScript code
document
  .querySelector("#yourForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    let formData = new FormData();

    // Get the dropdown elements
    let zoneDropdown = document.getElementById("zone");
    let productlineDropdown = document.getElementById("productline");
    let categoryDropdown = document.getElementById("category");
    let buttonDropdown = document.getElementById("button");

    // Get the selected values
    let zoneValue = zoneDropdown.options[zoneDropdown.selectedIndex].value;
    let productlineValue =
      productlineDropdown.options[productlineDropdown.selectedIndex].value;
    let categoryValue =
      categoryDropdown.options[categoryDropdown.selectedIndex].value;
    let buttonValue =
      buttonDropdown.options[buttonDropdown.selectedIndex].value;

    // Add the values to formData
    formData.append("zone", zoneValue);
    formData.append("productline", productlineValue);
    formData.append("category", categoryValue);
    formData.append("button", buttonValue);

    // Log the formData to the console to inspect it
    for (let [key, value] of formData.entries()) {
      console.log(key, value);
    }

    // Send formData to server...
    // TODO: Add your fetch or AJAX call here
  });
