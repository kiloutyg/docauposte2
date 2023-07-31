// Declaring variable 
let departmentsData;

// This line declares a variable named departmentsData without assigning it a value.

/**
 * Populates a dropdown with options based on the given data and selected id
 * @param {HTMLElement} dropdown - The dropdown element to be populated
 * @param {Array} data - The array of data to populate the dropdown with
 * @param {string} selectedId - The id of the option to be selected by default
 */
function populateDropdown(dropdown, data, selectedId) {
  // Clear the dropdown before populating it
  dropdown.innerHTML = "";

  // Create a default "Select" option and add it to the dropdown
  const defaultOption = document.createElement("option");
  defaultOption.value = "";
  defaultOption.selected = true;
  defaultOption.disabled = true;
  defaultOption.hidden = true;
  defaultOption.textContent = "Selectionner un Service";
  dropdown.appendChild(defaultOption);

  // Add each item in the data array as an option in the dropdown
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

// This is a function named populateDropdown that takes three parameters: dropdown, data, and selectedId. 
// It populates the given dropdown element with options based on the provided data array. 
// It clears the dropdown before populating it, creates a default "Select" option, and adds it to the dropdown. 
// Then, for each item in the data array, it creates an option element with the item's id and name as its value and text content respectively. 
// If the item's id matches the selectedId, it sets the selected attribute of the option element.
// Finally, it appends the option element to the dropdown.

/**
 * Initializes the cascading dropdowns
 */
function initCascadingDropdowns() {
  const department = document.getElementById("department");

  if (department) {
    // Populate the department dropdown with data
    populateDropdown(department, departmentsData);

    // Reset dropdowns
    resetDropdowns();
  }
  const validatorDepartment = document.getElementById("validator_department");

  if (validatorDepartment) {
    // Populate the department dropdown with data
    populateDropdown(validatorDepartment, departmentsData);

    // Reset dropdowns
    resetDropdowns();
  }
}

// This is a function named initCascadingDropdowns that initializes the cascading dropdowns. 
// It first gets the dropdown element with the id "department". 
// If the dropdown exists, it calls the populateDropdown() function to populate the department dropdown with the departmentsData. 
// Then it calls the resetDropdowns() function.

/**
 * Resets the dropdown to its default value
 */
function resetDropdowns() {
  const department = document.getElementById("department");

  if (department) {
    department.selectedIndex = 0;
  }
  const validatorDepartment = document.getElementById("validator_department");

  if (validatorDepartment) {
    department.selectedIndex = 0;
  }
}

// This is a function named resetDropdowns that resets the dropdown with the id "department" to its default value by setting the selectedIndex property to 0.

// Function to create a new department
document.addEventListener("turbo:load", function () {
  let createdepartmentButton = document.getElementById("create_department");

  if (createdepartmentButton) {
    createdepartmentButton.addEventListener("click", function (depcrea) {
      depcrea.preventDefault();

      // Get the value of the department name input field and trim any leading/trailing whitespace
      let departmentName = document.getElementById("department_name").value.trim();

      // Create a new XMLHttpRequest object
      let xhr = new XMLHttpRequest();
      xhr.open("POST", "/department/department_creation");
      xhr.setRequestHeader("Content-Type", "application/json");

      // Set the onload event handler for the XMLHttpRequest
      xhr.onload = function () {
        if (xhr.status >= 200 && xhr.status < 300) {
          // Parse the JSON response
          let response = JSON.parse(xhr.responseText);

          // Show the message to the user
          alert(response.message);

          // Check if the operation was successful
          if (response.success) {
            // Clear the input field after a successful submission
            document.getElementById("department_name").value = "";

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

      // Set the onerror event handler for the XMLHttpRequest
      xhr.onerror = function () {
        // Handle total failure of the request
        console.error("The request could not be made!");
      };

      // Send the POST request with the department name as JSON payload
      xhr.send(JSON.stringify({
        department_name: departmentName,
      }));
    });
  }
});


// // Event listener to fetch department data and initialize cascading dropdowns
document.addEventListener("turbo:load", function () {
  fetch("/api/department_data")
    .then((response) => response.json())
    .then((data) => {
      departmentsData = data.departments;

      // Call the function that initializes the cascading dropdowns
      // after the data has been fetched
      initCascadingDropdowns();
      resetDropdowns();
      
    })
    .catch((error) => {
      console.log('Error fetching data:', error);
    });
});

// This code adds an event listener to the document object for the "turbo:load" event. 
// When the event is triggered, it fetches department data from the API endpoint /api/department_data. 
// Once the data is successfully received, it assigns the departments property of the data to the departmentsData variable. 
// Then, it calls two functions: initCascadingDropdowns() and resetDropdowns(). 
// If there is an error during the fetch request, an error message will be logged to the console.


document.addEventListener('turbo:load', function() {
  const validatorDepartment = document.getElementById('validator_department');
  if (validatorDepartment) {
    validatorDepartment.addEventListener('change', function(e) {
      createNewSelect(e.target.value);
    });
  }
});

function createNewSelect(selectedValue) {
  // Check if the selected value is not empty
  if (selectedValue !== '') {
    var newSelect = document.createElement('select');
    newSelect.classList.add('departmentSelect');

    // Add the default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = "Selectionner un Service";
    newSelect.appendChild(defaultOption);

    // Get all the already selected options in other selects
    var selectedOptions = Array.from(document.querySelectorAll('.departmentSelect')).map(sel => sel.value);
    
    // For each possible option in departmentsData
    departmentsData.forEach(function(department) {
      // If it hasn't been selected yet
      if (!selectedOptions.includes(department.id)) {
        // Add it as an option in the new select
        const newOption = document.createElement('option');
        newOption.value = department.id;
        newOption.textContent = department.name;
        newSelect.appendChild(newOption);
      }
    });

    // Add the new select to the container
    document.getElementById('departmentsContainer').appendChild(newSelect);

    // Add an event listener to the new select
    newSelect.addEventListener('change', function(e) {
      createNewSelect(e.target.value);
    });
  }
}

