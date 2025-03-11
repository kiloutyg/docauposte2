import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["teamUapName", "message"];

    validateTeamUapName() {
        const regex = /^[A-Z-]{3,}$(?<!-)$/;

        var name = this.teamUapNameTarget.value;

        if (name != '') {
            var isValid = regex.test(name);
        } else {
            var isValid = true;
        }

        if (isValid) {
            this.messageTarget.textContent = "";
        } else {
            this.messageTarget.textContent = "Format invalide. Veuillez saisir sous la forme UAP ou TEAM";
            this.messageTarget.style.color = "DarkRed"; // Display the message in red color.
        }
    }

}