

import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

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
        console.log('initialIsTrainerValue:', this.initialIsTrainerValue);
    }

    validateSearchByName(name) {
        console.log('searching by name', name);
        const regex = /^[a-z]+(-[a-z]+)*$/;
        name = name.toLowerCase();
        const isValid = regex.test(name.trim());
        console.log('isValid:', isValid);
        return isValid;

    }


    async validateSearchByCode(code) {
        console.log('searching by code', code);
        const regex = /^[0-9]$/;
        const isValid = regex.test(code.trim());
        console.log(code, 'isValid:', isValid);
        return isValid;
    }


    async validateSearchByTeam(team) {
        console.log('searching by team', team);
        const regex = /^[a-z][A-Z]+$/;
        const isValid = regex.test(team.trim());
        console.log(team, 'isValid:', isValid);
        return isValid;
    }

    async validateSearchByUap(uap) {
        console.log('searching by uap', uap);
        const regex = /^[a-z][A-Z]+$/;
        const isValid = regex.test(uap.trim());
        console.log(uap, 'isValid:', isValid);
        return isValid;

    }


    submitSearchForm() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            console.log('submitting search', Date.now());
            try { this.submitSearch(); }
            catch (e) {
                console.log(e);
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
        }, 10000);
    }
}