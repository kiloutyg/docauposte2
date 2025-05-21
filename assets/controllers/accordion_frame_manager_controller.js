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
  
  disconnect() {
    // Clean up event listener
    document.removeEventListener('show.bs.collapse', this.handleOtherAccordionOpen.bind(this));
  }
  
  // Handle when other accordions are opened
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