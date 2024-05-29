import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentTrainingCOntroller extends Controller {

    static targets = [
        "trainingNeededToggle",
        "newDocumentTrainingValidatorCheckbox",
    ];

    validatorCheckboxClicked() {
        console.log("validatorCheckboxClicked");
        console.log(this.newDocumentTrainingValidatorCheckboxTarget.checked);
        console.log('toggle ', this.trainingNeededToggleTarget.value);
        if (this.newDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
            console.log("checked ", this.trainingNeededToggleTarget.checked);
        } else {
            this.trainingNeededToggleTarget.checked = false;
            console.log("unchecked ", this.trainingNeededToggleTarget.unchecked);
        }
    }
}