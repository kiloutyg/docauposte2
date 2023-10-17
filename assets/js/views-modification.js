$(document).ready(function () {
    // Stop event propagation for inputs inside accordion buttons
    $('.accordion-button input, .accordion-button select').on('click', function (e) {
        e.stopPropagation();
    });

    // Additionally, if you want to prevent the accordion from acting when the input or select gains focus
    $('.accordion-button input, .accordion-button select').on('focus', function (e) {
        e.stopPropagation();
    });
});