
import { getEntityData } from './server-variable.js';

// Declaring variable 
let departmentsData = null;



/**
 * Event listener that initializes department data and cascading dropdowns when the page loads.
 * This function is triggered by the Turbo framework's load event and handles the asynchronous
 * fetching of entity data, specifically department information, then initializes the UI components.
 * 
 * @function
 * @listens turbo:load - Turbo framework's page load event
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Fetches entity data from the server using getEntityData()
 * 2. Assigns the departments data to the global departmentsData variable
 * 3. Initializes cascading dropdown functionality
 * 4. Resets all dropdowns to their default state
 * 5. Handles any errors that occur during data fetching
 */
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





/**
 * Populates a dropdown element with options based on provided data array.
 * Clears the dropdown before populating it, creates a default "Select" option,
 * and adds all items from the data array as selectable options.
 * 
 * @function populateDropdown
 * @param {HTMLSelectElement} dropdown - The dropdown (select) element to populate with options
 * @param {Array<Object>} data - Array of objects containing dropdown option data. Each object should have 'id' and 'name' properties
 * @param {number|string} [selectedId] - Optional ID of the option that should be pre-selected in the dropdown
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Clears all existing options from the dropdown
 * 2. Creates and adds a default disabled option with text "Selectionner un Service"
 * 3. Iterates through the data array and creates an option element for each item
 * 4. Sets the option's value to the item's id and text content to the item's name
 * 5. If selectedId matches an item's id, marks that option as selected
 * 6. Appends each created option to the dropdown element
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





/**
 * Initializes cascading dropdown functionality for department selection elements.
 * This function locates department dropdown elements by their IDs, populates them with
 * department data, and resets them to their default state. It handles both the main
 * department dropdown and the validator department dropdown.
 * 
 * @function initCascadingDropdowns
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Searches for the main department dropdown element with ID "department"
 * 2. If found, populates it with departmentsData and resets it
 * 3. Searches for the validator department dropdown element with ID "validator_department0"
 * 4. If found, populates it with departmentsData and resets it
 * 5. Uses the global departmentsData variable as the data source for both dropdowns
 * 
 * @requires departmentsData - Global variable containing department data array
 * @requires populateDropdown - Function to populate dropdown with options
 * @requires resetDropdowns - Function to reset dropdowns to default state
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





/**
 * Resets department dropdown elements to their default selected state.
 * This function locates specific dropdown elements by their IDs and sets their
 * selectedIndex to 0, effectively selecting the first option (typically the default/placeholder option).
 * 
 * @function resetDropdowns
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Searches for the main department dropdown element with ID "department"
 * 2. If found, resets its selectedIndex to 0 (first option)
 * 3. Searches for the validator department dropdown element with ID "validator_department0"
 * 4. If found, resets its selectedIndex to 0 (first option)
 * 5. Safely handles cases where either dropdown element might not exist in the DOM
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






/**
 * Event listener that initializes dynamic department selection functionality when the page loads.
 * This function is triggered by the Turbo framework's load event and sets up cascading dropdown
 * behavior for validator department selection. It modifies the initial validator department element
 * and establishes event handling for dynamic select element creation and management.
 * 
 * @function
 * @listens turbo:load - Turbo framework's page load event
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Locates the validator department element with ID "validator_department"
 * 2. Renames the element to "validator_department0" for consistent naming convention
 * 3. Attaches a change event listener that manages cascading dropdown behavior
 * 4. When a selection is made, removes all subsequent department select elements except the first
 * 5. Triggers creation of new select elements based on the current selection
 * 
 * @requires createNewSelect - Function to create new department select elements
 * @requires departmentSelects - CSS class selector for department select elements
 */
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



/**
 * Manages the creation of new department select elements in a cascading dropdown system.
 * This function handles the logic for when to create new select elements based on user selections,
 * removes select elements that come after a changed element, and ensures proper cascading behavior
 * by only creating new elements when there's a valid selection and room for more elements.
 * 
 * @function createNewSelect
 * @param {string} selectedValue - The value of the option selected in the dropdown. If empty string, no new select will be created
 * @param {string} selectId - The ID of the select element that triggered the change event. Used to determine position in the cascade
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Validates that a non-empty value was selected
 * 2. Identifies the last select element in the cascade
 * 3. If the changed element is not the last one, removes all subsequent select elements
 * 4. Checks if there's room to create more select elements (based on available departments)
 * 5. Calls createSelectElement() to add a new select element if conditions are met
 * 
 * @requires departmentsData - Global variable containing department data array
 * @requires createSelectElement - Function to create and append new select elements
 */
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

/**
 * Creates and appends a new department select element to the cascading dropdown system.
 * This function dynamically generates a new select element with available department options,
 * excluding departments that have already been selected in other dropdowns. The new element
 * is automatically configured with proper styling, unique identifiers, and event handling
 * to maintain the cascading dropdown functionality.
 * 
 * @function createSelectElement
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Creates a new HTML select element with appropriate CSS classes
 * 2. Generates a unique ID based on the current number of existing department selects
 * 3. Adds a default placeholder option for user guidance
 * 4. Populates the select with available departments, excluding already selected ones
 * 5. Appends the new select element to the 'departmentsContainer' in the DOM
 * 6. Attaches a change event listener to enable continued cascading behavior
 * 
 * @requires departmentsData - Global variable containing department data array
 * @requires createNewSelect - Function called when the new select element changes
 * @requires DOM element with ID 'departmentsContainer' - Container where new select is appended
 */
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