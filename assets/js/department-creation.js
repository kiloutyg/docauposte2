
// Declaring variable 
let departmentsData;

// Fetch data from the API endpoint
fetch("/api/department_data")
  .then((response) => response.json())
  .then((data) => {
    departmentsData = data.departments;

    // Call the function that initializes the cascading dropdowns
    // after the data has been fetched
    initCascadingDropdowns();
    resetDropdowns();
  }
) 
.catch((error) => {
  console.log('Error fetching data:', error);
});


function populateDropdown(dropdown, data, selectedId) {
  dropdown.innerHTML = "";
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.selected = true;
  defaultOption.disabled = true;
  defaultOption.hidden = true;
  defaultOption.textContent = "Selectionner un Service";
  dropdown.appendChild(defaultOption);

  data.forEach((item) => {
    const option = document.createElement("option");
    option.value = item.id;
    option.textContent = item.name;
    // If this option should be selected, set the 'selected' attribute
    if (item.id === selectedId) {
      option.selected = true;
    }
    dropdown.appendChild(option);
  });
}


function initCascadingDropdowns() {
  const department = document.getElementById(
    "department"
  );

  if (department) {
    populateDropdown(department, departmentsData); 
    resetDropdowns();
  }
}

function resetDropdowns() {
  const department = document.getElementById(
    "department"
  );

  if (department) department.selectedIndex = 0;
}


// Function to create a new department
document.addEventListener("turbo:load", function () {
  let createdepartmentButton = document.getElementById(
    "create_department"
  );

  if (createdepartmentButton) {
    createdepartmentButton.addEventListener("click", function (depcrea) {
      depcrea.preventDefault();

      let departmentName = document
        .getElementById("department_name")
        .value.trim();

      let xhr = new XMLHttpRequest();
      xhr.open("POST", "/department/department_creation");
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
            document.getElementById("department_name").value =
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
          department_name: departmentName,
        })
      );
    });
  }
}
);
