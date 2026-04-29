<?php

/**
 * Imports translations from YAML files into Drupal node translations.
 * Run with: ddev drush php:script import/import-translations.php
 *
 * Set LANG env var to import a specific language:
 *   LANG=es ddev drush php:script import/import-translations.php
 * Or omit to import all available translation files.
 *
 * Translation YAML format:
 * - title: "Translated title"
 *   langcode: es
 *   source_title: "English source title"  # used to find the source node
 *   body: "<h2>...</h2><p>...</p>"
 */

use Drupal\node\Entity\Node;
use Symfony\Component\Yaml\Yaml;

$lang_arg = getenv('LANGCODE') ?: NULL;

// Validate langcode is one we support
$valid_langs = ['es', 'fr', 'it', 'de'];
if ($lang_arg && !in_array($lang_arg, $valid_langs)) {
  echo "Invalid LANGCODE: $lang_arg. Must be one of: " . implode(', ', $valid_langs) . "\n";
  exit(1);
}

// Determine which files to process
$trans_dir = '/var/www/html/import/translations';
if ($lang_arg) {
  $files = glob("{$trans_dir}/content-{$lang_arg}-batch*.yaml");
  sort($files);
} else {
  $files = glob("{$trans_dir}/content-*-batch*.yaml");
  sort($files);
}

if (empty($files)) {
  echo "No translation YAML files found.\n";
  exit(1);
}

// Build a title->node ID map for efficient lookup
echo "Building node title index...\n";
$title_map = [];
$nids = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->getQuery()
  ->accessCheck(FALSE)
  ->condition('langcode', 'en')
  ->execute();

$nodes = Node::loadMultiple($nids);
foreach ($nodes as $node) {
  $title_map[$node->label()] = $node->id();
}
echo "Indexed " . count($title_map) . " English nodes.\n";

$total = 0;
$created = 0;
$updated = 0;
$skipped = 0;
$errors = 0;

foreach ($files as $file) {
  echo "\n=== Importing: " . basename($file) . " ===\n";
  $raw = file_get_contents($file);
  $translations = Yaml::parse($raw);

  if (!is_array($translations)) {
    echo "ERROR: Could not parse $file\n";
    $errors++;
    continue;
  }

  foreach ($translations as $trans) {
    $total++;
    $langcode = $trans['langcode'] ?? NULL;
    $source_title = $trans['source_title'] ?? NULL;
    $trans_title = $trans['title'] ?? NULL;

    if (!$langcode || !$source_title || !$trans_title) {
      echo "  [error] Missing required fields in entry\n";
      $errors++;
      continue;
    }

    if (!isset($title_map[$source_title])) {
      echo "  [error] Source node not found: $source_title\n";
      $errors++;
      continue;
    }

    $nid = $title_map[$source_title];
    $node = Node::load($nid);

    if (!$node) {
      echo "  [error] Could not load node $nid\n";
      $errors++;
      continue;
    }

    // Check if translation already exists
    if ($node->hasTranslation($langcode)) {
      $trans_node = $node->getTranslation($langcode);
      $trans_node->setTitle($trans_title);
      if (!empty($trans['body'])) {
        $trans_node->body->value = $trans['body'];
        $trans_node->body->format = 'full_html';
      }
      if (!empty($trans['verdict'])) {
        $trans_node->field_verdict->value = $trans['verdict'];
        $trans_node->field_verdict->format = 'full_html';
      }
      if (!empty($trans['feature_table'])) {
        $trans_node->field_feature_table->value = $trans['feature_table'];
        $trans_node->field_feature_table->format = 'full_html';
      }
      $node->save();
      echo "  [updated] [$langcode] $trans_title\n";
      $updated++;
    } else {
      // Add new translation
      $trans_node = $node->addTranslation($langcode, [
        'title' => $trans_title,
        'status' => 1,
        'body' => [
          'value' => $trans['body'] ?? '',
          'format' => 'full_html',
        ],
      ]);

      if (!empty($trans['verdict'])) {
        $trans_node->field_verdict->value = $trans['verdict'];
        $trans_node->field_verdict->format = 'full_html';
      }
      if (!empty($trans['feature_table'])) {
        $trans_node->field_feature_table->value = $trans['feature_table'];
        $trans_node->field_feature_table->format = 'full_html';
      }

      $node->save();
      echo "  [created] [$langcode] $trans_title\n";
      $created++;
    }
  }
}

echo "\n=== Translation Import Summary ===\n";
echo "Total processed: $total\n";
echo "Created: $created\n";
echo "Updated: $updated\n";
echo "Errors: $errors\n";
