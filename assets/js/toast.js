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
    // Ensure the bootstrap object is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
      var toast = new bootstrap.Toast(toastContainer);
      toast.show();
    } else {
      console.error('Bootstrap is not loaded');
    }
  }
}

// document.addEventListener("turbo:frame-load", function (event) {
//   // Fetch and update the flash messages from the server
//   fetch('/docauposte/flash-messages')
//     .then(response => response.text())
//     .then(html => {
//       document.getElementById('flash-messages').innerHTML = html;
//     })
//     .catch(error => console.error('Error updating flash messages:', error));
// });
// document.addEventListener("turbo:load", function () {
//   // Fetch and update the flash messages from the server
//   fetch('/docauposte/flash-messages')
//     .then(response => response.text())
//     .then(html => {
//       const flashMessagesContainer = document.getElementById('flash-messages');
//       if (flashMessagesContainer) {
//         flashMessagesContainer.innerHTML = html;
//       } else {
//         console.error('Flash messages container not found.');
//       }
//     })
//     .catch(error => console.error('Error updating flash messages:', error));
// });
