import { getSettingsData } from './server-variable.js';
import { timer } from './timer.js';

/**
 * Initializes the inactivity timer functionality.
 * Fetches the incident auto display timer setting from the server and starts the timer.
 * If the server request fails, a default delay of 5 minutes (300000 milliseconds) is used.
 */
document.addEventListener("turbo:load", function () {
    /**
     * Fetches the incident auto display timer setting from the server.
     * @returns {Promise<Object>} A promise that resolves with the server response data.
     */
    getSettingsData()
        .then((data) => {
            /**
             * The delay in milliseconds before triggering the inactivity check.
             * @type {number}
             */
            const delay = data.incidentAutoDisplayTimer;
            timer(delay, 'inactivity_check');
        })
        .catch((error) => {
            console.error('Query server variable error', response.data.cause, error);
            timer(300000, 'inactivity_check');
        });
});

