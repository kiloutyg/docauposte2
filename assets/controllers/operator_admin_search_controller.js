
import { Controller } from '@hotwired/stimulus';

import axios from 'axios';

export default class OperatorAdminSearchController extends Controller {


    static targets = [
        'operatorAdminSearchNameInput',
        'operatorAdminSearchSubmit',
        'operatorAdminSearchCodeInput',
        'operatorAdminSearchTeamInput',
        'operatorAdminSearchUapInput'

    ];
}