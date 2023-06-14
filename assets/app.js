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
import $ from "jquery";
import { createPopper } from "@popperjs/core";

// Make jQuery and Popper.js available globally
global.$ = global.jQuery = $;
global.createPopper = createPopper;

// import "./js/formdata.js";
import "bootstrap";

// // homegrown javascript
// import "./js/confirmation.js";
// import "./js/cascading-dropdowns.js";
// import "./js/incident-cascading-dropdowns.js";
// import "./js/incident-checkbox-signature.js";
// import "./js/toast.js";
