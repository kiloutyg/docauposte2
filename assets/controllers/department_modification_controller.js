import { Controller } from '@hotwired/stimulus';

export default class DepartmentModificationController extends Controller {
  static targets = ["select"]
  
  connect() {
    this.setupSelectListeners();
  }
  
  setupSelectListeners() {
    this.selectTargets.forEach(select => {
      select.addEventListener('change', this.handleSelectionChange.bind(this, select));
    });
  }
  
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