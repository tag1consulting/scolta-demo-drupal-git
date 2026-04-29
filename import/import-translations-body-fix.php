<?php

/**
 * One-time fix: populate body on translation nodes.
 * Run with: ddev drush php:script import/import-translations-body-fix.php
 */

use Drupal\node\Entity\Node;
use Symfony\Component\Yaml\Yaml;

$trans_dir = '/var/www/html/import/translations';
$files = glob("{$trans_dir}/content-*-batch*.yaml");
sort($files);

$updated = 0;
$skipped = 0;

// Build title→nid map
$nids = \Drupal::entityTypeManager()->getStorage('node')->getQuery()
  ->accessCheck(FALSE)->condition('langcode', 'en')->execute();
$nodes = Node::loadMultiple($nids);
$title_map = [];
foreach ($nodes as $node) { $title_map[$node->label()] = $node->id(); }

foreach ($files as $file) {
  echo "Processing " . basename($file) . "...\n";
  $translations = Yaml::parseFile($file);
  foreach ($translations as $trans) {
    $langcode = $trans['langcode'] ?? NULL;
    $source_title = $trans['source_title'] ?? NULL;
    $body = $trans['body'] ?? NULL;
    if (!$langcode || !$source_title || !$body) { $skipped++; continue; }
    if (!isset($title_map[$source_title])) { $skipped++; continue; }
    $node = Node::load($title_map[$source_title]);
    if (!$node || !$node->hasTranslation($langcode)) { $skipped++; continue; }
    $t = $node->getTranslation($langcode);
    $t->body->value = $body;
    $t->body->format = 'full_html';
    $node->save();
    $updated++;
  }
}
echo "Updated: $updated, Skipped: $skipped\n";
