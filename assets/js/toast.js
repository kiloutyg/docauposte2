document.addEventListener("turbo:load", () => {
  var toastLiveExample = document.getElementById("liveToast");

  if (toastLiveExample) {
    toastLiveExample.classList.add("show");
  }
});



document.addEventListener('DOMContentLoaded', (event) => {
  setTimeout(() => {
    document.querySelectorAll('.toast').forEach((message) => {
      message.style.transition = 'opacity 0.5s';
      message.style.opacity = '0';
      setTimeout(() => message.remove(), 300); // Remove from DOM after fade out
    });
  }, 3000); // 5 seconds
});
