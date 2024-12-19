import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class DocumentTrainingController extends Controller {

    static targets = [
        "trainingNeededToggle",
        "newDocumentTrainingValidatorCheckbox",
        "modificationDocumentTrainingValidatorCheckbox",
    ];

    newDocumentValidatorCheckboxClicked() {
        if (this.newDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
        } else {
            this.trainingNeededToggleTarget.checked = false;
        }
    }

    modificationDocumentValidatorCheckboxClicked() {
        if (this.modificationDocumentTrainingValidatorCheckboxTarget.checked === true) {
            this.trainingNeededToggleTarget.checked = true;
        } else {
            this.trainingNeededToggleTarget.checked = false;
        }
    }
}