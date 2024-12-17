import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentDisplayController extends Controller {

    static targets = [
        "displayNeededToggle",
        "newDocumentDisplayValidatorCheckboxForcedDisplay",
        "modificationDocumentDisplayValidatorCheckboxForcedDisplay",
    ];

    newDocumentValidatorCheckboxClickedForcedDisplay() {
        // console.log("forced display validatorCheckboxClicked");
        // console.log(this.newDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked);
        // console.log('forced display toggle ', this.displayNeededToggleTarget.value);
        if (this.newDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
            // console.log("forced display checked ", this.displayNeededToggleTarget.checked);
        } else {
            this.displayNeededToggleTarget.checked = false;
            // console.log("forced display unchecked ", this.displayNeededToggleTarget.unchecked);
        }
    }

    modificationDocumentValidatorCheckboxClickedForcedDisplay() {
        // console.log("forced display validatorCheckboxClicked");
        // console.log(this.modificationDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked);
        // console.log('forced display toggle ', this.displayNeededToggleTarget.value);
        if (this.modificationDocumentDisplayValidatorCheckboxForcedDisplayTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
            // console.log("forced display checked ", this.displayNeededToggleTarget.checked);
        } else {
            this.displayNeededToggleTarget.checked = false;
            // console.log("forced display unchecked ", this.displayNeededToggleTarget.unchecked);
        }
    }
}