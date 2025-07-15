/**
 * Initializes event listeners for checkboxes, a text field, and a button.
 * Displays the text field when all checkboxes are checked and displays the button when the text field is not empty.
 *
 * @event turbo:load - Triggered when the page is fully loaded.
 * @param {Event} event - The event object for the turbo:load event.
 * @returns {void} This function does not return a value.
 */
document.addEventListener("turbo:load", function () // window.onload = function
{
  let checkboxes = document.querySelectorAll(".checkbox-input");
  let textField = document.querySelector(".text-input");
  let nextButton = document.querySelector("#nextButton");

  if (checkboxes && textField && nextButton) {
    for (let i = 0; i < checkboxes.length; i++) {
      checkboxes[i].addEventListener("change", function () {
        let allChecked = Array.from(checkboxes).every(
          (checkbox) => checkbox.checked
        );
        if (allChecked) {
          textField.style.display = "block";
        }
      });
    }

    textField.addEventListener("input", function () {
      if (textField.value !== "") {
        nextButton.style.display = "block";
      } else {
        nextButton.style.display = "none";
      }
    });
  }
});
