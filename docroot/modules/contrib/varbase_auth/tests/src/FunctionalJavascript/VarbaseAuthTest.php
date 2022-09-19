<?php

namespace Drupal\Tests\varbase_auth\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Varbase Auth test.
 *
 * @group varbase_auth
 */
class VarbaseAuthTest extends WebDriverTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'standard';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'vartheme_bs4';


  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'varbase_auth',
    'social_auth',
    'social_auth_facebook',
    'social_auth_google',
    'social_auth_linkedin',
    'social_auth_twitter',
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
   * Check Varbase Auth Login page.
   */
  public function testCheckVarbaseAuthLoginPage() {

    $this->drupalGet('/user/login');
    $this->assertSession()->elementExists('css', '.block-social-auth-login');

  }

  /**
   * Check Varbase Auth User register.
   */
  public function testCheckVarbaseAuthUserRegister() {

    $this->drupalGet('/user/register');
    $this->assertSession()->elementExists('css', '.block-social-auth');
  }

  /**
   * Check Varbase Auth User authentication.
   */
  public function testCheckVarbaseAuthUserAuthentication() {

    $this->drupalLogin($this->rootUser);
    $this->drupalGet('/admin/config/social-api/social-auth');
    $this->assertSession()->pageTextContains('social_auth_facebook');
    $this->assertSession()->pageTextContains('social_auth_google');
    $this->assertSession()->pageTextContains('social_auth_linkedin');
    $this->assertSession()->pageTextContains('social_auth_twitter');
  }

}
