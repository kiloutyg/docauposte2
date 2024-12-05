import { getSettingsData } from './serverVariable.js';
import axios from 'axios'; // Import axios

document.addEventListener("turbo:load", function () {
    getSettingsData()
        .then((data) => {
            const delay = data.incidentAutoDisplayTimer;
            console.log('timer in milliseconds', delay)
            inactivityTime(delay);
        })
        .catch((error) => {
            console.log('Error fetching settings data:', error);
            inactivityTime(300000); // 5 minutes
        });
});

function inactivityTime(delay) {
    let time;

    const resetTimer = () => {
        console.log('Inactivity timer reset at', new Date().toTimeString());
        clearTimeout(time);
        time = setTimeout(inactivity, delay || 300000); // Default to 5 minutes
    };

    const inactivity = () => {
        console.log('window inactivity due to inactivity at', new Date().toTimeString());
        axios.post('/docauposte/cycling_incident')
            .then(response => {
                console.log('response', response);
                console.log('response data redirect', response.data.redirect);
                if (response.data.redirect) {
                    // Redirect the browser to the new URL
                    window.location.href = response.data.redirect;
                } else if (response.data.redirect === false) {
                    console.log('response data cause', response.data.cause);
                    resetTimer();
                }
            })
            .catch(error => {
                console.log('Error notifying server of inactivity:', error);
                resetTimer();
            });
    };

    console.log('Inactivity timer started at', new Date().toTimeString());
    time = setTimeout(inactivity, delay || 300000); // Start the initial timer

};