import { Controller } from '@hotwired/stimulus';
import { getSettingsData } from '../js/server-variable';

import axios from 'axios';

export default class OperatorCodeController extends Controller {


    retrieveCodeOpeRegexSettings() {
        getSettingsData()
            .then((data) => { })
    }
}
