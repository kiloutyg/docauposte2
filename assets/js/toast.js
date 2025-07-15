/**
 * Initializes and displays toast notifications on the page.
 * 
 * This function listens for the "turbo:load" event and then selects all elements
 * with the class 'toast'. It adds a 'show' class to each toast to display it,
 * sets a timeout to fade out the toast after 10 seconds, and removes the toast
 * from the DOM after the fade-out transition ends.
 * 
 * @event turbo:load - The event that triggers the initialization of toasts.
 * @returns {void} This function does not return a value.
 */
document.addEventListener("turbo:load", () => {
  // Initialize all toasts
  document.querySelectorAll('.toast').forEach((toast) => {
    toast.classList.add("show");

    // Set up fade out after 2 seconds
    setTimeout(() => {
      toast.style.transition = 'opacity 1s';
      toast.style.opacity = '0';

      // Remove from DOM after fade animation
      toast.addEventListener('transitionend', () => {
        toast.remove();
      });
    }, 10000);
  });
});