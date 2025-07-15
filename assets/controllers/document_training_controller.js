import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentTrainingController extends Controller {

    static targets = [
        "trainingNeededToggle",
        "newDocumentTrainingValidatorCheckbox",
        "modificationDocumentTrainingValidatorCheckbox",
    ];

    /**
     * Handles the click event for the new document training validator checkbox.
     * When the checkbox is checked, it automatically enables the training needed toggle.
     * When unchecked, it disables the training needed toggle.
     * 
     * @returns {void} This method does not return a value.
     */
    newDocumentValidatorCheckboxClicked() {
        if (this.newDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
        } else {
            this.trainingNeededToggleTarget.checked = false;
        }
    }

    /**
     * Handles the click event for the modification document training validator checkbox.
     * When the checkbox is checked, it automatically enables the training needed toggle.
     * When unchecked, it disables the training needed toggle.
     * 
     * @returns {void} This method does not return a value.
     */
    modificationDocumentValidatorCheckboxClicked() {
        if (this.modificationDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
        } else {
            this.trainingNeededToggleTarget.checked = false;
        }
    }
}