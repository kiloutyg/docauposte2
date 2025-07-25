import { Controller } from '@hotwired/stimulus';

export default class TrainingMaterialTypeFormController extends Controller {

    static targets = [
        'trainingMaterialTypeCateogorySelector',
        'uploadSelector',
    ]

    /**
     * Handles the change event for the training material type category selector.
     * Enables or disables the upload selector based on the selected category value.
     * When 'Upload' is selected, the upload selector becomes enabled and required.
     * For any other selection, the upload selector is disabled and not required.
     * 
     * @returns {void} This function does not return a value
     */
    trainingMaterialTypeCateogorySelectorChange() {
        const selectedCategory = this.trainingMaterialTypeCateogorySelectorTarget.value;
        console.log('Training Material Selector changed', selectedCategory);
        if (selectedCategory === 'Upload') {
            this.uploadSelectorTarget.disabled = false;
            this.uploadSelectorTarget.required = true;
        } else {
            this.uploadSelectorTarget.disabled = true;
            this.uploadSelectorTarget.required = false;
        }
    }



}
