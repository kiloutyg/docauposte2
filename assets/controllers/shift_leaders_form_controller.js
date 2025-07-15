import { Controller } from '@hotwired/stimulus';

export default class ShiftLeadersFormController extends Controller {

    static targets = [
        'operatorShiftLeaders',
        'userShiftLeaders',
    ]


    /**
     * Handles the change event of the user shift leaders dropdown.
     * Disables or enables the operator shift leaders dropdown based on the selected user.
     *
     * @function userShiftLeadersChange
     * @returns {void}
     */
    userShiftLeadersChange() {
        /**
         * The selected value from the user shift leaders dropdown.
         * @type {string}
         */
        const selectedUser = this.userShiftLeadersTarget.value;
        console.log('User shift leaders changed', selectedUser);

        if (selectedUser !== '') {
            this.operatorShiftLeadersTarget.disabled = true;
            this.operatorShiftLeadersTarget.required = false;
        } else {
            this.operatorShiftLeadersTarget.disabled = false;
            this.operatorShiftLeadersTarget.required = true;
        }
    }

    /**
     * Handles the change event of the operator shift leaders dropdown.
     * Disables or enables the user shift leaders dropdown based on the selected operator.
     *
     * @function operatorShiftLeadersChange
     * @returns {void}
     */
    operatorShiftLeadersChange() {
        /**
         * The selected value from the operator shift leaders dropdown.
         * @type {string}
         */
        const selectedOperator = this.operatorShiftLeadersTarget.value;
        console.log('Operator shift leaders changed', selectedOperator);

        if (selectedOperator !== '') {
            this.userShiftLeadersTarget.disabled = true;
            this.userShiftLeadersTarget.required = false;
        } else {
            this.userShiftLeadersTarget.disabled = false;
            this.userShiftLeadersTarget.required = true;
        }
    }


}
