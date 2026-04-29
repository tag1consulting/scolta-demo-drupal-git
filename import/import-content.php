<?php

/**
 * Imports GitMastery content from YAML files into Drupal.
 * Run with: ddev drush php:script import/import-content.php
 *
 * Pass --batch=1 (or 2, 3, 4) via environment variable BATCH to import specific batch:
 *   BATCH=1 ddev drush php:script import/import-content.php
 *   Or run without argument to import all available batches.
 */

use Drupal\node\Entity\Node;
use Symfony\Component\Yaml\Yaml;

$batch_arg = getenv('BATCH');

// Determine which files to process
$files = [];
if ($batch_arg) {
  $file = "/var/www/html/import/content-en-batch{$batch_arg}.yaml";
  if (file_exists($file)) {
    $files[] = $file;
  } else {
    echo "File not found: $file\n";
    exit(1);
  }
} else {
  foreach (glob('/var/www/html/import/content-en-batch*.yaml') as $f) {
    $files[] = $f;
  }
  sort($files);
}

if (empty($files)) {
  echo "No YAML files found to import.\n";
  exit(1);
}

// Load taxonomy term IDs for efficient lookup
$term_cache = [];

function get_term_id(string $vocabulary, string $name): ?int {
  global $term_cache;
  $key = "{$vocabulary}:{$name}";
  if (!isset($term_cache[$key])) {
    $terms = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['vid' => $vocabulary, 'name' => $name]);
    $term_cache[$key] = $terms ? reset($terms)->id() : NULL;
  }
  return $term_cache[$key];
}

function import_page(array $data, string $langcode = 'en'): ?Node {
  // Map content type
  $bundle_map = [
    'documentation_page' => 'documentation_page',
    'tutorial'           => 'tutorial',
    'comparison'         => 'comparison',
    'tip'                => 'tip',
  ];

  $bundle = $data['type'] ?? 'documentation_page';

  // Check for existing node by title + langcode
  $existing = \Drupal::entityTypeManager()
    ->getStorage('node')
    ->loadByProperties([
      'type'     => $bundle,
      'title'    => $data['title'],
      'langcode' => $langcode,
    ]);

  if ($existing) {
    $node = reset($existing);
    echo "  [skip] Already exists: {$data['title']}\n";
    return $node;
  }

  $values = [
    'type'     => $bundle,
    'title'    => $data['title'],
    'langcode' => $langcode,
    'status'   => 1,
    'uid'      => 1,
  ];

  // Body field
  if (!empty($data['body'])) {
    $values['body'] = [
      'value'  => $data['body'],
      'format' => 'full_html',
    ];
  }

  // Section taxonomy
  if (!empty($data['section'])) {
    $tid = get_term_id('section', $data['section']);
    if ($tid) {
      $values['field_section'] = [['target_id' => $tid]];
    }
  }

  // Difficulty taxonomy
  if (!empty($data['difficulty'])) {
    $tid = get_term_id('difficulty', $data['difficulty']);
    if ($tid) {
      $values['field_difficulty'] = [['target_id' => $tid]];
    }
  }

  // Category taxonomy (for tips)
  if (!empty($data['category'])) {
    $tid = get_term_id('topic', $data['category']);
    if ($tid) {
      $values['field_category'] = [['target_id' => $tid]];
    }
  }

  // Scalar fields
  if (!empty($data['git_version'])) {
    $values['field_git_version'] = $data['git_version'];
  }
  if (!empty($data['weight'])) {
    $values['field_weight'] = (int) $data['weight'];
  }
  if (!empty($data['estimated_time'])) {
    $values['field_estimated_time'] = $data['estimated_time'];
  }
  if (!empty($data['applies_to'])) {
    $values['field_applies_to'] = $data['applies_to'];
  }
  if (!empty($data['compared_systems'])) {
    $values['field_compared_systems'] = array_map(
      fn($s) => ['value' => $s],
      (array) $data['compared_systems']
    );
  }
  if (!empty($data['verdict'])) {
    $values['field_verdict'] = ['value' => $data['verdict'], 'format' => 'full_html'];
  }
  if (!empty($data['feature_table'])) {
    $values['field_feature_table'] = ['value' => $data['feature_table'], 'format' => 'full_html'];
  }

  $node = Node::create($values);
  $node->save();
  echo "  [created] {$data['title']} (nid:{$node->id()})\n";
  return $node;
}

$total = 0;
$created = 0;
$skipped = 0;

foreach ($files as $file) {
  echo "\n=== Importing: " . basename($file) . " ===\n";
  $raw = file_get_contents($file);
  $pages = Yaml::parse($raw);

  if (!is_array($pages)) {
    echo "ERROR: Could not parse $file\n";
    continue;
  }

  foreach ($pages as $page) {
    $total++;
    $existing_count_before = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('title', $page['title'])
      ->count()
      ->execute();

    import_page($page, 'en');

    $existing_count_after = \Drupal::entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('title', $page['title'])
      ->count()
      ->execute();

    if ($existing_count_after > $existing_count_before) {
      $created++;
    } else {
      $skipped++;
    }
  }
}

echo "\n=== Import Summary ===\n";
echo "Total processed: $total\n";
echo "Created: $created\n";
echo "Skipped (already existed): $skipped\n";
