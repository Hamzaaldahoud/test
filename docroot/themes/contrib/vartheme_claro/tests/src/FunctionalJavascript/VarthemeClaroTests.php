<?php

namespace Drupal\Tests\vartheme_claro\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Core\Cache\Cache;

/**
 * Vartheme Claro Tests.
 *
 * @group vartheme_claro
 */
class VarthemeClaroTests extends WebDriverTestBase {

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
    'toolbar',
    'language',
    'config_translation',
    'content_translation',
    'locale',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);

    // Insall the Claro admin theme.
    $this->container->get('theme_installer')->install(['vartheme_claro']);

    // Set the Claro theme as the default admin theme.
    $this->config('system.theme')->set('admin', 'vartheme_claro')->save();

    drupal_flush_all_caches();

    ConfigurableLanguage::createFromLangcode('ar')->save();
    Cache::invalidateTags(['rendered', 'locale']);
  }

  /**
   * Check Vartheme Claro blocks.
   */
  public function testCheckVarthemeClaroBlocks() {
    $assert_session = $this->assertSession();

    $this->drupalLogin($this->rootUser);

    // Vartheme Claro blocks.
    $this->drupalGet('/admin/structure/block/list/vartheme_claro');

    $assert_session->pageTextContains($this->t('Page title'));
    $assert_session->pageTextContains($this->t('Primary tabs'));
    $assert_session->pageTextContains($this->t('Secondary tabs'));
    $assert_session->pageTextContains($this->t('Breadcrumbs'));
    $assert_session->pageTextContains($this->t('Status messages'));
    $assert_session->pageTextContains($this->t('Help'));
    $assert_session->pageTextContains($this->t('Primary admin actions'));
    $assert_session->pageTextContains($this->t('Main page content'));
  }

}
