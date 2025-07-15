import { getSettingsData } from './server-variable.js';
import { timer } from './timer.js';

/**
 * Event listener for the "turbo:load" event that fetches settings data and starts a timer.
 * 
 * @event turbo:load
 * @function
 * @description Fetches settings data to determine the delay for the incident auto-display timer.
 *              If fetching fails, a default delay is used. The timer is then started with the
 *              specified delay and a cycling incident identifier.
 */
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



