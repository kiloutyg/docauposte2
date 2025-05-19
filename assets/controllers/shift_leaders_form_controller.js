import { Controller } from '@hotwired/stimulus';

export default class ShiftLeadersFormController extends Controller {

    static targets = [
        'operatorShiftLeaders',
        'userShiftLeaders',
    ]


    userShiftLeadersChange() {
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

    operatorShiftLeadersChange() {
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
