<?php
/**
 * Creates the "About This Demo" page for GitMastery.
 * Run with: ddev drush php:script import/setup-about-page.php
 * Idempotent: skips creation if the page already exists.
 */

use Drupal\node\Entity\Node;

$existing = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->loadByProperties(['type' => 'page', 'title' => 'About This Demo']);

if ($existing) {
  echo "About This Demo page already exists — skipping.\n";
  return;
}

$body = <<<'HTML'
<h2>About This Site</h2>
<p><strong>GitMastery is a fictional website.</strong> It was created by Tag1 Consulting to demonstrate the capabilities of Scolta, an open-source AI-powered search platform, on a content-rich technical reference site built with Drupal 11.</p>

<h2>What You Are Looking At</h2>
<p>This site is a Drupal 11 demonstration built to show how Scolta performs on a large, multilingual technical documentation site. The site contains 285 pages of English reference content across categories including:</p>
<ul>
  <li>Getting started guides and installation instructions</li>
  <li>Core Git concepts: branching, merging, rebasing, history</li>
  <li>Advanced workflows: bisect, reflog, worktrees, submodules, hooks</li>
  <li>Comparison tables between similar commands and approaches</li>
  <li>Quick-reference tips and common mistake guides</li>
</ul>
<p>All content is available in five languages: English, German, Spanish, French, and Italian, demonstrating Scolta's multilingual search capabilities.</p>

<h2>What Scolta Does Here</h2>
<p>The search bar at the top of this page uses Scolta to let you explore the Git documentation by asking natural language questions. Try asking things like:</p>
<ul>
  <li>"How do I undo the last commit without losing my changes?"</li>
  <li>"What is the difference between git merge and git rebase?"</li>
  <li>"How does git stash work?"</li>
  <li>"How do I resolve a merge conflict?"</li>
  <li>"What does HEAD detached mean?"</li>
</ul>
<p>Scolta uses Pagefind for full-text indexing, Claude via the Anthropic API for query expansion and AI-generated overviews, and a custom BM25-based scoring layer. The result is a search experience that understands what you are asking, not just which keywords you used.</p>

<h2>About Tag1 Consulting</h2>
<p>Tag1 Consulting is one of the leading Drupal development and consulting firms in the world. Tag1 built and open-sources Scolta as a demonstration of what AI-augmented content discovery can look like on modern Drupal sites. For more information about Tag1 and Scolta, visit <a href="https://tag1.com">tag1.com</a>.</p>

<h2>Reuse and Attribution</h2>
<p>The Git reference content on this site was written to be technically accurate and practically useful. If you find a page helpful for learning Git, you are welcome to use it. If you are evaluating Scolta for your organization and have questions about how this demo was built or how to implement Scolta for your use case, contact Tag1 Consulting.</p>
HTML;

$node = Node::create([
  'type'     => 'page',
  'title'    => 'About This Demo',
  'langcode' => 'en',
  'status'   => 1,
  'uid'      => 1,
  'body'     => ['value' => $body, 'format' => 'full_html'],
  'path'     => [['alias' => '/about/demo']],
]);
$node->save();

echo "Created 'About This Demo' at /about/demo (node/" . $node->id() . ")\n";
