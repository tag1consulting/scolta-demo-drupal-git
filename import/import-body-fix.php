<?php

/**
 * One-time fix: populate the body field on all nodes from YAML source files.
 * Run with: ddev drush php:script import/import-body-fix.php
 */

use Drupal\node\Entity\Node;
use Symfony\Component\Yaml\Yaml;

$files = glob('/var/www/html/import/content-en-batch*.yaml');
sort($files);

$updated = 0;
$skipped = 0;

foreach ($files as $file) {
  echo "Processing " . basename($file) . "...\n";
  $pages = Yaml::parseFile($file);
  foreach ($pages as $page) {
    if (empty($page['body'])) { $skipped++; continue; }
    $nodes = \Drupal::entityTypeManager()->getStorage('node')
      ->loadByProperties(['title' => $page['title'], 'langcode' => 'en']);
    if (empty($nodes)) { $skipped++; continue; }
    $node = reset($nodes);
    $node->body->value = $page['body'];
    $node->body->format = 'full_html';
    $node->save();
    $updated++;
  }
}
echo "Updated: $updated, Skipped: $skipped\n";
