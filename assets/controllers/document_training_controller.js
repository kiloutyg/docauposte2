import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentTrainingController extends Controller {

    static targets = [
        "trainingNeededToggle",
        "newDocumentTrainingValidatorCheckbox",
        "modificationDocumentTrainingValidatorCheckbox",
    ];

    newDocumentValidatorCheckboxClicked() {
        // console.log("training needed validatorCheckboxClicked");
        // console.log(this.newDocumentTrainingValidatorCheckboxTarget.checked);
        // console.log('training needed toggle ', this.trainingNeededToggleTarget.value);
        if (this.newDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
            // console.log("training needed checked ", this.trainingNeededToggleTarget.checked);
        } else {
            this.trainingNeededToggleTarget.checked = false;
            // console.log("training needed unchecked ", this.trainingNeededToggleTarget.unchecked);
        }
    }

    modificationDocumentValidatorCheckboxClicked() {
        // console.log("training needed validatorCheckboxClicked");
        // console.log(this.modificationDocumentTrainingValidatorCheckboxTarget.checked);
        // console.log('training needed toggle ', this.trainingNeededToggleTarget.value);
        if (this.modificationDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
            // console.log("training needed checked ", this.trainingNeededToggleTarget.checked);
        } else {
            this.trainingNeededToggleTarget.checked = false;
            // console.log("training needed unchecked ", this.trainingNeededToggleTarget.unchecked);
        }
    }
}