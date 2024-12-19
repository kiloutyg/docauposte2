// docauposte2/assets/js/document-validator.js

import { getUserData, getSettingsData } from './server-variable.js';
import { populateDropdown, resetDropdowns } from './dropdown-utils.js';

// Global variables
let usersData = [];
let validatorSelectCount = - 1;
let minimumValidatorsRequired = 1;

/**
 * Initializes the script on Turbo navigation load.
 */
document.addEventListener('turbo:load', () => {
  initialize();
});

/**
 * Fetches required data and initializes event listeners.
 */
function initialize() {
  Promise.all([getUserData(), getSettingsData()])
    .then(([userData, settingsData]) => {
      usersData = userData.users;
      minimumValidatorsRequired = settingsData.validatorNumber;
      initValidatorDropdowns();
      resetDropdowns(document.getElementById('validator_user'));
      initFormValidationListeners();
    })
    .catch((error) => {
      console.error('Error fetching data:', error);
    });
}

/**
 * Initializes validator dropdowns and form validation listeners.
 */
function initFormValidationListeners() {
  handleApprovalRadioButtons();
  handleModificationLevel();
  handleTextareaRequirements();
}

/**
 * Handles the approval radio buttons to set the required state of the approbation comment textarea.
 */
function handleApprovalRadioButtons() {
  const radioDisapprove = document.getElementById('danger-outlined');
  const radioApprove = document.getElementById('success-outlined');
  const textareaComment = document.querySelector('textarea[name="approbationComment"]');

  if (radioDisapprove && textareaComment) {
    radioDisapprove.addEventListener('change', () => {
      if (radioDisapprove.checked) {
        textareaComment.required = true;
      }
    });
  }

  if (radioApprove && textareaComment) {
    radioApprove.addEventListener('change', () => {
      if (radioApprove.checked) {
        textareaComment.required = false;
      }
    });
  }
}

/**
 * Handles the modification level radio buttons to show/hide validator dropdown and set textarea requirements.
 */
function handleModificationLevel() {
  const modificationLevel = document.querySelector('input[name="modificationLevel"]:checked');
  const validatorNeededDropdown = document.getElementById('accordionValidator');
  const textareaComment = document.querySelector('textarea[name="modificationComment"]');

  if (modificationLevel && validatorNeededDropdown && textareaComment) {
    modificationLevel.addEventListener('change', () => {
      if (modificationLevel.value === 'minor-modification') {
        validatorNeededDropdown.hidden = true;
        textareaComment.required = false;
      } else {
        validatorNeededDropdown.hidden = false;
        textareaComment.required = true;
      }
    });
  }
}

/**
 * Updates the requirement of textarea and select elements based on file input and checkbox states.
 */
function handleTextareaRequirements() {
  const fileInput = document.getElementById('upload_file') || document.getElementById('file');
  const textareaComment = document.querySelector('textarea[name="modificationComment"]') || document.querySelector('textarea[name="validationComment"]');
  const validatorCheckbox = document.getElementById('validatorRequired');
  const validatorUserSelect = document.querySelector('select[name="validator_user"]');

  if (validatorCheckbox) {
    validatorCheckbox.addEventListener('change', () => {
      updateElementRequirement(textareaComment, validatorCheckbox.checked);
      updateElementRequirement(validatorUserSelect, validatorCheckbox.checked);
    });
  }

  if (fileInput) {
    fileInput.addEventListener('change', () => {
      const isValidationCycle = document.querySelector('div[name="validation_cycle"]') !== null;
      if (isValidationCycle && textareaComment) {
        updateElementRequirement(textareaComment, fileInput.files.length > 0);
      }
    });
  }
}

/**
 * Updates the 'required' attribute of an element based on a condition.
 * @param {HTMLElement} element - The element to update.
 * @param {boolean} isRequired - Whether the element should be required.
 */
function updateElementRequirement(element, isRequired) {
  if (element) {
    element.required = isRequired;
  }
}

/**
 * Initializes the validator dropdowns.
 */
function initValidatorDropdowns() {
  const validatorDropdown = document.getElementById('validator_user');

  if (validatorDropdown) {
    // Populate the first validator dropdown
    populateDropdown(validatorDropdown, usersData, {
      defaultText: 'Sélectionner un Valideur',
      textFormatter: formatUserName,
    });

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
  const selectedValidators = getSelectedValidatorIds();
  newSelect.id = `validator_user${validatorSelectCount}`;
  newSelect.name = `validator_user${validatorSelectCount}`;
  newSelect.classList.add('form-select', 'validatorUserSelect', 'mt-2');

  populateDropdown(newSelect, usersData, {
    defaultText: 'Sélectionner un autre Valideur',
    excludeValues: selectedValidators,
    textFormatter: formatUserName,
  });

  if (selectedValidators.length < minimumValidatorsRequired) {
    updateElementRequirement(newSelect, true);
  } else {
    updateElementRequirement(newSelect, false)
  }

  newSelect.addEventListener('change', handleValidatorChange);

  container.appendChild(newSelect);

}

/**
 * Checks if a new validator dropdown should be added.
 * @returns {boolean} - True if a new dropdown should be added.
 */
function shouldAddNewDropdown() {
  const validatorSelects = document.querySelectorAll('.validatorUserSelect');
  const lastSelect = validatorSelects[validatorSelects.length - 1];
  return lastSelect && lastSelect.value !== '';
}

/**
 * Retrieves the IDs of currently selected validators as integers.
 * @returns {Array} - Array of selected validator IDs.
 */
function getSelectedValidatorIds() {
  const validatorSelects = document.querySelectorAll('.validatorUserSelect');
  return Array.from(validatorSelects)
    .map((select) => parseInt(select.value))
    .filter((value) => !isNaN(value));
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
    alert(`Veuillez sélectionner au moins ${minimumValidatorsRequired} valideur(s).`);
  } else {
    // Proceed with form submission or additional validation
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