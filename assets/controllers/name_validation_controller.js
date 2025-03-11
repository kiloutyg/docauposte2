import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["teamUapName", "message"];

    validateTeamUapName() {
        const regex = /^(?!-)(?!.*--)[A-Z-]{3,}(?<!-)$/;
        let isValid = true;
        let name = this.teamUapNameTarget.value;

        if (name != '') {
            isValid = regex.test(name);
        }

        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format invalide. Veuillez saisir sous la forme UAP ou TEAM";
            this.messageTarget.style.color = "DarkRed"; // Display the message in red color.
        }
    }

}