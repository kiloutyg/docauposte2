document.addEventListener("turbo:load", function () {
  let checkboxes = document.querySelectorAll(".checkbox-input");
  let textField = document.querySelector(".text-input");
  let nextButton = document.querySelector("#nextButton");

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
});
