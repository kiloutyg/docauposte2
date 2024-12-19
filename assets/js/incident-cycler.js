import { getSettingsData } from './server-variable.js';
import { timer } from './timer.js';

document.addEventListener("turbo:load", function () {
    getSettingsData()
        .then((data) => {
            const delay = data.incidentAutoDisplayTimer;
            timer(delay, 'cycling_incident');
        })
        .catch((error) => {
            console.error('Error fetching settings data:', response.data.cause, error);
            timer(300000, 'cycling_incident');
        });
});



