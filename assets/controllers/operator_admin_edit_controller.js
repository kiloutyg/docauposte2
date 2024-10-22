import { Controller } from '@hotwired/stimulus';
import OperatorAdminCreationController from './operator_admin_creation_controller';
import axios from 'axios';

export default class operatorAdminEdit extends OperatorAdminCreationController {


    static targets = [
        "operatorFormFirstname",
        "operatorFormLastname",
        "operatorFormCode",
        "operatorFormTeam",
        "operatorFormUap",
        "operatorFormIsTrainer",
    ];


    async EditedCodeChecker() {
        const code = this.operatorFormCodeTarget.value;

        try {
            const reponse = await axios.post(`/docauposte/operator/check-if-code-exist`, { code: code });
            console.log('response for edited code checker:', reponse.data);
            if (reponse.data.found) {
                console.log('code already exists');
                this.operatorFormCodeTarget.value = "";
                this.operatorFormCodeTarget.placeholder = 'Code déjà utilisé';
            } else {
                console.log('code does not exist');

            }
        } catch (error) {
            console.error('Error checking for duplicate operator code.', error);
            // Handle error accordingly
        }
    }

}
