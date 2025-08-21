import Split from 'split.js';

console.log('Dynamic Iframe script loaded.');
document.addEventListener('turbo:load', function () {
    console.log('Initializing Split.js...');


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