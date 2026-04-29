(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var block = document.querySelector('.language-switcher-language-url');
    if (!block) { return; }

    var list = block.querySelector('.links');
    if (!list) { return; }

    var activeItem = list.querySelector('li.is-active');
    var activeLink = activeItem ? activeItem.querySelector('a') : null;
    if (!activeLink) { return; }

    // Create a separate in-flow trigger button so the panel (ul.links) can
    // always be position:absolute — no layout shift when it opens/closes.
    var trigger = document.createElement('button');
    trigger.className = 'lang-trigger';
    trigger.setAttribute('type', 'button');
    trigger.setAttribute('aria-expanded', 'false');
    // Show the 2-letter language code (e.g. "EN") extracted from the link
    // href (e.g. "/en", "/fr/taxonomy/…"). Falls back to the link text.
    var href = activeLink.getAttribute('href') || '';
    var match = href.match(/^\/([a-z]{2})(?:\/|$)/);
    trigger.textContent = match ? match[1].toUpperCase() : activeLink.textContent.trim();
    list.parentNode.insertBefore(trigger, list);

    // The active language is already the trigger — hide it from the panel.
    activeItem.hidden = true;

    function close() {
      block.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
    }

    function open() {
      block.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
    }

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      block.classList.contains('is-open') ? close() : open();
    });

    // Close on outside click.
    document.addEventListener('click', function (e) {
      if (!block.contains(e.target)) { close(); }
    });

    // Close on Escape.
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') { close(); }
    });
  });
}());
