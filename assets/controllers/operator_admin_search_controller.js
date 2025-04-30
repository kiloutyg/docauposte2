import { operatorCodeService } from './services/operator_code_service';

import { Controller } from '@hotwired/stimulus';

export default class OperatorAdminSearchController extends Controller {


    static targets = [
        'operatorAdminSearchNameInput',
        'operatorAdminSearchCodeInput',
        'operatorAdminSearchTeamInput',
        'operatorAdminSearchUapInput',
        'operatorAdminSearchIsTrainerInput',
        'operatorAdminSearchSubmit'


    ];


    connect() {
        this.initialIsTrainerValue = this.operatorAdminSearchIsTrainerInputTarget.value;
    }

    validateSearchByName(name) {
        const regex = /^[a-z]+(-[a-z]+)*$/;
        name = name.toLowerCase();
        const isValid = regex.test(name.trim());
        return isValid;

    }


    async validateSearchByCode(code) {
        const settings = await operatorCodeService.getSettings();
        const regex = settings.regex;
        const isValid = regex.test(code.trim());
        return isValid;
    }


    async validateSearchByTeam(team) {
        const regex = /^[a-z][A-Z]+$/;
        const isValid = regex.test(team.trim());
        return isValid;
    }

    async validateSearchByUap(uap) {
        const regex = /^[a-z][A-Z]+$/;
        const isValid = regex.test(uap.trim());
        return isValid;

    }


    submitSearchForm() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            try { this.submitSearch(); }
            catch (e) {
                console.error('submit Search Form error catch', e)
            }
        }, 1000);

    }


    submitSearch() {
        const name = this.operatorAdminSearchNameInputTarget.value;
        const code = this.operatorAdminSearchCodeInputTarget.value;
        const team = this.operatorAdminSearchTeamInputTarget.value;
        const uap = this.operatorAdminSearchUapInputTarget.value;
        const isTrainer = this.operatorAdminSearchIsTrainerInputTarget.value;

        let submitCount = 0;
        let validations = [];

        if (name !== '') validations.push(this.validateSearchByName(name));
        if (code !== '') validations.push(this.validateSearchByCode(code));
        if (team !== '') validations.push(this.validateSearchByTeam(team));
        if (uap !== '') validations.push(this.validateSearchByUap(uap));

        validations.push(isTrainer !== this.initialIsTrainerValue); // Assuming selection is mandatory unless "Tous"

        submitCount = validations.filter(Boolean).length;

        if (submitCount > 0) {
            this.operatorAdminSearchSubmitTarget.click();
            this.cleanSubmittedTargetValue();
        } else {
            console.error('Validation failed');
            this.cleanSubmittedTargetValue();
        }
    }



    cleanSubmittedTargetValue() {
        clearTimeout(this.cleanTimeout);
        this.cleanTimeout = setTimeout(() => {
            this.operatorAdminSearchUapInputTarget.value = '';
            this.operatorAdminSearchTeamInputTarget.value = '';
            this.operatorAdminSearchCodeInputTarget.value = '';
            this.operatorAdminSearchNameInputTarget.value = '';
            this.operatorAdminSearchIsTrainerInputTarget.value = '';
        }, 15000);
    }
}