import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentDisplayController extends Controller {

    static targets = [
        "displayNeededToggle",
        "newDocumentDisplayValidatorCheckboxForcedDisplay",
        "modificationDocumentDisplayValidatorCheckboxForcedDisplay",
    ];

    newDocumentValidatorCheckboxClickedForcedDisplay() {
        if (this.newDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
        } else {
            this.displayNeededToggleTarget.checked = false;
        }
    }

    modificationDocumentValidatorCheckboxClickedForcedDisplay() {
        if (this.modificationDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
        } else {
            this.displayNeededToggleTarget.checked = false;
        }
    }
}