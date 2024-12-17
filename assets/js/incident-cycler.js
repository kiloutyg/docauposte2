import { getSettingsData } from './server-variable.js';
import { timer } from './timer.js';

document.addEventListener("turbo:load", function () {
    getSettingsData()
        .then((data) => {
            const delay = data.incidentAutoDisplayTimer;
            // console.log('timer in milliseconds', delay)
            timer(delay, 'cycling_incident');
        })
        .catch((error) => {
            // console.log('Error fetching settings data:', error);
            timer(300000, 'cycling_incident');
        });
});



