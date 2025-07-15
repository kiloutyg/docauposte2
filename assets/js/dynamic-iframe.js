// Initialize Split.js when DOM is ready
import Split from 'split.js';
/**
 * Initializes Split.js when the DOM is ready.
 * Saves and restores the split pane sizes using localStorage.
 *
 * @listens document:DOMContentLoaded
 */
document.addEventListener('DOMContentLoaded', function () {
    // Get saved sizes or use defaults
    /**
     * @type {string|null} savedSizes - The saved split pane sizes from localStorage.
     */
    const savedSizes = localStorage.getItem('splitSizes');

    /**
     * @type {Array<number>} sizes - The split pane sizes to be used.
     * If savedSizes is not null, parse it as JSON. Otherwise, use default sizes [70, 30].
     */
    const sizes = savedSizes ? JSON.parse(savedSizes) : [70, 30];

    /**
     * Initialize Split.js with the specified options.
     *
     * @param {Array<string>} elements - The CSS selectors of the split panes.
     * @param {Object} options - The Split.js options.
     * @param {Array<number>} options.sizes - The initial sizes of the split panes.
     * @param {number} options.minSize - The minimum size of a split pane.
     * @param {number} options.gutterSize - The size of the gutter between split panes.
     * @param {function} options.onDragEnd - The callback function to be executed after a drag ends.
     */
    Split(['#split-left', '#split-right'], {
        sizes: sizes,
        minSize: 0,
        gutterSize: 8,
        onDragEnd: function (newSizes) {
            /**
             * Save the new split pane sizes to localStorage.
             *
             * @param {string} key - The key to store the data.
             * @param {string} value - The data to be stored.
             */
            localStorage.setItem('splitSizes', JSON.stringify(newSizes));
        }
    });
});