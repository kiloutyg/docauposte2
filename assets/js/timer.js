import axios from 'axios'; // Import axios

export function timer(delay, locationString) {
    let time;

    const resetTimer = () => {
        console.log('Inactivity timer reset at', new Date().toTimeString());
        clearTimeout(time);
        time = setTimeout(inactivity, delay || 300000); // Default to 5 minutes
    };

    const inactivity = () => {
        console.log('window inactivity due to inactivity at', new Date().toTimeString());
        axios.post('/docauposte/' + locationString)
            .then(response => {
                console.log('response', response);
                console.log('response data redirect', response.data.redirect);
                if (response.data.redirect) {
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

    console.log('Inactivity timer started at', new Date().toTimeString());
    time = setTimeout(inactivity, delay || 300000); // Start the initial timer

};