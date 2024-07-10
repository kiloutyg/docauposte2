import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentDisplayController extends Controller {

    static targets = [
        "displayNeededToggle",
        "newDocumentDisplayValidatorCheckbox",
        "modificationDocumentDisplayValidatorCheckbox",
    ];

    newDocumentValidatorCheckboxClickedForcedDisplay() {
        console.log("forced display validatorCheckboxClicked");
        console.log(this.newDocumentDisplayValidatorCheckboxTarget.checked);
        console.log('forced display toggle ', this.displayNeededToggleTarget.value);
        if (this.newDocumentDisplayValidatorCheckboxTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
            console.log("forced display checked ", this.displayNeededToggleTarget.checked);
        } else {
            this.displayNeededToggleTarget.checked = false;
            console.log("forced display unchecked ", this.displayNeededToggleTarget.unchecked);
        }
    }

    modificationDocumentValidatorCheckboxClickedForcedDisplay() {
        console.log("forced display validatorCheckboxClicked");
        console.log(this.modificationDocumentDisplayValidatorCheckboxTarget.checked);
        console.log('forced display toggle ', this.displayNeededToggleTarget.value);
        if (this.modificationDocumentDisplayValidatorCheckboxTarget.checked === true) {
            this.displayNeededToggleTarget.checked = true;
            console.log("forced display checked ", this.displayNeededToggleTarget.checked);
        } else {
            this.displayNeededToggleTarget.checked = false;
            console.log("forced display unchecked ", this.displayNeededToggleTarget.unchecked);
        }
    }
}