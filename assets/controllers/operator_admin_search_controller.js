

import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class OperatorAdminSearchController extends Controller {


    static targets = [
        'operatorAdminSearchNameInput',
        'operatorAdminSearchCodeInput',
        'operatorAdminSearchTeamInput',
        'operatorAdminSearchUapInput',
        'operatorAdminSearchSubmit'

    ];

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
        const regex = /^[0-9]{5}$/;
        code = code;
        const isValid = regex.test(code.trim());
        console.log('isValid:', isValid);
        return isValid;
    }


    async validateSearchByTeam(team) {
        console.log('searching by team', team);
        const regex = /^[a-z][A-Z]+$/;
        team = team;
        const isValid = regex.test(team.trim());
        console.log('isValid:', isValid);
        return isValid;
    }

    async validateSearchByUap(uap) {
        console.log('searching by uap', uap);
        const regex = /^[a-z][A-Z]+$/;
        uap = uap;
        const isValid = regex.test(uap.trim());
        console.log('isValid:', isValid);
        return isValid;

    }


    submitSearchForm() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            console.log('submitting search');
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

        let submitCount = 0;
        if (name !== '') {
            if (this.validateSearchByName(name)) {
                submitCount++;
            } else {
                console.log('invalid name');
                this.operatorAdminSearchNameInputTarget.value = '';
            }
        }
        if (code !== '') {
            if (this.validateSearchByCode(code)) {
                submitCount++;
            } else {
                console.log('invalid code');
                this.operatorAdminSearchCodeInputTarget.value = '';
            }
        }
        if (team !== '') {
            if (this.validateSearchByTeam(team)) {
                submitCount++;
            } else {
                console.log('invalid team')
                this.operatorAdminSearchTeamInputTarget.value = '';
            }
        }
        if (uap !== '') {
            if (this.validateSearchByUap(uap)) {
                submitCount++;
            } else {
                console.log('invalid uap');
                this.operatorAdminSearchUapInputTarget.value = '';
            }
        }

        if (submitCount > 0) {
            this.operatorAdminSearchSubmitTarget.click();
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
        }, 5000);
    }
}