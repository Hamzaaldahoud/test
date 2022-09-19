<?php

namespace Drupal\Tests\varbase_api\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API Entity Operations.
 *
 * @group varbase_api
 */
class VarbaseApiEntityOperationsTest extends WebDriverTestBase {

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
   * Check Enabled JSON:API Entity Operation links for Content Types.
   */
  public function testCheckEnabledJsonApiEntityOperationsForContentTypes() {

    // Create an content admin user who can only manage content types.
    $content_admin_user = $this->drupalCreateUser([
      'view the administration theme',
      'access toolbar',
      'administer content types',
      'administer nodes',
      'administer node fields',
      'administer node display',
      'administer node form display',
      'access content overview',
    ]);
    $this->drupalLogin($content_admin_user);

    // When navigating to the Content Types page.
    $this->drupalGet('admin/structure/types');

    $content_types_text = $this->t('Content Types');
    $this->assertSession()->pageTextContains($content_types_text);

    $this->assertSession()->linkNotExists('View JSON');
    $this->assertSession()->linkNotExists('View API Docs');

    // Create a Site Admin user. Who can View JSON and the View API Docs.
    $site_admin_user = $this->drupalCreateUser([
      'view the administration theme',
      'access toolbar',
      'administer content types',
      'administer nodes',
      'administer node fields',
      'administer node display',
      'administer node form display',
      'access content overview',
      'access view json entity operation',
      'access view api docs entity operation',
      'access openapi api docs',
    ]);
    $this->drupalLogin($site_admin_user);

    // When navigating to the Content Types page.
    $this->drupalGet('admin/structure/types');

    $content_types_text = $this->t('Content Types');
    $this->assertSession()->pageTextContains($content_types_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkExists('View JSON');
    $this->assertSession()->linkExists('View API Docs');

    // Given that the root super user 1 was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // When navigating to the Content Types page.
    $this->drupalGet('admin/structure/types');

    $content_types_text = $this->t('Content Types');
    $this->assertSession()->pageTextContains($content_types_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkExists('View JSON');
    $this->assertSession()->linkExists('View API Docs');

  }

  /**
   * Check Enabled JSON:API Entity Operation links for media Types.
   */
  public function testCheckEnabledJsonApiEntityOperationsForMediaTypes() {

    // Create an content admin user who can only manage media types.
    $content_admin_user = $this->drupalCreateUser([
      'view the administration theme',
      'access toolbar',
      'administer media types',
      'administer media',
      'administer media fields',
      'administer media display',
      'administer media form display',
      'access media overview',
    ]);
    $this->drupalLogin($content_admin_user);

    // When navigating to the Media types page.
    $this->drupalGet('admin/structure/media');

    $media_types_text = $this->t('Media types');
    $this->assertSession()->pageTextContains($media_types_text);

    $this->assertSession()->linkNotExists('View JSON');
    $this->assertSession()->linkNotExists('View API Docs');

    // Create a Site Admin user. Who can View JSON and the View API Docs.
    $site_admin_user = $this->drupalCreateUser([
      'view the administration theme',
      'access toolbar',
      'administer media types',
      'administer media',
      'administer media fields',
      'administer media display',
      'administer media form display',
      'access media overview',
      'access view json entity operation',
      'access view api docs entity operation',
      'access openapi api docs',
    ]);
    $this->drupalLogin($site_admin_user);

    // When navigating to the Media types page.
    $this->drupalGet('admin/structure/media');

    $media_types_text = $this->t('Media types');
    $this->assertSession()->pageTextContains($media_types_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkExists('View JSON');
    $this->assertSession()->linkExists('View API Docs');

    // Given that the root super user 1 was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // When navigating to the Media types page.
    $this->drupalGet('admin/structure/media');

    $media_types_text = $this->t('Media types');
    $this->assertSession()->pageTextContains($media_types_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkExists('View JSON');
    $this->assertSession()->linkExists('View API Docs');

  }

  /**
   * Check Enabled JSON:API Entity Operation links for Taxonomy.
   */
  public function testCheckEnabledJsonApiEntityOperationsForTaxonomy() {

    // Create an content admin user who can only manage Taxonomies.
    $content_admin_user = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'access toolbar',
      'administer taxonomy',
      'access taxonomy overview',
    ]);
    $this->drupalLogin($content_admin_user);

    // When navigating to the Taxonomy page.
    $this->drupalGet('admin/structure/taxonomy');

    // Then should see "Taxonomy".
    $taxonomy_text = $this->t('Taxonomy');
    $this->assertSession()->pageTextContains($taxonomy_text);

    $this->assertSession()->linkNotExists('View JSON');
    $this->assertSession()->linkNotExists('View API Docs');

    // Create a Site Admin user. Who can View JSON and the View API Docs.
    $site_admin_user = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'access toolbar',
      'administer taxonomy',
      'access taxonomy overview',
      'access view json entity operation',
      'access view api docs entity operation',
      'access openapi api docs',
    ]);
    $this->drupalLogin($site_admin_user);

    // When navigating to the Taxonomy page.
    $this->drupalGet('admin/structure/taxonomy');

    // Then should see "Taxonomy".
    $taxonomy_text = $this->t('Taxonomy');
    $this->assertSession()->pageTextContains($taxonomy_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkExists('View JSON');
    $this->assertSession()->linkExists('View API Docs');

    // Given that the root super user 1 was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // When navigating to the Taxonomy page.
    $this->drupalGet('admin/structure/taxonomy');

    // Then should see "Taxonomy".
    $taxonomy_text = $this->t('Taxonomy');
    $this->assertSession()->pageTextContains($taxonomy_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkExists('View JSON');
    $this->assertSession()->linkExists('View API Docs');

  }

  /**
   * Check Disabled JSON:API Entity Operation links for Block Types.
   */
  public function testCheckDisabledJsonApiEntityOperationsForBlockTypes() {

    // Create an content admin user who can only manage block types.
    $content_admin_user = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'access toolbar',
      'administer blocks',
    ]);
    $this->drupalLogin($content_admin_user);

    // When navigating to the Block types page.
    $this->drupalGet('admin/structure/block/block-content/types');

    // Then should see "Custom block library".
    $block_types_text = $this->t('Custom block library');
    $this->assertSession()->pageTextContains($block_types_text);

    $this->assertSession()->linkNotExists('View JSON');
    $this->assertSession()->linkNotExists('View API Docs');

    // Create a Site Admin user. Who can View JSON and the View API Docs.
    $site_admin_user = $this->drupalCreateUser([
      'access administration pages',
      'view the administration theme',
      'access toolbar',
      'administer blocks',
      'access view json entity operation',
      'access view api docs entity operation',
      'access openapi api docs',
    ]);
    $this->drupalLogin($site_admin_user);

    // When navigating to the Block types page.
    $this->drupalGet('admin/structure/block/block-content/types');

    // Then should see "Custom block library".
    $block_types_text = $this->t('Custom block library');
    $this->assertSession()->pageTextContains($block_types_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkNotExists('View JSON');
    $this->assertSession()->linkNotExists('View API Docs');

    // Given that the root super user 1 was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // When navigating to the Block types page.
    $this->drupalGet('admin/structure/block/block-content/types');

    // Then should see "Custom block library".
    $block_types_text = $this->t('Custom block library');
    $this->assertSession()->pageTextContains($block_types_text);

    // The user can see the "View JSON" and "View API Docs" entity oprations.
    $this->assertSession()->linkNotExists('View JSON');
    $this->assertSession()->linkNotExists('View API Docs');

  }

}
