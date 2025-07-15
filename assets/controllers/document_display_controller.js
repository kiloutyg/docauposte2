import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentDisplayController extends Controller {

    static targets = [
        "displayNeededToggle",
        "newDocumentDisplayValidatorCheckboxForcedDisplay",
        "modificationDocumentDisplayValidatorCheckboxForcedDisplay",
    ];

    /**
     * Handles the click event for the new document validator checkbox that forces display.
     * When the checkbox is checked, it automatically enables the display needed toggle.
     * When unchecked, it disables the display needed toggle.
     * 
     * @returns {void} This function does not return a value.
     */
    newDocumentValidatorCheckboxClickedForcedDisplay() {
        if (this.newDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
        } else {
            this.displayNeededToggleTarget.checked = false;
        }
    }

    /**
     * Handles the click event for the modification document validator checkbox that forces display.
     * When the checkbox is checked, it automatically enables the display needed toggle.
     * When unchecked, it disables the display needed toggle.
     * 
     * @returns {void} This function does not return a value.
     */
    modificationDocumentValidatorCheckboxClickedForcedDisplay() {
        if (this.modificationDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
        } else {
            this.displayNeededToggleTarget.checked = false;
        }
    }
}