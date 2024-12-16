import { getSettingsData } from './serverVariable.js';
import axios from 'axios'; // Import axios

document.addEventListener("turbo:load", function () {
    getSettingsData()
        .then((data) => {
            const delay = data.incidentAutoDisplayTimer;
            console.log('timer in milliseconds', delay)
            cyclingTime(delay);
        })
        .catch((error) => {
            console.log('Error fetching settings data:', error);
            cyclingTime(300000); // 5 minutes
        });
});

function cyclingTime(delay) {
    let time;

    const resetTimer = () => {
        console.log('cycling timer reset at', new Date().toTimeString());
        clearTimeout(time);
        time = setTimeout(cycling, delay || 300000); // Default to 5 minutes
    };

    const cycling = () => {
        console.log('window cycling due to cycling at', new Date().toTimeString());
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
                console.log('Error notifying server of cycling:', error);
                resetTimer();
            });
    };

    console.log('cycling timer started at', new Date().toTimeString());
    time = setTimeout(cycling, delay || 300000); // Start the initial timer

};