<?php

/**
 * Configure language negotiation (URL prefix) and pathauto patterns.
 * Run with: ddev drush php:script import/setup-language-pathauto.php
 */

// Enable URL-based language negotiation
$language_types = \Drupal::configFactory()->getEditable('language.types');
$language_types->set('negotiation.language_interface.enabled', [
  'language-url' => -8,
  'language-selected' => 12,
])->save();

// Configure URL prefix method with all five languages
$negotiation = \Drupal::configFactory()->getEditable('language.negotiation');
$negotiation->set('url.source', 'path_prefix');
$negotiation->set('url.prefixes', [
  'en' => 'en',
  'es' => 'es',
  'fr' => 'fr',
  'it' => 'it',
  'de' => 'de',
])->save();

echo "Language URL prefix negotiation configured\n";

// Enable URL negotiation for content language as well
$language_types->set('negotiation.language_content.enabled', [
  'language-url' => -8,
  'language-selected' => 12,
])->save();

// Create Pathauto patterns for each content type
$patterns = [
  'documentation_page' => '/[node:langcode]/docs/[node:field_section:name:slug]/[node:title]',
  'tutorial' => '/[node:langcode]/tutorials/[node:title]',
  'comparison' => '/[node:langcode]/comparisons/[node:title]',
  'tip' => '/[node:langcode]/tips/[node:title]',
];

foreach ($patterns as $bundle => $pattern) {
  $id = "node_{$bundle}";
  $existing = \Drupal::entityTypeManager()
    ->getStorage('pathauto_pattern')
    ->load($id);

  if ($existing) {
    echo "Pathauto pattern already exists for: $bundle\n";
    continue;
  }

  \Drupal\pathauto\Entity\PathautoPattern::create([
    'id' => $id,
    'label' => ucfirst(str_replace('_', ' ', $bundle)),
    'type' => 'canonical_entities:node',
    'pattern' => $pattern,
    'selection_criteria' => [
      'ad97b4ec-7d5a-4b07-8277-d2b02b4d857e' => [
        'id' => 'entity_bundle:node',
        'bundles' => [$bundle => $bundle],
        'negate' => FALSE,
        'context_mapping' => ['node' => 'node'],
      ],
    ],
    'selection_logic' => 'and',
    'weight' => 0,
    'enabled' => TRUE,
  ])->save();

  echo "Created pathauto pattern for: $bundle -> $pattern\n";
}

echo "\nLanguage and pathauto setup complete!\n";
