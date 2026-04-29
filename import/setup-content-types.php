<?php

/**
 * Setup script: Creates content types, fields, and taxonomies for GitMastery.
 * Run with: ddev drush php:script import/setup-content-types.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\taxonomy\Entity\Term;
use Drupal\language\Entity\ConfigurableLanguage;

// ============================================================
// TAXONOMIES
// ============================================================

$vocabularies = [
  'section' => [
    'label' => 'Section',
    'terms' => [
      'Getting Started', 'Core Concepts', 'Commands Reference',
      'Advanced', 'Performance', 'Tips', 'Comparisons', 'Tutorials',
    ],
  ],
  'difficulty' => [
    'label' => 'Difficulty',
    'terms' => ['Beginner', 'Intermediate', 'Advanced', 'Expert'],
  ],
  'topic' => [
    'label' => 'Topic',
    'terms' => [
      'Branching', 'Merging', 'Remotes', 'History', 'Undoing',
      'Configuration', 'Performance', 'Hooks', 'Large Repos', 'Migration',
    ],
  ],
];

foreach ($vocabularies as $machine_name => $info) {
  if (!Vocabulary::load($machine_name)) {
    Vocabulary::create([
      'vid' => $machine_name,
      'name' => $info['label'],
    ])->save();
    echo "Created vocabulary: {$info['label']}\n";
  } else {
    echo "Vocabulary already exists: {$info['label']}\n";
  }

  foreach ($info['terms'] as $term_name) {
    $existing = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties(['name' => $term_name, 'vid' => $machine_name]);
    if (empty($existing)) {
      Term::create([
        'vid' => $machine_name,
        'name' => $term_name,
        'langcode' => 'en',
      ])->save();
      echo "  Created term: $term_name\n";
    }
  }
}

// ============================================================
// HELPER: Create a text field
// ============================================================
function create_text_field(string $entity_type, string $bundle, string $field_name, string $label, string $type = 'string', bool $translatable = true, int $cardinality = 1): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $type,
      'cardinality' => $cardinality,
      'translatable' => $translatable,
    ])->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'translatable' => $translatable,
    ])->save();
    echo "  Field created: $field_name on $bundle\n";
  }
}

// ============================================================
// HELPER: Create a taxonomy reference field
// ============================================================
function create_taxonomy_field(string $entity_type, string $bundle, string $field_name, string $label, string $vocabulary, int $cardinality = 1): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'entity_reference',
      'cardinality' => $cardinality,
      'settings' => ['target_type' => 'taxonomy_term'],
      'translatable' => FALSE,
    ])->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'translatable' => FALSE,
      'settings' => [
        'handler' => 'default:taxonomy_term',
        'handler_settings' => [
          'target_bundles' => [$vocabulary => $vocabulary],
          'auto_create' => FALSE,
        ],
      ],
    ])->save();
    echo "  Field created: $field_name on $bundle\n";
  }
}

// ============================================================
// HELPER: Create a node reference field
// ============================================================
function create_node_ref_field(string $entity_type, string $bundle, string $field_name, string $label, array $target_bundles, int $cardinality = -1): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => 'entity_reference',
      'cardinality' => $cardinality,
      'settings' => ['target_type' => 'node'],
      'translatable' => FALSE,
    ])->save();
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'translatable' => FALSE,
      'settings' => [
        'handler' => 'default:node',
        'handler_settings' => [
          'target_bundles' => array_combine($target_bundles, $target_bundles),
          'auto_create' => FALSE,
        ],
      ],
    ])->save();
    echo "  Field created: $field_name on $bundle\n";
  }
}

// ============================================================
// CONTENT TYPE: documentation_page
// ============================================================
if (!NodeType::load('documentation_page')) {
  NodeType::create([
    'type' => 'documentation_page',
    'name' => 'Documentation Page',
    'description' => 'A Git documentation page.',
  ])->save();
  echo "Created content type: documentation_page\n";
}

// Enable translation for this content type
\Drupal::service('content_translation.manager')->setEnabled('node', 'documentation_page', TRUE);

create_taxonomy_field('node', 'documentation_page', 'field_section', 'Section', 'section');
create_taxonomy_field('node', 'documentation_page', 'field_difficulty', 'Difficulty', 'difficulty');
create_text_field('node', 'documentation_page', 'field_git_version', 'Git Version', 'string', FALSE);
create_node_ref_field('node', 'documentation_page', 'field_related_commands', 'Related Commands', ['documentation_page']);
create_node_ref_field('node', 'documentation_page', 'field_related_pages', 'Related Pages', ['documentation_page']);
create_text_field('node', 'documentation_page', 'field_weight', 'Weight', 'integer', FALSE);

// ============================================================
// CONTENT TYPE: tutorial
// ============================================================
if (!NodeType::load('tutorial')) {
  NodeType::create([
    'type' => 'tutorial',
    'name' => 'Tutorial',
    'description' => 'A hands-on Git tutorial.',
  ])->save();
  echo "Created content type: tutorial\n";
}
\Drupal::service('content_translation.manager')->setEnabled('node', 'tutorial', TRUE);

create_node_ref_field('node', 'tutorial', 'field_prerequisites', 'Prerequisites', ['documentation_page', 'tutorial']);
create_text_field('node', 'tutorial', 'field_estimated_time', 'Estimated Time', 'string', FALSE);
create_taxonomy_field('node', 'tutorial', 'field_difficulty', 'Difficulty', 'difficulty');

// ============================================================
// CONTENT TYPE: comparison
// ============================================================
if (!NodeType::load('comparison')) {
  NodeType::create([
    'type' => 'comparison',
    'name' => 'Comparison',
    'description' => 'A comparison between Git and another VCS.',
  ])->save();
  echo "Created content type: comparison\n";
}
\Drupal::service('content_translation.manager')->setEnabled('node', 'comparison', TRUE);

create_text_field('node', 'comparison', 'field_compared_systems', 'Compared Systems', 'string', FALSE, -1);
create_text_field('node', 'comparison', 'field_feature_table', 'Feature Comparison Table', 'text_long', TRUE);
create_text_field('node', 'comparison', 'field_verdict', 'Verdict', 'text_long', TRUE);

// ============================================================
// CONTENT TYPE: tip
// ============================================================
if (!NodeType::load('tip')) {
  NodeType::create([
    'type' => 'tip',
    'name' => 'Tip',
    'description' => 'A Git tip or trick.',
  ])->save();
  echo "Created content type: tip\n";
}
\Drupal::service('content_translation.manager')->setEnabled('node', 'tip', TRUE);

create_taxonomy_field('node', 'tip', 'field_category', 'Category', 'topic');
create_taxonomy_field('node', 'tip', 'field_difficulty', 'Difficulty', 'difficulty');
create_text_field('node', 'tip', 'field_applies_to', 'Applies To', 'string', FALSE);

// ============================================================
// PATHAUTO PATTERNS
// ============================================================
\Drupal::service('pathauto.generator'); // ensure service is loaded

// Set language prefix negotiation
$config = \Drupal::configFactory()->getEditable('language.negotiation');
$methods = $config->get('url.prefixes');
if (empty($methods)) {
  $config->set('url.prefixes', [
    'en' => 'en',
    'es' => 'es',
    'fr' => 'fr',
    'it' => 'it',
    'de' => 'de',
  ])->save();
  echo "Configured language URL prefixes\n";
}

echo "\nContent types and taxonomies setup complete!\n";
