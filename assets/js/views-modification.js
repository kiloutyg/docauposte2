document.addEventListener("turbo:load", function () {
    // Create a variable to store the input and select elements inside accordion buttons
    var accordionInputs = $('.accordion-button input, .accordion-button select');

    // Attach event listeners to the accordionInputs variable
    accordionInputs.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    accordionInputs.addEventListener('focus', function (e) {
        e.stopPropagation();
    });
});