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
        time = setTimeout(inactivity, delay || 300000); // Default to 5 minutes
    };

    const inactivity = () => {
        console.log('window inactivity due to inactivity at', new Date().toTimeString());
    // Send AJAX request to inform the server of inactivity
    axios.post('/inactivity')
      .then(response => {
        console.log('Server acknowledged inactivity');
      })
      .catch(error => {
        console.log('Error notifying server of inactivity:', error);
      });
        // window.location.inactivity();

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
    time = setTimeout(inactivity, delay || 300000); // Start the initial timer

};