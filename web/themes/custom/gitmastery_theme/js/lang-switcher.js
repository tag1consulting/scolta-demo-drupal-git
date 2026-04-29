(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var block = document.querySelector('.language-switcher-language-url');
    if (!block) { return; }

    block.addEventListener('click', function (e) {
      // Only toggle when clicking the active-language link (the trigger).
      if (!e.target.closest('.links li.is-active')) { return; }
      e.preventDefault();
      block.classList.toggle('is-open');
    });

    // Close on outside click.
    document.addEventListener('click', function (e) {
      if (!block.contains(e.target)) {
        block.classList.remove('is-open');
      }
    });

    // Close on Escape.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { block.classList.remove('is-open'); }
    });
  });
}());
