import '@hotwired/turbo';
document.addEventListener('turbo:load', () => {
    console.log('Turbo frame loaded');
});

document.addEventListener('turbo:before-render', (event) => {
    console.log('Before Turbo renders', event);
});

document.addEventListener('turbo:render', (event) => {
    console.log('After Turbo renders', event);
});

document.addEventListener('turbo:submit-start', (event) => {
    console.log('Form submit started', event);
});
import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import "./styles/app.scss";

// start the Stimulus application
import "./bootstrap";

// Import jQuery and Popper.js
// import $ from "jquery";
// import { createPopper } from "@popperjs/core";

// Make jQuery and Popper.js available globally
// global.$ = global.jQuery = $;
// global.createPopper = createPopper;

// Import Bootstrap's JavaScript
import "bootstrap";

// // Import the Select2 library
// import "select2";

// // initialize Select2 on your elements
// $(document).ready(function () {
//     $('.select2-enable').select2();
// });

