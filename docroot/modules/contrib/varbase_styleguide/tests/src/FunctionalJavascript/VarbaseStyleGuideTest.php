<?php

namespace Drupal\Tests\varbase_styleguide\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\vmi\ViewModesInventoryFactory;

/**
 * Tests Varbase Style Guide.
 *
 * @group varbase_styleguide
 */
class VarbaseStyleGuideTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'minimal';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'vartheme_bs4';


  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'user',
    'filter',
    'toolbar',
    'block',
    'views',
    'node',
    'filter',
    'editor',
    'ckeditor',
    'field',
    'field_ui',
    'layout_discovery',
    'ds',
    'ds_extras',
    'field_group',
    'smart_trim',
    'media',
    'vmi',
    'varbase_styleguide',
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

    drupal_flush_all_caches();

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // Create a content type and activate all.
    $this->testContentType = $this->drupalCreateContentType([
      'type' => 'post',
      'name' => 'Post',
    ]);

    $storage = FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => 'field_image',
      'type' => 'entity_reference',
      'settings' => [
        'target_type' => 'media',
      ],
    ]);
    $storage->save();

    FieldConfig::create([
      'field_storage' => $storage,
      'entity_type' => 'node',
      'bundle' => 'post',
      'label' => 'Main image',
      'settings' => [
        'handler_settings' => [
          'target_bundles' => [
            'image' => 'image',
          ],
        ],
      ],
    ])->save();

    drupal_flush_all_caches();

    // Create a testing node.
    $this->drupalCreateNode([
      'title' => 'Post - Test Content',
      'type' => 'post',
      'body' => [
        [
          'value' => 'Varbase is an enhanced Drupal distribution packed with adaptive
            functionalities and essential modules, that speed up your development,
            and provides you with standardized configurations, making your life easier.

            The essence of Varbase, lies within the basic concept that initiated it;
            DRY (Don’t Repeat Yourself). Varbase handles that for you, relieving you
            from repeating all the modules, features, configurations that are included
            in every Drupal project.',
        ],
      ],
    ]);

  }

  /**
   * Check Varbase Style Guide on custom elments.
   *
   * Varbase elements and Bootstrap elements for Vartheme.
   */
  public function testCheckVarbaseStyleGuideOnCustomElementsForVartheme() {

    // Check on Vartheme (Bootstrap 4 - SASS).
    $this->drupalGet('admin/appearance/styleguide');
    $this->assertSession()->pageTextContains('Style guide');
    $this->assertSession()->pageTextContains('Showing style guide for Vartheme (Bootstrap 4 - SASS)');

    // Check on Bootstrap elements.
    $this->assertSession()->pageTextContains('Bootstrap elements');

    // Check on Varbase elements.
    $this->assertSession()->pageTextContains('Varbase elements');
    $this->assertSession()->pageTextContains('Callout Standard');
    $this->assertSession()->pageTextContains('Callout Danger');
    $this->assertSession()->pageTextContains('Callout Warning');
    $this->assertSession()->pageTextContains('Callout Info');

  }

  /**
   * Check Varbase Style Guide on (VMI).
   *
   * View Modes Inventory - Bootstrap Ready elements for Vartheme.
   */
  public function testCheckVarbaseStyleGuideOnVmiElementsForVartheme() {

    $assert_session = $this->assertSession();

    $this->drupalGet('admin/structure/types/manage/post/display');
    $assert_session->pageTextContains('Manage display');

    // Check all check boxes for VMI custom display view modes.
    $vmi_factory = \Drupal::service('class_resolver')
      ->getInstanceFromDefinition(ViewModesInventoryFactory::class);

    // View modes inventory list.
    $vmi_list = $vmi_factory->getViewModesList();

    // View modes inventory layouts mapping.
    $vmi_layouts_mapping = $vmi_factory->getLayoutsMapping();

    $selected_view_modes = [
      'hero_xlarge',
      'tout_large',
      'tout_medium',
      'tout_xlarge',
      'vertical_media_teaser_large',
      'vertical_media_teaser_medium',
      'vertical_media_teaser_small',
      'vertical_media_teaser_xlarge',
      'vertical_media_teaser_xsmall',
      'horizontal_media_teaser_large',
      'horizontal_media_teaser_medium',
      'horizontal_media_teaser_small',
      'horizontal_media_teaser_xlarge',
      'horizontal_media_teaser_xsmall',
      'text_teaser_large',
      'text_teaser_medium',
      'text_teaser_small',
    ];

    if (isset($vmi_list['view_modes'])
        && isset($vmi_layouts_mapping['mapping'])) {

      foreach ($selected_view_modes as $selected_view_mode) {
        // Only when we do hava a new selected view mode inventory.
        if (isset($vmi_list['view_modes'][$selected_view_mode])
           && isset($vmi_layouts_mapping['mapping'][$selected_view_mode])
           && isset($vmi_layouts_mapping['mapping'][$selected_view_mode]['layout'])
           && isset($vmi_layouts_mapping['mapping'][$selected_view_mode]['config_template'])
           && isset($vmi_layouts_mapping['mapping'][$selected_view_mode]['config_name'])) {

          $default_mapped_layout = $vmi_layouts_mapping['mapping'][$selected_view_mode]['layout'];
          $config_template_file = $vmi_layouts_mapping['mapping'][$selected_view_mode]['config_template'];
          $config_name = $vmi_layouts_mapping['mapping'][$selected_view_mode]['config_name'];

          $vmi_factory->mapViewModeWithLayout($selected_view_mode, $default_mapped_layout, 'node', 'post', $config_template_file, $config_name);

        }
      }
    }

    $this->drupalGet('admin/structure/types/manage/post/display/hero_xlarge');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Hero content'));
    $assert_session->pageTextContains($this->t('Title'));

    $this->drupalGet('admin/structure/types/manage/post/display/tout_large');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Tout content'));
    $assert_session->pageTextContains($this->t('Title'));

    $this->drupalGet('admin/structure/types/manage/post/display/tout_medium');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Tout content'));
    $assert_session->pageTextContains($this->t('Title'));

    $this->drupalGet('admin/structure/types/manage/post/display/tout_xlarge');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Tout content'));
    $assert_session->pageTextContains($this->t('Title'));

    $this->drupalGet('admin/structure/types/manage/post/display/vertical_media_teaser_large');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/vertical_media_teaser_medium');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/vertical_media_teaser_small');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/vertical_media_teaser_xlarge');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/vertical_media_teaser_xsmall');
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/horizontal_media_teaser_large');
    $assert_session->pageTextContains($this->t('Left'));
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Right'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/horizontal_media_teaser_medium');
    $assert_session->pageTextContains($this->t('Left'));
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Right'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/horizontal_media_teaser_small');
    $assert_session->pageTextContains($this->t('Left'));
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Right'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/horizontal_media_teaser_xlarge');
    $assert_session->pageTextContains($this->t('Left'));
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Right'));
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/horizontal_media_teaser_xsmall');
    $assert_session->pageTextContains($this->t('Left'));
    $assert_session->pageTextContains($this->t('Main image'));
    $assert_session->pageTextContains($this->t('Right'));
    $assert_session->pageTextContains($this->t('Title'));

    $this->drupalGet('admin/structure/types/manage/post/display/text_teaser_large');
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/text_teaser_medium');
    $assert_session->pageTextContains($this->t('Title'));
    $assert_session->pageTextContains($this->t('Body'));

    $this->drupalGet('admin/structure/types/manage/post/display/text_teaser_small');
    $assert_session->pageTextContains($this->t('Title'));

    $this->drupalGet('admin/appearance/styleguide');
    $assert_session->pageTextContains('Style guide');

    $assert_session->pageTextContains('View Modes - Content type [Post] - hero_xlarge');
    $assert_session->pageTextContains('View Modes - Content type [Post] - tout_large');
    $assert_session->pageTextContains('View Modes - Content type [Post] - tout_medium');
    $assert_session->pageTextContains('View Modes - Content type [Post] - tout_xlarge');
    $assert_session->pageTextContains('View Modes - Content type [Post] - vertical_media_teaser_large');
    $assert_session->pageTextContains('View Modes - Content type [Post] - vertical_media_teaser_medium');
    $assert_session->pageTextContains('View Modes - Content type [Post] - vertical_media_teaser_small');
    $assert_session->pageTextContains('View Modes - Content type [Post] - vertical_media_teaser_xlarge');
    $assert_session->pageTextContains('View Modes - Content type [Post] - vertical_media_teaser_xsmall');
    $assert_session->pageTextContains('View Modes - Content type [Post] - horizontal_media_teaser_large');
    $assert_session->pageTextContains('View Modes - Content type [Post] - horizontal_media_teaser_medium');
    $assert_session->pageTextContains('View Modes - Content type [Post] - horizontal_media_teaser_small');
    $assert_session->pageTextContains('View Modes - Content type [Post] - horizontal_media_teaser_xlarge');
    $assert_session->pageTextContains('View Modes - Content type [Post] - horizontal_media_teaser_xsmall');
    $assert_session->pageTextContains('View Modes - Content type [Post] - text_teaser_large');
    $assert_session->pageTextContains('View Modes - Content type [Post] - text_teaser_medium');
    $assert_session->pageTextContains('View Modes - Content type [Post] - text_teaser_small');

  }

}
