import { Controller } from '@hotwired/stimulus';

export default class DepartmentModificationController extends Controller {
  static targets = ["select"]
  
  /**
   * Stimulus controller lifecycle method that runs when the controller is connected to the DOM.
   * Initializes the select element listeners for handling department modification interactions.
   * 
   * @returns {void} This method does not return a value.
   */
  connect() {
    this.setupSelectListeners();
  }
  
  /**
   * Sets up event listeners for all select elements targeted by this controller.
   * Attaches a 'change' event listener to each select element that triggers
   * the handleSelectionChange method when the selection state changes.
   * 
   * @returns {void} This method does not return a value.
   */
  setupSelectListeners() {
    this.selectTargets.forEach(select => {
      select.addEventListener('change', this.handleSelectionChange.bind(this, select));
    });
  }
  
  /**
   * Handles the selection change event for a multi-select element, implementing
   * mutually exclusive behavior between a "None" option (value="0") and all other options.
   * When other options are selected, the "None" option is automatically deselected.
   * When the "None" option is selected, all other options are automatically deselected.
   * If no options are selected, the "None" option is automatically selected as a fallback.
   * 
   * @param {HTMLSelectElement} select - The select element that triggered the change event.
   *                                    Must be a multi-select element containing options with
   *                                    a "None" option having value="0".
   * @returns {void} This method does not return a value.
   */
  handleSelectionChange(select) {
    const options = Array.from(select.options);
    const noneOption = options.find(option => option.value === "0");
    const otherOptions = options.filter(option => option.value !== "0");
    
    // If any option other than "None" is selected
    const hasOtherSelected = otherOptions.some(option => option.selected);
    
    if (noneOption) {
      if (hasOtherSelected) {
        // If other options are selected, unselect the "None" option
        noneOption.selected = false;
      } else if (options.every(option => !option.selected)) {
        // If no options are selected, select the "None" option
        noneOption.selected = true;
      }
    }
    
    // If "None" option is selected, unselect all other options
    if (noneOption?.selected) {
      otherOptions.forEach(option => {
        option.selected = false;
      });
    }
    
  }
}