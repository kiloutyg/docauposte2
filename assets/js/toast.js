document.addEventListener("turbo:load", () => {
  var toastLiveExample = document.getElementById("liveToast");

  if (toastLiveExample) {
    toastLiveExample.classList.add("show");
  }
});

document.addEventListener("turbo:load", showToast);
document.addEventListener("turbo:frame-load", showToast);

function showToast() {
  var toastContainer = document.getElementById("liveToast");
  if (toastContainer) {
    var toast = new bootstrap.Toast(toastContainer);
    toast.show();
  }
}
