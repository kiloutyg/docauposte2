// docauposte2/assets/js/document-validator.js

import { getUserData, getSettingsData } from './serverVariable.js';
import {
  populateDropdown,
  resetDropdowns,
} from './dropdown-utils.js';

// Global variables
let usersData = [];
let validatorSelectCount = 0;
let minimumValidatorsRequired = 1; // Default value

/**
 * Fetches required data and initializes the application.
 */
function initialize() {
  Promise.all([getUserData(), getSettingsData()])
    .then(([userData, settingsData]) => {
      usersData = userData.users;
      minimumValidatorsRequired = settingsData.validatorNumber;
      initValidatorDropdowns();
      resetDropdowns(document.getElementById('validator_user0'));
    })
    .catch((error) => {
      console.error('Error fetching data:', error);
    });
}

// Event listener for Turbo navigation load
document.addEventListener('turbo:load', initialize);

/**
 * Initializes the validator dropdowns.
 */
function initValidatorDropdowns() {
  const validatorDropdown = document.getElementById('validator_user0');

  if (validatorDropdown) {
    // Populate the first validator dropdown
    populateDropdown(validatorDropdown, usersData, {
      defaultText: 'Sélectionner un Valideur',
      textFormatter: formatUserName,
    });

    // Add change event listener
    validatorDropdown.addEventListener('change', handleValidatorChange);
  }

  // Handle form submission
  const form = document.getElementById('validatorForm');
  if (form) {
    form.addEventListener('submit', handleFormSubmit);
  }
}

/**
 * Handles changes in validator dropdowns.
 * @param {Event} event - The change event.
 */
function handleValidatorChange(event) {
  const selectElement = event.target;
  const selectedValidators = getSelectedValidatorIds();

  // Prevent duplicate selections
  if (hasDuplicateSelections(selectedValidators)) {
    alert('Ce valideur a déjà été sélectionné.');
    selectElement.selectedIndex = 0;
    return;
  }

  // Add new validator dropdown if needed
  if (shouldAddNewDropdown()) {
    addNewValidatorSelect();
  }
}

/**
 * Adds a new validator dropdown to the form.
 */
function addNewValidatorSelect() {
  validatorSelectCount++;
  const container = document.getElementById('validatorContainer');
  const newSelect = document.createElement('select');
  newSelect.id = `validator_user${validatorSelectCount}`;
  newSelect.name = `validator_user${validatorSelectCount}`;
  newSelect.classList.add('form-select', 'validatorUserSelect', 'mt-2');

  populateDropdown(newSelect, usersData, {
    defaultText: 'Sélectionner un autre Valideur',
    textFormatter: formatUserName,
  });

  newSelect.addEventListener('change', handleValidatorChange);

  container.appendChild(newSelect);
}

/**
 * Checks if new validator dropdown should be added.
 * @returns {boolean} - True if a new dropdown should be added.
 */
function shouldAddNewDropdown() {
  const validatorSelects = document.querySelectorAll('.validatorUserSelect');
  const lastSelect = validatorSelects[validatorSelects.length - 1];
  return lastSelect && lastSelect.value !== '';
}

/**
 * Retrieves the IDs of currently selected validators.
 * @returns {Array} - Array of selected validator IDs.
 */
function getSelectedValidatorIds() {
  const validatorSelects = document.querySelectorAll('.validatorUserSelect');
  return Array.from(validatorSelects)
    .map((select) => select.value)
    .filter((value) => value !== '');
}

/**
 * Checks for duplicate selections.
 * @param {Array} selectedValidators - Selected validator IDs.
 * @returns {boolean} - True if duplicates exist.
 */
function hasDuplicateSelections(selectedValidators) {
  const uniqueValidators = new Set(selectedValidators);
  return uniqueValidators.size !== selectedValidators.length;
}

/**
 * Handles the form submission event.
 * @param {Event} event - The submit event.
 */
function handleFormSubmit(event) {
  const selectedValidators = getSelectedValidatorIds();

  if (selectedValidators.length < minimumValidatorsRequired) {
    event.preventDefault();
    alert(
      `Veuillez sélectionner au moins ${minimumValidatorsRequired} valideur(s).`
    );
  } else {
    // Proceed with form submission
    console.log('Form submitted successfully.');
  }
}

/**
 * Formats the username for display.
 * @param {string} username - The username to format.
 * @returns {string} - Formatted username.
 */
function formatUserName(username) {
  const [firstName, lastName] = username.split('.');
  return `${capitalize(firstName)} ${lastName.toUpperCase()}`;
}

/**
 * Capitalizes the first letter of the given string.
 * @param {string} str - The string to capitalize.
 * @returns {string} - Capitalized string.
 */
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}