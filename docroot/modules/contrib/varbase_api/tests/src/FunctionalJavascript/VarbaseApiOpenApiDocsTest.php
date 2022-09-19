<?php

namespace Drupal\Tests\varbase_api\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Tests Varbase API OpenAPI Documentation test.
 *
 * @group varbase_api
 */
class VarbaseApiOpenApiDocsTest extends WebDriverTestBase {

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
   * Check OpenAPI Documentation using Explore With Swagger UI.
   */
  public function testCheckOpenApiDocsExploreWithSwagger() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // OpenAPI Documentation.
    $this->drupalGet('admin/config/services/openapi/swagger/jsonapi');

    $page = $this->getSession()->getPage();

    $page->waitFor(20, function () use ($page) {
      return $page->find('css', "section.models.is-open");
    });

    $openapi_doc_text = $this->t('OpenAPI Documentation');
    $this->assertSession()->pageTextContains($openapi_doc_text);

    // Content type.
    $content_type_text = $this->t('Content type');
    $this->assertSession()->pageTextContains($content_type_text);

    // Content.
    $content_text = $this->t('Content - Basic page');
    $this->assertSession()->pageTextContains($content_text);

    // Taxonomy vocabulary.
    $taxonomy_vocabulary_text = $this->t('Taxonomy vocabulary');
    $this->assertSession()->pageTextContains($taxonomy_vocabulary_text);

    // Taxonomy term.
    $taxonomy_term_text = $this->t('Taxonomy term - Tags');
    $this->assertSession()->pageTextContains($taxonomy_term_text);

    // Media type.
    $media_type_text = $this->t('Media type');
    $this->assertSession()->pageTextContains($media_type_text);

    // Media - Image.
    $media_image_text = $this->t('Media - Image');
    $this->assertSession()->pageTextContains($media_image_text);

    // Media - Video.
    $media_video_text = $this->t('Media - Video');
    $this->assertSession()->pageTextContains($media_video_text);

    // Media - Remote video.
    $media_remote_video_text = $this->t('Media - Remote video');
    $this->assertSession()->pageTextContains($media_remote_video_text);

    // Media - Audio.
    $media_audio_text = $this->t('Media - Audio');
    $this->assertSession()->pageTextContains($media_audio_text);

  }

  /**
   * Check OpenAPI Documentation using Explore With ReDoc.
   */
  public function testCheckOpenApiDocsExploreWithReDoc() {

    // Given that the root super user was logged in to the site.
    $this->drupalLogin($this->rootUser);

    // OpenAPI Documentation.
    $this->drupalGet('admin/config/services/openapi/redoc/jsonapi');

    $page = $this->getSession()->getPage();

    $page->waitFor(20, function () use ($page) {
      return $page->find('css', "redoc#redoc-ui");
    });

    $openapi_doc_text = $this->t('OpenAPI Documentation');
    $this->assertSession()->pageTextContains($openapi_doc_text);

    // Content type.
    $content_type_text = $this->t('Content type');
    $this->assertSession()->pageTextContains($content_type_text);

    // Content.
    $content_text = $this->t('Content - Basic page');
    $this->assertSession()->pageTextContains($content_text);

    // Taxonomy vocabulary.
    $taxonomy_vocabulary_text = $this->t('Taxonomy vocabulary');
    $this->assertSession()->pageTextContains($taxonomy_vocabulary_text);

    // Taxonomy term.
    $taxonomy_term_text = $this->t('Taxonomy term - Tags');
    $this->assertSession()->pageTextContains($taxonomy_term_text);

    // Media type.
    $media_type_text = $this->t('Media type');
    $this->assertSession()->pageTextContains($media_type_text);

    // Media - Image.
    $media_image_text = $this->t('Media - Image');
    $this->assertSession()->pageTextContains($media_image_text);

    // Media - Video.
    $media_video_text = $this->t('Media - Video');
    $this->assertSession()->pageTextContains($media_video_text);

    // Media - Remote video.
    $media_remote_video_text = $this->t('Media - Remote video');
    $this->assertSession()->pageTextContains($media_remote_video_text);

    // Media - Audio.
    $media_audio_text = $this->t('Media - Audio');
    $this->assertSession()->pageTextContains($media_audio_text);

  }

}
