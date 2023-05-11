// // Add this at the end of your JavaScript code
// document
//   .querySelector("#modifyForm")
//   .addEventListener("submit", function (event) {
//     event.preventDefault();

//     // Get the file input element
//     let fileInput = document.querySelector("#file");

//     if (fileInput.files.length > 0) {
//       // A file was selected
//       let file = fileInput.files[0];

//       let formData = new FormData();

//       // Add the file to formData
//       formData.append("file", file);

//       // Rest of your code...
//     } else {
//       // No file was selected
//       console.error("No file was selected");
//     }

//     // Create a new FormData object
//     let formData = new FormData();

//     // Get the dropdown elements
//     let zoneDropdown = document.getElementById("zone");
//     let productlineDropdown = document.getElementById("productline");
//     let categoryDropdown = document.getElementById("category");
//     let buttonDropdown = document.getElementById("button");

//     // Get the selected values
//     let zoneValue = zoneDropdown.options[zoneDropdown.selectedIndex].value;
//     let productlineValue =
//       productlineDropdown.options[productlineDropdown.selectedIndex].value;
//     let categoryValue =
//       categoryDropdown.options[categoryDropdown.selectedIndex].value;
//     let buttonValue =
//       buttonDropdown.options[buttonDropdown.selectedIndex].value;

//     // Add the values to formData
//     formData.append("zone", zoneValue);
//     formData.append("productline", productlineValue);
//     formData.append("category", categoryValue);
//     formData.append("button", buttonValue);

//     // Log the formData to the console to inspect it
//     for (let [key, value] of formData.entries()) {
//       console.log(key, value);
//     }

//     // Send formData to server...
//     // TODO: Add your fetch or AJAX call here
//     fetch("/modify/{uploadId}", {
//       method: "POST",
//       body: formData,
//     })
//       .then((response) => response.json())
//       .then((data) => console.log(data))
//       .catch((error) => {
//         console.error("Error:", error);
//       });
//   });
