// Declaring variable 
let usersData;
// This line declares a variable named usersData without assigning it a value.


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
    defaultOption.textContent = "Selectionner un Validator";
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
  




/**
 * Initializes the cascading dropdowns
 */
function initCascadingDropdowns() {
    const user = document.getElementById("validator_user");
      if (user) {
      // Populate the user dropdown with data
      populateDropdown(user, usersData);
  
      // Reset dropdowns
      resetDropdowns();
    }
}


  /**
 * Resets the dropdown to its default value
 */
function resetDropdowns() {
    const user = document.getElementById("user");
      if (user) {
      user.selectedIndex = 0;
    }
}


document.addEventListener('turbo:load', function() {
    const validatorUser = document.getElementById('validator_user');
    if (validatorUser) {
      validatorUser.addEventListener('change', function(e) {
        createNewSelect(e.target.value);
      });
  }
});
  

function createNewSelect(selectedValue) {
// Check if the selected value is not empty
if (selectedValue !== '') {
    var newSelect = document.createElement('select');
    newSelect.classList.add('userSelect');

    // Add the default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = 'Select a user...';
    newSelect.appendChild(defaultOption);

    // Get all the already selected options in other selects
    var selectedOptions = Array.from(document.querySelectorAll('.userSelect')).map(sel => sel.value);
    
    // For each possible option in usersData
    usersData.forEach(function(user) {
    // If it hasn't been selected yet
    if (!selectedOptions.includes(user.id)) {
        // Add it as an option in the new select
        const newOption = document.createElement('option');
        newOption.value = user.id;
        newOption.textContent = user.username;
        newSelect.appendChild(newOption);
    }
    });

    // Add the new select to the container
    document.getElementById('usersContainer').appendChild(newSelect);

    // Add an event listener to the new select
    newSelect.addEventListener('change', function(e) {
    createNewSelect(e.target.value);
    });
}
}
  