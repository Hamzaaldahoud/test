<?php

namespace Drupal\Tests\varbase_api\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API Settings test.
 *
 * @group varbase_api
 */
class VarbaseApiSettingsTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'olivero';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'varbase_api',
    'node',
    'taxonomy',
    'media',
    'user',
    'block',
    'block_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Insall the Claro admin theme.
    $this->container->get('theme_installer')->install(['claro']);

    // Set the Claro theme as the default admin theme.
    $this->config('system.theme')->set('admin', 'claro')->save();

  }

  /**
   * Check Varbase API Settings.
   */
  public function testCheckVarbaseApiSettings() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // Varbase API settings.
    $this->drupalGet('admin/config/system/varbase/api');

    $page = $this->getSession()->getPage();

    $varbase_api_settings_text = $this->t('Varbase API settings');
    $this->assertSession()->pageTextContains($varbase_api_settings_text);

    $expose_view_json_text = $this->t('Expose a "View JSON" link in entity operations');
    $this->assertSession()->pageTextContains($expose_view_json_text);
    $entity_json = $page->findField('entity_json');
    $this->assertNotEmpty($entity_json);
    $this->assertTrue($entity_json->isChecked());

    $expose_view_api_doc_text = $this->t('Expose a "View API Documentation" link in bundle entity operations');
    $this->assertSession()->pageTextContains($expose_view_api_doc_text);
    $bundle_docs = $page->findField('bundle_docs');
    $this->assertNotEmpty($bundle_docs);
    $this->assertTrue($bundle_docs->isChecked());

    $auto_enable = $this->t('Auto Enabled JSON:API Endpoints for Entity Types');
    $this->assertSession()->pageTextContains($auto_enable);

  }

  /**
   * Check Varbase API Generate keys.
   */
  public function testCheckVarbaseApiGenerateKeys() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // Generate keys.
    $this->drupalGet('admin/config/system/varbase/api/keys');

    $generate_keys_text = $this->t('Generate keys');
    $this->assertSession()->pageTextContains($generate_keys_text);

    $destination_text = $this->t('Destination');
    $this->assertSession()->pageTextContains($destination_text);

  }

  /**
   * Check Varbase API Auto Enabled JSON:API Endpoints for Entity Types.
   */
  public function testCheckVarbaseApiAutoEnabledJsonApiEndpoints() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    $this->drupalGet('admin/config/system/varbase/api');

    $page = $this->getSession()->getPage();

    // Content type is auto enabled.
    $auto_enabled_entity_types_node_type = $page->findField('auto_enabled_entity_types[node_type]');
    $this->assertNotEmpty($auto_enabled_entity_types_node_type);
    $this->assertTrue($auto_enabled_entity_types_node_type->isChecked());

    // Content is auto enabled.
    $auto_enabled_entity_types_node = $page->findField('auto_enabled_entity_types[node]');
    $this->assertNotEmpty($auto_enabled_entity_types_node);
    $this->assertTrue($auto_enabled_entity_types_node->isChecked());

    // Taxonomy vocabulary is auto enabled.
    $auto_enabled_entity_types_taxonomy_vocabulary = $page->findField('auto_enabled_entity_types[taxonomy_vocabulary]');
    $this->assertNotEmpty($auto_enabled_entity_types_taxonomy_vocabulary);
    $this->assertTrue($auto_enabled_entity_types_taxonomy_vocabulary->isChecked());

    // Taxonomy term is auto enabled.
    $auto_enabled_entity_types_taxonomy_term = $page->findField('auto_enabled_entity_types[taxonomy_term]');
    $this->assertNotEmpty($auto_enabled_entity_types_taxonomy_term);
    $this->assertTrue($auto_enabled_entity_types_taxonomy_term->isChecked());

    // Media type is auto enabled.
    $auto_enabled_entity_types_media_type = $page->findField('auto_enabled_entity_types[media_type]');
    $this->assertNotEmpty($auto_enabled_entity_types_media_type);
    $this->assertTrue($auto_enabled_entity_types_media_type->isChecked());

    // Media is auto enabled.
    $auto_enabled_entity_types_media = $page->findField('auto_enabled_entity_types[media]');
    $this->assertNotEmpty($auto_enabled_entity_types_media);
    $this->assertTrue($auto_enabled_entity_types_media->isChecked());

    // User is disabled.
    $auto_enabled_entity_types_user = $page->findField('auto_enabled_entity_types[user]');
    $this->assertNotEmpty($auto_enabled_entity_types_user);
    $this->assertFalse($auto_enabled_entity_types_user->isChecked());

    // Block is disabled.
    $auto_enabled_entity_types_block = $page->findField('auto_enabled_entity_types[block]');
    $this->assertNotEmpty($auto_enabled_entity_types_block);
    $this->assertFalse($auto_enabled_entity_types_block->isChecked());

    // Custom block type is disabled.
    $auto_enabled_entity_types_block_content_type = $page->findField('auto_enabled_entity_types[block_content_type]');
    $this->assertNotEmpty($auto_enabled_entity_types_block_content_type);
    $this->assertFalse($auto_enabled_entity_types_block_content_type->isChecked());

    // Custom block is disabled.
    $auto_enabled_entity_types_block_content = $page->findField('auto_enabled_entity_types[block_content]');
    $this->assertNotEmpty($auto_enabled_entity_types_block_content);
    $this->assertFalse($auto_enabled_entity_types_block_content->isChecked());

  }

}
