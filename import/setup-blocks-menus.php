<?php

/**
 * Configure blocks: language switcher, navigation, breadcrumb.
 * Run with: ddev drush php:script import/setup-blocks-menus.php
 */

use Drupal\block\Entity\Block;

// Place language switcher block in header region
$lang_block_id = 'gitmastery_theme_languageswitcher';
if (!Block::load($lang_block_id)) {
  Block::create([
    'id' => $lang_block_id,
    'plugin' => 'language_block:language_interface',
    'theme' => 'gitmastery_theme',
    'region' => 'header',
    'weight' => 10,
    'settings' => [
      'label' => 'Language switcher',
      'label_display' => '0',
      'id' => 'language_block:language_interface',
      'provider' => 'language',
    ],
    'visibility' => [],
  ])->save();
  echo "Created language switcher block\n";
} else {
  echo "Language switcher block already exists\n";
}

// Place primary navigation in header
$nav_block_id = 'gitmastery_theme_main_navigation';
if (!Block::load($nav_block_id)) {
  Block::create([
    'id' => $nav_block_id,
    'plugin' => 'system_menu_block:main',
    'theme' => 'gitmastery_theme',
    'region' => 'primary_menu',
    'weight' => 0,
    'settings' => [
      'label' => 'Main navigation',
      'label_display' => '0',
      'level' => 1,
      'depth' => 0,
      'expand_all_items' => FALSE,
    ],
    'visibility' => [],
  ])->save();
  echo "Created main navigation block\n";
}

// Place breadcrumb
$bc_block_id = 'gitmastery_theme_breadcrumbs';
if (!Block::load($bc_block_id)) {
  Block::create([
    'id' => $bc_block_id,
    'plugin' => 'system_breadcrumb_block',
    'theme' => 'gitmastery_theme',
    'region' => 'breadcrumb',
    'weight' => 0,
    'settings' => [
      'label' => 'Breadcrumbs',
      'label_display' => '0',
    ],
    'visibility' => [],
  ])->save();
  echo "Created breadcrumb block\n";
}

// Place page title
$title_block_id = 'gitmastery_theme_page_title';
if (!Block::load($title_block_id)) {
  Block::create([
    'id' => $title_block_id,
    'plugin' => 'page_title_block',
    'theme' => 'gitmastery_theme',
    'region' => 'content',
    'weight' => -5,
    'settings' => [
      'label' => 'Page title',
      'label_display' => '0',
    ],
    'visibility' => [],
  ])->save();
  echo "Created page title block\n";
}

// Place site branding block in header
$branding_id = 'gitmastery_theme_branding';
if (!Block::load($branding_id)) {
  Block::create([
    'id' => $branding_id,
    'plugin' => 'system_branding_block',
    'theme' => 'gitmastery_theme',
    'region' => 'header',
    'weight' => -10,
    'settings' => [
      'label' => 'Site branding',
      'label_display' => '0',
      'use_site_logo' => FALSE,
      'use_site_name' => TRUE,
      'use_site_slogan' => FALSE,
    ],
    'visibility' => [],
  ])->save();
  echo "Created site branding block\n";
}

// Update site name to "GitMastery"
\Drupal::configFactory()->getEditable('system.site')
  ->set('name', 'GitMastery')
  ->set('slogan', 'The complete guide to Git')
  ->save();
echo "Updated site name\n";

// Set front page to /en/docs
\Drupal::configFactory()->getEditable('system.site')
  ->set('page.front', '/node/1')
  ->save();

echo "\nBlocks and site settings configured!\n";
