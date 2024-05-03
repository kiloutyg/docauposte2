import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class operatorAdminEdit extends Controller {


    static targets = [
        "operatorFormFirstname",
        "operatorFormLastname",
        "operatorFormCode",
        "operatorFormTeam",
        "operatorFormUap",
        "operatorFormIsTrainer",
    ];


}
