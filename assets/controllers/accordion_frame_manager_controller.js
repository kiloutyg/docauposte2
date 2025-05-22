import { Controller } from '@hotwired/stimulus';

/**
 * A controller that manages Turbo Frame loading based on accordion state
 * Works with the turbo_frame_loader macro from accordion_loading_animations.html.twig
 */
export default class AccordionFrameManagerController extends Controller {
  static targets = ["accordionContent", "turboFrame"]
  
  // Keep track of all open accordions
  static values = {
    parentId: String
  }
  
  /**
   * Initializes the AccordionFrameManagerController.
   * This function is called when the controller is connected to the DOM.
   * It performs initial setup, such as finding the parent accordion ID,
   * checking if the accordion is initially open, and listening for
   * bootstrap collapse events on other accordions.
   *
   * @returns {void}
   */
  connect() {
    console.log("AccordionFrameManager connected", {
      hasAccordionContent: this.hasAccordionContentTarget,
      hasTurboFrame: this.hasTurboFrameTarget
    });
    
    // Find the parent accordion ID
    if (this.hasAccordionContentTarget) {
      const parentId = this.accordionContentTarget.getAttribute('data-bs-parent');
      if (parentId) {
        this.parentIdValue = parentId;
        console.log(`Parent accordion ID: ${this.parentIdValue}`);
      }
    }

    // Check if the accordion is initially open
    if (this.hasAccordionContentTarget && this.accordionContentTarget.classList.contains('show')) {
      console.log("Accordion is initially open, loading frame immediately");
      this.loadFrame();
    }
    
    // Listen for bootstrap collapse events on other accordions
    document.addEventListener('show.bs.collapse', this.handleOtherAccordionOpen.bind(this));
  }
  
  /**
   * Disconnects the controller and cleans up event listeners.
   * This function is called automatically by Stimulus when the controller
   * is disconnected from the DOM. It removes the event listener for
   * bootstrap collapse events to prevent memory leaks.
   *
   * @returns {void}
   */
  disconnect() {
    // Clean up event listener
    document.removeEventListener('show.bs.collapse', this.handleOtherAccordionOpen.bind(this));
  }
  
  // Handle when other accordions are opened
  /**
   * Handles the event when another accordion is opened.
   * This function is triggered by the 'show.bs.collapse' Bootstrap event.
   * It checks if the opened accordion belongs to the same parent group,
   * and if so, unloads the current frame if this accordion is open.
   * This ensures that only one accordion's content is loaded at a time
   * within the same accordion group.
   *
   * @param {Event} event - The Bootstrap collapse event object containing information about the opened accordion
   * @returns {void}
   */
  handleOtherAccordionOpen(event) {
    // Only proceed if we have a parent ID and this is not our own accordion
    if (this.parentIdValue && event.target !== this.accordionContentTarget) {
      // Check if the opened accordion is in the same parent
      const openedAccordionParent = event.target.getAttribute('data-bs-parent');
      if (openedAccordionParent === this.parentIdValue) {
        console.log("Another accordion in the same parent is being opened, unloading this frame if open");
        
        // If our accordion is open, unload it
        if (this.hasAccordionContentTarget && this.accordionContentTarget.classList.contains('show')) {
          console.log("This accordion is open, unloading frame");
          this.unloadFrame();
        }
      }
    }
  }

  // Called when the accordion button is clicked
  /**
   * Handles click events on accordion buttons.
   * This function determines whether the accordion is being opened or closed
   * and triggers the appropriate frame loading or unloading action.
   * It checks the aria-expanded attribute of the clicked button to determine
   * the accordion's current state.
   *
   * @param {Event} event - The click event object from the accordion button
   * @returns {void}
   */
  handleAccordionClick(event) {
    console.log("Accordion button clicked");

    // Check if the accordion is currently closed (will be opened)
    const button = event.currentTarget;
    const isExpanded = button.getAttribute('aria-expanded') === 'true';
    console.log("Is expanded:", isExpanded);

    if (isExpanded) {
      console.log("Accordion is being opened, loading frame immediately");
      this.loadFrame();
    } else {
      console.log("Accordion is being closed, unloading frame");
      this.unloadFrame();
    }
  }

  // Load the frame content
  /**
   * Loads the content of a Turbo Frame.
   * This function checks if a Turbo Frame target exists and has a data-original-src attribute.
   * If so, it sets the src attribute to the value of data-original-src, which triggers
   * the frame to load its content. This is typically called when an accordion is opened
   * to load the content dynamically.
   * 
   * @returns {void} This function does not return a value
   */
  loadFrame() {
    console.log("Loading frame");

    if (!this.hasTurboFrameTarget) {
      console.warn("No turbo frame target found");
      return;
    }

    const frame = this.turboFrameTarget;
    console.log("Frame found:", frame);

    // If the frame has a data-original-src attribute but no src, set the src
    if (frame.hasAttribute('data-original-src')) {
      const originalSrc = frame.getAttribute('data-original-src');

      if (!frame.hasAttribute('src') || frame.getAttribute('src') !== originalSrc) {
        console.log("Setting src to:", originalSrc);
        frame.setAttribute('src', originalSrc);
      } else {
        console.log("Frame already has correct src, no change needed");
      }
    } else {
      console.warn("Frame does not have data-original-src attribute");
    }
  }

  // Unload the frame content
  /**
   * Unloads the content of a Turbo Frame.
   * This function checks if a Turbo Frame target exists and removes its src attribute
   * if present. It also clears the frame's content. This is typically called when an
   * accordion is closed to prevent unnecessary content loading and to free up resources.
   * 
   * @returns {void} This function does not return a value
   */
  unloadFrame() {
    console.log("Unloading frame");

    if (!this.hasTurboFrameTarget) {
      console.warn("No turbo frame target found");
      return;
    }

    const frame = this.turboFrameTarget;
    console.log("Frame found for unloading:", frame);

    // Simply remove the src attribute to prevent loading
    if (frame.hasAttribute('src')) {
      frame.removeAttribute('src');
      console.log("Removed src attribute");
      
      // Clear the frame content
      frame.innerHTML = '';
      console.log("Cleared frame content");
    } else {
      console.log("Frame does not have src attribute, nothing to unload");
    }
  }
}