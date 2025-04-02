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
    }, 6000);
  });
});