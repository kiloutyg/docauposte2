// Declaring variable 
let usersData;
// This line declares a variable named usersData without assigning it a value.

// This code is a JavaScript program that includes various functions to populate, 
// reset, and create cascading dropdowns based on user data.

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
  defaultOption.textContent = "Selectionner un Valideur";
  dropdown.appendChild(defaultOption);

  // Add each item in the data array as an option in the dropdown
  data.forEach((item) => {
    const option = document.createElement("option");
    option.value = item.id;
    option.textContent = item.username;

    // If this option should be selected, set the 'selected' attribute
    if (item.id === selectedId) {
      option.selected = true;
    }

    dropdown.appendChild(option);
  });
}

// The populateDropdown function takes three parameters: 
// dropdown (an HTMLElement representing the dropdown element), 
// data (an array of data used to populate the dropdown options), 
// and selectedId (a string representing the id of the option to be selected by default). 
// It clears the dropdown element, creates a default "Select" option, adds it to the dropdown, 
// and then iterates over each item in the data array to create options and add them to the dropdown. 
// If an item's id matches the selectedId, the option is set as selected.

// // Event listener to fetch user data and initialize cascading dropdowns
document.addEventListener("turbo:load", function () {
  fetch("/api/user_data")
    .then((response) => response.json())
    .then((data) => {
      usersData = data.users;

      // Call the function that initializes the cascading dropdowns
      // after the data has been fetched
      initCascadingDropdowns();
      resetDropdowns();

    })
    .catch((error) => {
      console.log('Error fetching data:', error);
    });
});

// The code also includes an event listener attached to the document's 
// "turbo:load" event. When the event is triggered, the code fetches user data from 
// "/api/user_data", stores it in the usersData variable, 
// and then calls the initCascadingDropdowns and resetDropdowns functions.

/**
 * Initializes the cascading dropdowns
 */
function initCascadingDropdowns() {
  const user = document.getElementById("validator_user0");
  if (user) {
    // Populate the user dropdown with data
    populateDropdown(user, usersData);

    // Reset dropdowns
    resetDropdowns();
  }
}

// The initCascadingDropdowns function gets the element with the id "validator_user0" 
// and populates it using the populateDropdown function. 
// It then calls the resetDropdowns function.

/**
* Resets the dropdown to its default value
*/
function resetDropdowns() {
  const user = document.getElementById("user");
  if (user) {
    user.selectedIndex = 0;
  }
}

// The resetDropdowns function gets the element with the id "user" and sets its selectedIndex to 0, 
// effectively resetting the dropdown to its default value.

document.addEventListener('turbo:load', function () {
  // Get the element with the id 'validator_user'
  const validatorUser = document.getElementById('validator_user');

  // Check if the element exists
  if (validatorUser) {
    // Change the id and name attributes to match the desired structure
    validatorUser.id = 'validator_user0';
    validatorUser.name = 'validator_user0';
    // Add an event listener to the element when it changes
    validatorUser.addEventListener('change', function (e) {
      // Remove all the select elements except the first one
      const userSelects = Array.from(document.querySelectorAll('.userSelect'));
      userSelects.forEach((select, index) => {
        if (index !== 0) select.remove();
      });

      // Call the createNewSelect function
      createNewSelect(e.target.value, e.target.id);
    });
  }
});

// There is another event listener attached to the document's 
// "turbo:load" event. This event listener checks if the element with the id "validator_user" exists. 
// If it does, it changes the id and name attributes to match the desired structure and attaches 
// an event listener for the "change" event. When the "change" event is triggered, it removes all select 
// elements except the first one, 
// and then calls the createNewSelect function.

function createNewSelect(selectedValue, selectId) {
  // Check if the selected value is not empty
  if (selectedValue !== '') {
    // Get all the selected options from other select elements
    var lastSelectId = document.querySelectorAll('.userSelect:last-child')[0].id;

    // If it wasn't the last select element that was changed, remove all select elements after it
    if (selectId !== lastSelectId) {
      const userSelects = Array.from(document.querySelectorAll('.userSelect'));
      const changedSelectIndex = userSelects.findIndex(select => select.id === selectId);
      userSelects.forEach((select, index) => {
        if (index > changedSelectIndex) select.remove();
      });
    }

    // If there is room to create more select elements
    if (document.querySelectorAll('.userSelect').length < usersData.length) {
      createSelectElement();
    }
  }
}

// The createNewSelect function takes two parameters: selectedValue 
// (a string representing the selected value) and selectId 
// (a string representing the id of the select element that triggered the change event). 
// It checks if the selected value is not empty and removes any select elements after the one 
// that triggered the change event. If there is room to create more select elements 
// (up to the number of users in the usersData array), it calls the createSelectElement function.

function createSelectElement() {
  // Create a new select element
  var newSelect = document.createElement('select');

  // Add classes to the new select element
  newSelect.classList.add('mt-2', 'mb-2', 'form-select', 'userSelect');

  // Generate a unique id for the new select element
  var newSelectId = "validator_user" + document.querySelectorAll('.userSelect').length;

  // Set the id and name attributes of the new select element
  newSelect.id = newSelectId;
  newSelect.name = newSelectId;

  // Create a default option for the new select element
  const defaultOption = document.createElement('option');
  defaultOption.value = '';
  defaultOption.textContent = 'Selectionner un Valideur';

  // Append the default option to the new select element
  newSelect.appendChild(defaultOption);

  // Get all the selected options from other select elements
  var selectedOptions = Array.from(document.querySelectorAll('.userSelect')).map(sel => parseInt(sel.value));

  // Iterate over each user in the usersData array
  usersData.forEach(function (user) {
    // Check if the user has not been selected yet
    if (!selectedOptions.includes(user.id)) {
      // Create a new option for the new select element
      const newOption = document.createElement('option');
      newOption.value = user.id;
      newOption.textContent = user.username;

      // Append the new option to the new select element
      newSelect.appendChild(newOption);
    }
  });

  // Add the new select element to the container with the id 'usersContainer'
  document.getElementById('usersContainer').appendChild(newSelect);

  // Add an event listener to the new select element when it changes
  newSelect.addEventListener('change', function (e) {
    // Call the createNewSelect function and pass in the value of the changed element
    createNewSelect(e.target.value, e.target.id);
  });
}


// The createSelectElement function creates a new select element, 
// adds classes and unique id and name attributes to it, 
// and creates a default option. It then iterates over each user in the usersData array, 
// checks if the user has not been selected yet, creates a new option for the select element,
// and appends it. The new select element is appended to a container with the id "usersContainer", 
// and an event listener is attached to it for the "change" event. When the "change" event is triggered, 
// it calls the createNewSelect function.

// Overall, this code handles the population, reset, and creation of cascading dropdowns based on user data.