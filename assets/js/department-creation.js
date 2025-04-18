
import { getEntityData } from './server-variable.js';

// Declaring variable 
let departmentsData = null;

// Function to fetch department data
// // Event listener to fetch department data and initialize cascading dropdowns
document.addEventListener("turbo:load", function () {
  getEntityData()
    .then((data) => {
      departmentsData = data.departments;

      // after the data has been fetched
      initCascadingDropdowns();
      resetDropdowns();

    })
    .catch((error) => {
      console.error('error catching server variable', error)
    });
});



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
  const validatorDepartment = document.getElementById("validator_department0");

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
  const validatorDepartment = document.getElementById("validator_department0");

  if (validatorDepartment) {
    validatorDepartment.selectedIndex = 0;
  }
}



// This code adds an event listener to the document object for the "turbo:load" event. 
// When the event is triggered, it fetches department data from the API endpoint /api/entity_data. 
// Once the data is successfully received, it assigns the departments property of the data to the departmentsData variable. 
// Then, it calls two functions: initCascadingDropdowns() and resetDropdowns(). 
// If there is an error during the fetch request, an error message will be logged to the console.

document.addEventListener('turbo:load', function () {
  // Get the element with the id 'validator_department'
  const validatorDepartment = document.getElementById('validator_department');

  // Check if the element exists
  if (validatorDepartment) {
    // Change the id and name attributes to match the desired structure
    validatorDepartment.id = 'validator_department0';
    validatorDepartment.name = 'validator_department0';
    // Add an event listener to the element when it changes
    validatorDepartment.addEventListener('change', function (e) {
      // Remove all the select elements except the first one
      const departmentSelects = Array.from(document.querySelectorAll('.departmentSelect'));
      departmentSelects.forEach((select, index) => {
        if (index !== 0) select.remove();
      });

      // Call the createNewSelect function
      createNewSelect(e.target.value, e.target.id);
    });
  }
});

function createNewSelect(selectedValue, selectId) {
  // Check if the selected value is not empty
  if (selectedValue !== '') {
    // Get all the selected options from other select elements
    var lastSelectId = document.querySelectorAll('.departmentSelect:last-child')[0].id;

    // If it wasn't the last select element that was changed, remove all select elements after it
    if (selectId !== lastSelectId) {
      const departmentSelects = Array.from(document.querySelectorAll('.departmentSelect'));
      const changedSelectIndex = departmentSelects.findIndex(select => select.id === selectId);
      departmentSelects.forEach((select, index) => {
        if (index > changedSelectIndex) select.remove();
      });
    }

    // If there is room to create more select elements
    if (document.querySelectorAll('.departmentSelect').length < departmentsData.length) {
      createSelectElement();
    }
  }
}

function createSelectElement() {
  // Create a new select element
  var newSelect = document.createElement('select');

  // Add classes to the new select element
  newSelect.classList.add('mt-2', 'mb-2', 'form-select', 'departmentSelect');

  // Generate a unique id for the new select element
  var newSelectId = "validator_department" + document.querySelectorAll('.departmentSelect').length;

  // Set the id and name attributes of the new select element
  newSelect.id = newSelectId;
  newSelect.name = newSelectId;

  // Create a default option for the new select element
  const defaultOption = document.createElement('option');
  defaultOption.value = '';
  defaultOption.textContent = 'Selectionner un autre Service';

  // Append the default option to the new select element
  newSelect.appendChild(defaultOption);

  // Get all the selected options from other select elements
  var selectedOptions = Array.from(document.querySelectorAll('.departmentSelect')).map(sel => parseInt(sel.value));

  // Iterate over each department in the departmentsData array
  departmentsData.forEach(function (department) {
    // Check if the department has not been selected yet
    if (!selectedOptions.includes(department.id)) {
      // Create a new option for the new select element
      const newOption = document.createElement('option');
      newOption.value = department.id;
      newOption.textContent = department.name;

      // Append the new option to the new select element
      newSelect.appendChild(newOption);
    }
  });

  // Add the new select element to the container with the id 'departmentsContainer'
  document.getElementById('departmentsContainer').appendChild(newSelect);

  // Add an event listener to the new select element when it changes
  newSelect.addEventListener('change', function (e) {
    // Call the createNewSelect function and pass in the value of the changed element
    createNewSelect(e.target.value, e.target.id);
  });
}