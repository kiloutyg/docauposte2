// docauposte2/assets/js/dropdown-utils.js

/**
 * Filters data based on a key-value pair.
 * @param {Array} data - The data array to filter.
 * @param {string} key - The key to filter by.
 * @param {any} value - The value to match.
 * @returns {Array} - The filtered data array.
 */
export function filterData(data, key, value) {
    return data.filter((item) => item[key] === value);
  }
  
  /**
   * Populates a dropdown with options based on provided data.
   * @param {HTMLSelectElement} dropdown - The dropdown element to populate.
   * @param {Array} data - The data array to use for options.
   * @param {Object} options - Additional options.
   * @param {string} [options.selectedId] - The ID of the option to preselect.
   * @param {string} [options.defaultText] - The default option text.
   * @param {string} [options.valueKey] - The key for the option value.
   * @param {string} [options.textKey] - The key for the option text.
   * @param {Function} [options.textFormatter] - A function to format the option text.
   */
  export function populateDropdown(dropdown, data, options = {}) {
    const {
      selectedId = null,
      defaultText = 'Select an option',
      valueKey = 'id',
      textKey = 'name',
      textFormatter = null,
    } = options;
  
    // Clear existing options
    dropdown.innerHTML = '';
  
    // Create and append the default option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.selected = true;
    defaultOption.disabled = true;
    defaultOption.hidden = true;
    defaultOption.textContent = defaultText;
    dropdown.appendChild(defaultOption);
  
    // Populate the dropdown with data
    data.forEach((item) => {
      const option = document.createElement('option');
      option.value = item[valueKey];
  
      let text = item[textKey];
      if (textFormatter) {
        text = textFormatter(text);
      }
  
      option.textContent = text;
  
      if (item[valueKey] === selectedId) {
        option.selected = true;
      }
  
      dropdown.appendChild(option);
    });
  }
  
  /**
   * Resets the selected index of the provided dropdowns.
   * @param  {...HTMLSelectElement} dropdowns - The dropdown elements to reset.
   */
  export function resetDropdowns(...dropdowns) {
    dropdowns.forEach((dropdown) => {
      if (dropdown) dropdown.selectedIndex = 0;
    });
  }
  
  /**
   * Preselects values in dropdowns based on provided IDs.
   * @param {Array} dropdowns - An array of dropdown configurations.
   * Each configuration is an object with `dropdown`, `data`, `id`, and `options` properties.
   */
  export function preselectValues(dropdowns) {
    dropdowns.forEach(({ dropdown, data, id, options = {} }) => {
      if (dropdown && data && id != null) {
        populateDropdown(dropdown, data, { ...options, selectedId: id });
      }
    });
  }
  