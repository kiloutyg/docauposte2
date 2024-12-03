import { getSettingsData } from './serverVariable.js';

getSettingsData()
    .then((data) => {
        const delay = data.incidentAutoDisplayTimer;
        console.log('timer', delay)
        inactivityTime(delay);
    })
    .catch((error) => {
        console.log('Error fetching settings data:', error);
    });


function inactivityTime(delay) {
    let time;

    window.onload = resetTimer;
    // document.onmousemove = resetTimer;
    document.onkeydown = resetTimer;
    document.onclick = resetTimer;
    document.onscroll = resetTimer;

    function logout() {
        // Replace 'app_inactivity_redirect' with your route name
        window.location.reload();
    }

    function resetTimer() {
        clearTimeout(time);
        // Set the timer (e.g., 5 minutes = 300,000 ms)
        time = setTimeout(logout, 300000);
        // time = setTimeout(logout, delay);

    }
};

