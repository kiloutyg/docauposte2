import { getSettingsData } from './serverVariable.js';
document.addEventListener("turbo:load", function () {
    getSettingsData()
        .then((data) => {
            const delay = data.incidentAutoDisplayTimer;
            console.log('timer', delay)
            inactivityTime(delay);
        })
        .catch((error) => {
            console.log('Error fetching settings data:', error);
            // Fall back to a default delay if needed
            inactivityTime(300000); // 5 minutes
        });
});

function inactivityTime(delay) {
    let time;

    const resetTimer = () => {
        console.log('Inactivity timer reset at', new Date().toTimeString());
        clearTimeout(time);
        time = setTimeout(reload, delay || 300000); // Default to 5 minutes
    };

    const reload = () => {
        console.log('window reload due to inactivity at', new Date().toTimeString());
        // Redirect or take appropriate action
        window.location.reload();
    };

    // Attach event listeners using a helper function
    const attachEventListeners = () => {
        const events = [
            'load',
            // 'mousemove',
            // 'keydown',
            // 'click',
            // 'scroll'
        ];
        events.forEach(event => {
            window.addEventListener(event, resetTimer);
        });
    };

    console.log('Inactivity timer started at', new Date().toTimeString());
    attachEventListeners();
    time = setTimeout(reload, delay || 300000); // Start the initial timer

};