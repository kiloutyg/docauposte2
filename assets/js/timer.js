import axios from 'axios'; // Import axios

/**
 * Initializes a timer that triggers an inactivity check after a specified delay.
 * If the locationString is 'inactivity_check', it attaches event listeners to reset the timer on user activity.
 *
 * @param {number} delay - The delay in milliseconds before the inactivity function is triggered. Defaults to 300000 (5 minutes) if not provided.
 * @param {string} locationString - A string used to determine the endpoint for the inactivity check and to conditionally attach event listeners.
 */
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