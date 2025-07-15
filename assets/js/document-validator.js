// docauposte2/assets/js/document-validator.js

import { getUserData, getSettingsData } from './server-variable.js';
import { populateDropdown, resetDropdowns } from './dropdown-utils.js';

// Global variables
let usersData = [];
let validatorSelectCount = - 1;
let minimumValidatorsRequired = 1;


/**
 * Event listener that initializes the document validator functionality when the page is loaded via Turbo.
 * This ensures that the validator dropdowns and form validation are properly set up after Turbo navigation.
 * 
 * @listens turbo:load - Turbo framework event fired when a page is loaded or navigated to
 */
document.addEventListener('turbo:load', () => {
  initialize();
});



/**
 * Initializes the document validator functionality by fetching required data and setting up the UI components.
 * This function orchestrates the initialization process by retrieving user and settings data,
 * then configuring validator dropdowns and form validation listeners.
 * 
 * @function initialize
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Fetches user data and settings data concurrently using Promise.all
 * 2. Sets global variables (usersData and minimumValidatorsRequired) from the fetched data
 * 3. Initializes validator dropdown functionality
 * 4. Resets the main validator dropdown to its default state
 * 5. Sets up form validation event listeners
 * 6. Handles any errors that occur during the data fetching process
 * 
 * @throws {Error} Logs errors to console if data fetching fails
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
 * Initializes all form validation event listeners for the document validator form.
 * This function sets up the necessary event handlers for various form elements including
 * approval radio buttons, modification level controls, and textarea requirement validation.
 * It orchestrates the initialization of all form validation behaviors by calling
 * specialized handler functions for different form sections.
 * 
 * @function initFormValidationListeners
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Sets up approval radio button event handlers for approbation comments
 * 2. Configures modification level radio button behaviors and validator dropdown visibility
 * 3. Initializes textarea requirement validation based on file uploads and checkbox states
 * 
 * @see {@link handleApprovalRadioButtons} - Handles approval/disapproval radio button logic
 * @see {@link handleModificationLevel} - Manages modification level radio button behaviors
 * @see {@link handleTextareaRequirements} - Sets up textarea requirement validation
 */
function initFormValidationListeners() {
  handleApprovalRadioButtons();
  handleModificationLevel();
  handleTextareaRequirements();
}






/**
 * Handles the approval radio button event listeners to dynamically set textarea requirement based on selection.
 * This function sets up event listeners for approval and disapproval radio buttons that control whether
 * the approbation comment textarea is required. When disapproval is selected, the comment becomes mandatory,
 * while approval makes the comment optional.
 * 
 * @function handleApprovalRadioButtons
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves DOM elements for disapproval radio button (danger-outlined), approval radio button (success-outlined), and approbation comment textarea
 * 2. Sets up change event listener for the disapproval radio button to make the textarea required when selected
 * 3. Sets up change event listener for the approval radio button to make the textarea optional when selected
 * 4. Only adds event listeners if the corresponding DOM elements exist to prevent errors
 * 
 * @example
 * // Called during form initialization to set up approval/disapproval logic
 * handleApprovalRadioButtons();
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
 * Handles the modification level radio button event listeners to dynamically control validator dropdown visibility and comment requirement.
 * This function sets up event listeners for modification level radio buttons that control whether
 * the validator dropdown is visible and whether the modification comment textarea is required.
 * When "minor-modification" is selected, the validator dropdown is hidden and the comment becomes optional,
 * while other modification levels show the validator dropdown and make the comment mandatory.
 * 
 * @function handleModificationLevel
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves DOM elements for the checked modification level radio button, validator dropdown accordion, and modification comment textarea
 * 2. Sets up change event listener for the modification level radio button to control UI visibility and requirements
 * 3. Hides validator dropdown and makes comment optional for minor modifications
 * 4. Shows validator dropdown and makes comment required for major modifications
 * 5. Only adds event listeners if all corresponding DOM elements exist to prevent errors
 * 
 * @example
 * // Called during form initialization to set up modification level logic
 * handleModificationLevel();
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
 * Handles the dynamic requirement validation for textarea elements based on form state changes.
 * This function sets up event listeners for file input and validator checkbox elements to dynamically
 * control whether textarea comments and validator user selection are required. The requirement state
 * changes based on user interactions such as file uploads and checkbox selections.
 * 
 * @function handleTextareaRequirements
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves DOM elements for file input (upload_file or file), textarea comments (modificationComment or validationComment), validator checkbox, and validator user select
 * 2. Sets up change event listener for the validator checkbox to control requirement state of textarea and validator select elements
 * 3. Sets up change event listener for file input to make textarea required when files are uploaded during validation cycles
 * 4. Uses the updateElementRequirement helper function to toggle the required attribute on form elements
 * 5. Only adds event listeners if the corresponding DOM elements exist to prevent errors
 * 
 * @example
 * // Called during form initialization to set up textarea requirement validation
 * handleTextareaRequirements();
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
 * Updates the required attribute of a form element based on the specified requirement state.
 * This utility function provides a safe way to toggle the required attribute on form elements,
 * ensuring that the element exists before attempting to modify its properties.
 * 
 * @function updateElementRequirement
 * @param {HTMLElement|null} element - The DOM element whose required attribute should be updated. Can be null or undefined for safe handling.
 * @param {boolean} isRequired - Whether the element should be marked as required (true) or optional (false).
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Checks if the provided element exists and is not null/undefined
 * 2. Sets the required attribute of the element to the specified boolean value
 * 3. Safely handles cases where the element parameter is null or undefined
 * 
 * @example
 * // Make a textarea element required
 * const textarea = document.querySelector('textarea[name="comment"]');
 * updateElementRequirement(textarea, true);
 * 
 * @example
 * // Make a select element optional
 * const select = document.querySelector('select[name="validator"]');
 * updateElementRequirement(select, false);
 */
function updateElementRequirement(element, isRequired) {
  if (element) {
    element.required = isRequired;
  }
}




/**
 * Initializes the validator dropdown functionality by setting up the main validator dropdown and form submission handling.
 * This function configures the primary validator dropdown with user data, sets up event listeners for dropdown changes,
 * and establishes form submission validation. It serves as the entry point for all validator dropdown-related functionality
 * in the document validator form.
 * 
 * @function initValidatorDropdowns
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves the main validator dropdown element by ID ('validator_user')
 * 2. Populates the dropdown with user data using a default selection text and custom name formatting
 * 3. Sets up a change event listener to handle validator selection changes and dynamic dropdown addition
 * 4. Retrieves the validator form element by ID ('validatorForm')
 * 5. Sets up a submit event listener to validate minimum validator requirements before form submission
 * 6. Only adds event listeners if the corresponding DOM elements exist to prevent errors
 * 
 * @requires usersData - Global array containing user data for populating dropdowns
 * @requires populateDropdown - Function from dropdown-utils.js for populating select elements
 * @requires formatUserName - Function for formatting user names in dropdown options
 * @requires handleValidatorChange - Function to handle validator dropdown change events
 * @requires handleFormSubmit - Function to handle form submission validation
 * 
 * @example
 * // Called during initialization to set up validator dropdowns
 * initValidatorDropdowns();
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
 * Handles the change event for validator dropdown selections to prevent duplicates and manage dynamic dropdown addition.
 * This function is triggered when a user selects a validator from any validator dropdown. It validates the selection
 * to prevent duplicate validator assignments and automatically adds new validator dropdowns when needed to allow
 * selection of additional validators up to the required minimum.
 * 
 * @function handleValidatorChange
 * @param {Event} event - The change event object triggered by selecting an option in a validator dropdown. The event.target property contains the select element that was changed.
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Extracts the select element from the event target to identify which dropdown was changed
 * 2. Retrieves all currently selected validator IDs to check for duplicates
 * 3. Validates the selection to prevent duplicate validator assignments by checking if the selected validator is already chosen
 * 4. Resets the dropdown to default selection and shows an alert if a duplicate is detected
 * 5. Determines if a new validator dropdown should be added based on current selections
 * 6. Automatically adds a new validator dropdown if the current dropdown has a valid selection and more validators can be added
 * 
 * @example
 * // Event listener setup for validator dropdown
 * validatorDropdown.addEventListener('change', handleValidatorChange);
 * 
 * @see {@link getSelectedValidatorIds} - Retrieves currently selected validator IDs
 * @see {@link hasDuplicateSelections} - Checks for duplicate validator selections
 * @see {@link shouldAddNewDropdown} - Determines if a new dropdown should be added
 * @see {@link addNewValidatorSelect} - Adds a new validator dropdown to the form
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
 * Creates and adds a new validator dropdown select element to the form dynamically.
 * This function generates a new validator dropdown with a unique ID and name, populates it with user data
 * while excluding already selected validators, and configures its requirement state based on the minimum
 * validators needed. The new dropdown is then appended to the validator container and set up with
 * appropriate event listeners for handling selection changes.
 * 
 * @function addNewValidatorSelect
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Increments the global validatorSelectCount to ensure unique element identification
 * 2. Retrieves the validator container element from the DOM to append the new dropdown
 * 3. Creates a new select element with dynamically generated ID and name attributes
 * 4. Retrieves currently selected validator IDs to exclude them from the new dropdown options
 * 5. Applies appropriate CSS classes for styling and functionality identification
 * 6. Populates the dropdown with user data, excluding already selected validators and using custom text formatting
 * 7. Sets the required attribute based on whether the minimum validator requirement has been met
 * 8. Attaches a change event listener to handle validator selection changes
 * 9. Appends the configured dropdown to the validator container in the DOM
 * 
 * @requires validatorSelectCount - Global counter for generating unique validator dropdown IDs
 * @requires usersData - Global array containing user data for populating the dropdown
 * @requires minimumValidatorsRequired - Global variable defining the minimum number of validators needed
 * @requires populateDropdown - Function from dropdown-utils.js for populating select elements with options
 * @requires getSelectedValidatorIds - Function to retrieve currently selected validator IDs
 * @requires updateElementRequirement - Function to set the required attribute on form elements
 * @requires handleValidatorChange - Function to handle dropdown change events
 * @requires formatUserName - Function for formatting user names in dropdown options
 * 
 * @example
 * // Called when a new validator dropdown needs to be added
 * addNewValidatorSelect();
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
 * Determines whether a new validator dropdown should be added to the form based on the current state of existing dropdowns.
 * This function checks if the last validator dropdown in the form has a selected value, which indicates that
 * a new dropdown should be created to allow selection of additional validators. It serves as a condition
 * checker for the dynamic dropdown addition functionality.
 * 
 * @function shouldAddNewDropdown
 * @returns {boolean} Returns true if a new validator dropdown should be added (when the last dropdown has a selected value), false otherwise
 * 
 * @description
 * The function performs the following operations:
 * 1. Queries all validator dropdown elements using the 'validatorUserSelect' class selector
 * 2. Identifies the last validator dropdown in the collection
 * 3. Checks if the last dropdown exists and has a non-empty selected value
 * 4. Returns true if both conditions are met, indicating a new dropdown should be added
 * 
 * @example
 * // Check if a new dropdown should be added before creating one
 * if (shouldAddNewDropdown()) {
 *   addNewValidatorSelect();
 * }
 */
function shouldAddNewDropdown() {
  const validatorSelects = document.querySelectorAll('.validatorUserSelect');
  const lastSelect = validatorSelects[validatorSelects.length - 1];
  return lastSelect && lastSelect.value !== '';
}




/**
 * Retrieves an array of currently selected validator IDs from all validator dropdown elements in the form.
 * This function queries all validator dropdown elements, extracts their selected values, converts them to integers,
 * and filters out any invalid or empty selections. It provides a clean array of validator IDs that can be used
 * for validation, duplicate checking, and form processing operations.
 * 
 * @function getSelectedValidatorIds
 * @returns {number[]} An array of integers representing the IDs of currently selected validators. Empty array if no valid selections are found.
 * 
 * @description
 * The function performs the following operations:
 * 1. Queries all validator dropdown elements using the 'validatorUserSelect' class selector
 * 2. Converts the NodeList to an array for easier manipulation
 * 3. Maps each select element to its integer value using parseInt
 * 4. Filters out any NaN values that result from empty or invalid selections
 * 5. Returns the final array of valid validator IDs
 * 
 * @example
 * // Get currently selected validator IDs for duplicate checking
 * const selectedIds = getSelectedValidatorIds();
 * // Returns: [123, 456, 789] for valid selections or [] for no selections
 */
function getSelectedValidatorIds() {
  const validatorSelects = document.querySelectorAll('.validatorUserSelect');
  return Array.from(validatorSelects)
    .map((select) => parseInt(select.value))
    .filter((value) => !isNaN(value));
}




/**
 * Checks if there are duplicate validator IDs in the provided array of selected validators.
 * This function uses a Set data structure to identify duplicates by comparing the size of the unique
 * values set against the original array length. If the sizes differ, it indicates that duplicate
 * values were removed when creating the Set, confirming the presence of duplicates.
 * 
 * @function hasDuplicateSelections
 * @param {number[]} selectedValidators - An array of validator IDs (integers) to check for duplicates. Can be empty or contain any number of validator IDs.
 * @returns {boolean} Returns true if duplicate validator IDs are found in the array, false if all validator IDs are unique or if the array is empty.
 * 
 * @description
 * The function performs the following operations:
 * 1. Creates a new Set from the selectedValidators array, which automatically removes duplicate values
 * 2. Compares the size of the unique Set against the length of the original array
 * 3. Returns true if the sizes differ (indicating duplicates were present), false otherwise
 * 
 * @example
 * // Returns true - duplicates found
 * hasDuplicateSelections([1, 2, 3, 2, 4]); // true
 * 
 * @example
 * // Returns false - no duplicates
 * hasDuplicateSelections([1, 2, 3, 4, 5]); // false
 * 
 * @example
 * // Returns false - empty array
 * hasDuplicateSelections([]); // false
 */
function hasDuplicateSelections(selectedValidators) {
  const uniqueValidators = new Set(selectedValidators);
  return uniqueValidators.size !== selectedValidators.length;
}


/**
 * Handles the form submission event to validate minimum validator requirements before allowing form submission.
 * This function is triggered when the validator form is submitted and performs validation to ensure that
 * the minimum required number of validators has been selected. If the requirement is not met, it prevents
 * form submission and displays an alert message to the user. If the requirement is satisfied, it allows
 * the form submission to proceed normally.
 * 
 * @function handleFormSubmit
 * @param {Event} event - The form submission event object. Contains the submit event triggered by the form, which can be prevented using event.preventDefault() if validation fails.
 * @returns {void} This function does not return a value
 * 
 * @description
 * The function performs the following operations:
 * 1. Retrieves the currently selected validator IDs using the getSelectedValidatorIds function
 * 2. Compares the number of selected validators against the global minimumValidatorsRequired setting
 * 3. Prevents form submission and shows an alert if insufficient validators are selected
 * 4. Allows normal form submission to proceed if the minimum requirement is met
 * 
 * @requires minimumValidatorsRequired - Global variable defining the minimum number of validators needed
 * @requires getSelectedValidatorIds - Function to retrieve currently selected validator IDs
 * 
 * @example
 * // Event listener setup for form submission
 * form.addEventListener('submit', handleFormSubmit);
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
 * Formats a username string by capitalizing the first name and converting the last name to uppercase.
 * This function expects a username in the format "firstname.lastname" and transforms it into a
 * human-readable display format with proper capitalization. The first name gets title case
 * formatting while the last name is converted to all uppercase letters.
 * 
 * @function formatUserName
 * @param {string} username - The username string to format, expected to be in "firstname.lastname" format with a period separator. Should contain exactly one period separating the first and last names.
 * @returns {string} A formatted string with the first name capitalized and last name in uppercase, separated by a space. Returns in the format "Firstname LASTNAME".
 * 
 * @description
 * The function performs the following operations:
 * 1. Splits the username string at the period (.) character to separate first and last names
 * 2. Applies capitalize function to the first name to ensure proper title case formatting
 * 3. Converts the last name to uppercase using the toUpperCase() method
 * 4. Combines the formatted names with a space separator
 * 
 * @requires capitalize - Function to capitalize the first letter of a string
 * 
 * @example
 * // Format a standard username
 * formatUserName("john.doe"); // Returns: "John DOE"
 * 
 * @example
 * // Format a username with different casing
 * formatUserName("marie.dupont"); // Returns: "Marie DUPONT"
 */
function formatUserName(username) {
  const [firstName, lastName] = username.split('.');
  return `${capitalize(firstName)} ${lastName.toUpperCase()}`;
}



/**
 * Capitalizes the first letter of a string while keeping the rest of the string unchanged.
 * This utility function takes a string input and returns a new string with the first character
 * converted to uppercase and all subsequent characters preserved in their original case.
 * It's commonly used for formatting names, titles, or other text that requires sentence case formatting.
 * 
 * @function capitalize
 * @param {string} str - The input string to capitalize. Can be any string value including empty strings. Only the first character will be affected by the capitalization.
 * @returns {string} A new string with the first character converted to uppercase and the remaining characters unchanged. Returns the original string if it's empty or if the first character is not a letter.
 * 
 * @description
 * The function performs the following operations:
 * 1. Extracts the first character of the string using charAt(0)
 * 2. Converts the first character to uppercase using toUpperCase()
 * 3. Extracts the remaining characters using slice(1)
 * 4. Concatenates the uppercase first character with the unchanged remainder
 * 
 * @example
 * // Capitalize a lowercase word
 * capitalize("hello"); // Returns: "Hello"
 * 
 * @example
 * // Capitalize a mixed case word
 * capitalize("jOHN"); // Returns: "JOHN"
 * 
 * @example
 * // Handle empty string
 * capitalize(""); // Returns: ""
 */
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}