/**
 * @file
 * Search result cards for GitMastery.
 *
 * Registers two Scolta renderers: a result renderer that puts the section,
 * difficulty and Git-version facet values under each title as badges, and a
 * suggestion renderer that puts the section on each search-as-you-type row.
 * Both read from the search index — the labels ride along in the fragment's
 * meta map, put there by gitmastery_scolta_scolta_content_item_alter() — so
 * neither costs a per-result server call.
 *
 * There is no thumbnail here, on purpose. This corpus is Git documentation and
 * carries no per-item image: no populated image field, no managed files, not
 * one <img> in a body across 287 pages. A generic illustration on a page about
 * git-bisect would be decoration pretending to be information. The card earns
 * its keep by telling you what kind of page it is and how hard it is before
 * you click, which is the question a documentation search actually asks.
 *
 * Load order matters. scolta.js defines window.Scolta when it executes and
 * Drupal's scolta bridge behavior calls Scolta.init() on DOMContentLoaded, so
 * this file must run after the former and before the latter. Declaring
 * scolta/search as a dependency and leaving the library in the footer puts it
 * exactly there; registering at top level (not inside a DOMContentLoaded
 * handler) keeps it there.
 */
(function (global) {
  'use strict';

  if (!global.Scolta || typeof global.Scolta.setResultRenderer !== 'function') {
    // A bundle without the render seam is not something to work around here.
    console.warn('[gitmastery] Scolta.setResultRenderer unavailable; leaving the built-in card in place.');
    return;
  }

  var ENTITIES = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  };

  function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
      .replace(/[&<>"']/g, function (c) { return ENTITIES[c]; });
  }

  /**
   * How many badges a card paints. Mirrors the indexer's own cap, which is
   * what actually bounds the string; this is the client-side belt to it.
   */
  var BADGE_LIMIT = 3;

  /**
   * A Git version badge reads as a version, not as a category.
   *
   * The values in this facet are strings like "2.30+". Setting them in the
   * theme's mono face next to the sans-serif section and difficulty badges is
   * what tells a reader at a glance that the third badge is a different kind
   * of fact from the first two, without a label saying so.
   */
  var VERSION_PATTERN = /^\d+(\.\d+)*\+?$/;

  /**
   * Renders a result's facet badges.
   *
   * data.meta.badges is raw index data: a JSON-encoded array of facet values,
   * already ordered and capped by
   * gitmastery_scolta_scolta_content_item_alter(). JSON and not a delimited
   * string because a taxonomy label is free text, so there is no separator a
   * future label provably cannot contain.
   *
   * Anything that does not parse into an array counts as no badges. A page
   * without them simply shows none, rather than a broken card.
   */
  function badges(encoded) {
    if (!encoded) {
      return '';
    }
    var labels;
    try {
      labels = JSON.parse(encoded);
    } catch (e) {
      return '';
    }
    if (!Array.isArray(labels)) {
      return '';
    }
    var out = '';
    for (var i = 0; i < labels.length && i < BADGE_LIMIT; i++) {
      var label = String(labels[i] === null || labels[i] === undefined ? '' : labels[i]).trim();
      if (label !== '') {
        var cls = 'gitmastery-result__badge'
          + (VERSION_PATTERN.test(label) ? ' gitmastery-result__badge--version' : '');
        out += '<span class="' + cls + '">' + escapeHtml(label) + '</span>';
      }
    }
    return out;
  }

  /**
   * Renders one result.
   *
   * Escaping: every ctx value used here ends in Html, Attr or Text, or is
   * safeUrl, so Scolta has already escaped it exactly as its own card would.
   * Everything read from data.meta is raw index data and is escaped here.
   * ctx.query and ctx.highlightTerms are raw and never reach the markup.
   *
   * A page with no badges gets this same card with an empty meta row, never
   * Scolta's built-in one. Mixing two card designs down a single result list
   * reads as a broken page rather than a designed fallback.
   */
  global.Scolta.setResultRenderer(function (data, ctx) {
    var meta = (data && data.meta) || {};
    var badgeHtml = badges(meta.badges);

    var metaRow = '';
    if (ctx.dateHtml || badgeHtml) {
      metaRow = '<div class="gitmastery-result__meta">'
        + badgeHtml
        + (ctx.dateHtml ? '<span class="gitmastery-result__date">' + ctx.dateHtml + '</span>' : '')
        + '</div>';
    }

    // target/rel match the built-in card: within one result list, no card may
    // open differently from its neighbour.
    return '<div class="scolta-result-card gitmastery-result">'
      + '<a class="scolta-result-title gitmastery-result__title" href="' + ctx.safeUrl + '"'
      + ' target="_blank" rel="noopener" title="' + ctx.titleAttr + '">' + ctx.titleHtml + '</a>'
      + metaRow
      + '<div class="scolta-result-excerpt gitmastery-result__excerpt">' + ctx.excerptHtml + '</div>'
      + '</div>';
  });

  // Behind its own guard rather than the file-level one: this seam landed
  // after setResultRenderer, so a bundle old enough to lack it still gets the
  // cards above, and the dropdown degrades to the themed but badgeless rows
  // instead of throwing.
  if (typeof global.Scolta.setSuggestionRenderer !== 'function') {
    return;
  }

  /**
   * Renders one search-as-you-type suggestion row.
   *
   * Returns the row's INNER markup only. The option element around it is the
   * bundle's, and it is what carries the combobox contract — role="option",
   * the stable id the input's aria-activedescendant points at, aria-selected,
   * the data-scolta-sayt-index the keyboard and click handlers dispatch on,
   * and the href in navigate mode. None of that is restated here, because a
   * renderer cannot break by omission what it never writes.
   *
   * Where a demo with pictures puts a thumbnail, this row puts the section.
   * In a dropdown the useful question is what kind of page a hit is — a
   * tutorial, a command reference, a tip — and on this corpus that is the only
   * per-item fact worth the width. Rows with no section still reserve the
   * column, so the titles stay aligned down the list.
   *
   * Escaping: ctx.titleHtml and ctx.excerptHtml arrive pre-escaped, escaped
   * exactly as the built-in row escapes them. suggestion.meta.* is raw index
   * data and is escaped here. ctx.query is raw and never reaches the markup.
   *
   * A recent search is handed back to the built-in row by returning null: it
   * has no fragment, no section and nothing to add, and the built-in row is
   * already the themed glyph treatment this dropdown wants for history.
   */
  global.Scolta.setSuggestionRenderer(function (suggestion, ctx) {
    if (!suggestion || suggestion.type !== 'title') {
      return null;
    }

    var meta = suggestion.meta || {};
    var section = String(meta.section || '').trim();

    // Decorative and aria-hidden: an option's accessible name is computed from
    // its contents, so this would otherwise be announced in front of the title
    // it qualifies — "Commands Reference, git-rebase". The title names the row.
    var tag = section === ''
      ? '<span class="gitmastery-sayt__section gitmastery-sayt__section--empty" aria-hidden="true"></span>'
      : '<span class="gitmastery-sayt__section" aria-hidden="true">' + escapeHtml(section) + '</span>';

    return '<span class="gitmastery-sayt">'
      + tag
      // Both classes on purpose. The scolta-* one carries the look the theme
      // already gives a suggestion's title and excerpt, so a title row and a
      // recent-search row stay typographically identical; the gitmastery-* one
      // adds only the layout this row needs. Two classes at the same
      // specificity, resolved by source order, rather than a nested selector.
      + '<span class="gitmastery-sayt__text">'
      + '<span class="scolta-sayt-title gitmastery-sayt__title">' + ctx.titleHtml + '</span>'
      + (ctx.excerptHtml
        ? '<span class="scolta-sayt-excerpt gitmastery-sayt__excerpt">' + ctx.excerptHtml + '</span>'
        : '')
      + '</span>'
      + '</span>';
  });

})(window);
