// Initialize Split.js when DOM is ready
import Split from 'split.js';
document.addEventListener('DOMContentLoaded', function () {
    // Get saved sizes or use defaults
    const savedSizes = localStorage.getItem('splitSizes');
    const sizes = savedSizes ? JSON.parse(savedSizes) : [70, 30];

    Split(['#split-left', '#split-right'], {
        sizes: sizes,
        minSize: 0,
        gutterSize: 8,
        onDragEnd: function (newSizes) {
            localStorage.setItem('splitSizes', JSON.stringify(newSizes));
        }
    });
});