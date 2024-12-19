import axios from 'axios'; // Import axios

export function timer(delay, locationString) {
    let time;

    const resetTimer = () => {
        clearTimeout(time);
        time = setTimeout(inactivity, delay || 300000); // Default to 5 minutes
    };

    const inactivity = () => {
        axios.post('/docauposte/' + locationString)
            .then(response => {
                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                } else if (response.data.redirect === false) {
                    resetTimer();
                }
            })
            .catch(error => {
                console.error('Error notifying server of inactivity:', error);
                resetTimer();
            });
    };
    if (locationString === 'inactivity_check') {
        // Attach event listeners using a helper function
        const attachEventListeners = () => {
            const events = [
                'load',
                'keydown',
                'click',
                'scroll'
            ];
            events.forEach(event => {
                window.addEventListener(event, resetTimer);
            });
        };
        attachEventListeners();
    }

    time = setTimeout(inactivity, delay || 300000); // Start the initial timer

};