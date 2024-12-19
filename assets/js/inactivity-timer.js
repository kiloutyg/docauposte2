import { getSettingsData } from './server-variable.js';
import { timer } from './timer.js';

document.addEventListener("turbo:load", function () {
    getSettingsData()
        .then((data) => {
            const delay = data.incidentAutoDisplayTimer;
            timer(delay, 'inactivity_check');
        })
        .catch((error) => {
            console.error('Query server variable error', response.data.cause, error);
            timer(300000, 'inactivity_check');
        });
});

