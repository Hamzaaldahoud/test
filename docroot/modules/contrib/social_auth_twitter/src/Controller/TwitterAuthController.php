<?php

namespace Drupal\social_auth_twitter\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\social_auth\User\UserAuthenticator;
use Drupal\social_auth\SocialAuthDataHandler;
use Drupal\social_auth_twitter\TwitterAuthManager;
use Drupal\social_api\Plugin\NetworkManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manages requests to Twitter API.
 */
class TwitterAuthController extends ControllerBase {

  /**
   * The network plugin manager.
   *
   * @var \Drupal\social_api\Plugin\NetworkManager
   */
  private $networkManager;

  /**
   * The user authenticator.
   *
   * @var \Drupal\social_auth\User\UserAuthenticator
   */
  private $userAuthenticator;

  /**
   * The Twitter authentication manager.
   *
   * @var \Drupal\social_auth_twitter\TwitterAuthManager
   */
  private $twitterManager;

  /**
   * Used to access GET parameters.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  private $request;

  /**
   * The Social Auth Data Handler.
   *
   * @var \Drupal\social_auth\SocialAuthDataHandler
   */
  private $dataHandler;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * TwitterLoginController constructor.
   *
   * @param \Drupal\social_api\Plugin\NetworkManager $network_manager
   *   Used to get an instance of social_auth_twitter network plugin.
   * @param \Drupal\social_auth\User\UserAuthenticator $user_authenticator
   *   Manages user login/registration.
   * @param \Drupal\social_auth_twitter\TwitterAuthManager $twitter_manager
   *   Used to manage authentication methods.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request
   *   Used to access GET parameters.
   * @param \Drupal\social_auth\SocialAuthDataHandler $data_handler
   *   SocialAuthDataHandler object.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Used to handle metadata for redirection to authentication URL.
   */
  public function __construct(NetworkManager $network_manager,
                              UserAuthenticator $user_authenticator,
                              TwitterAuthManager $twitter_manager,
                              RequestStack $request,
                              SocialAuthDataHandler $data_handler,
                              MessengerInterface $messenger,
                              RendererInterface $renderer = NULL) {
    $this->networkManager = $network_manager;
    $this->userAuthenticator = $user_authenticator;
    $this->twitterManager = $twitter_manager;
    $this->request = $request;
    $this->dataHandler = $data_handler;
    $this->messenger = $messenger;
    $this->renderer = $renderer;

    // Sets the plugin id.
    $this->userAuthenticator->setPluginId('social_auth_twitter');

    // Sets the session keys to nullify if user could not logged in.
    $this->userAuthenticator->setSessionKeysToNullify(['access_token']);

    if (!$this->renderer) {
      $this->renderer = \Drupal::service('renderer');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.network.manager'),
      $container->get('social_auth.user_authenticator'),
      $container->get('twitter_auth.manager'),
      $container->get('request_stack'),
      $container->get('social_auth.data_handler'),
      $container->get('messenger'),
      $container->get('renderer')
    );
  }

  /**
   * Redirects to Twitter for authentication.
   */
  public function redirectToTwitter() {
    $context = new RenderContext();
    $response = $this->renderer->executeInRenderContext($context, function () {
      try {
        /* @var \Drupal\social_auth_twitter\Plugin\Network\TwitterAuth $network_plugin */
        // Creates an instance of the social_auth_twitter Network Plugin.
        $network_plugin = $this->networkManager->createInstance('social_auth_twitter');

        // Destination parameter specified in url.
        $destination = $this->request->getCurrentRequest()->get('destination');
        // If destination parameter is set, save it.
        if ($destination) {
          $this->userAuthenticator->setDestination($destination);
          $this->request->getCurrentRequest()->query->remove('destination');
        }

        /* @var \Abraham\TwitterOAuth\TwitterOAuth $connection */
        $connection = $network_plugin->getSdk();

        if ($connection) {

          // Requests Twitter to get temporary tokens.
          $request_token = $connection->oauth('oauth/request_token', ['oauth_callback' => $network_plugin->getOauthCallback()]);

          // Saves the temporary token values in session.
          $this->twitterManager->setOauthToken($request_token['oauth_token']);
          $this->twitterManager->setOauthTokenSecret($request_token['oauth_token_secret']);

          // Generates url for user authentication.
          $url = $connection->url('oauth/authorize', ['oauth_token' => $request_token['oauth_token']]);

          // Forces session to be saved before redirection.
          $this->twitterManager->save();

          return new TrustedRedirectResponse($url);
        }
        else {
          $this->messenger->addError($this->t('Social Auth Twitter not configured properly. Contact site administrator.'));

          return $this->redirect('user.login');
        }
      }
      catch (\Exception $ex) {
        $this->messenger->addError($this->t('You could not be authenticated, please contact the administrator.'));

        return $this->redirect('user.login');
      }
    });

    // Add bubbleable metadata to the response.
    if ($response instanceof TrustedRedirectResponse && !$context->isEmpty()) {
      $bubbleable_metadata = $context->pop();
      $response->addCacheableDependency($bubbleable_metadata);
    }

     return $response;
  }

  /**
   * Callback function to login user.
   */
  public function callback() {
    // Check if retrieves $_GET['denied'].
    if ($this->request->getCurrentRequest()->query->has('denied')) {
      $this->messenger->addError($this->t('You could not be authenticated.'));
      return $this->redirect('user.login');
    }

    $oauth_token = $this->twitterManager->getOauthToken();
    $oauth_token_secret = $this->twitterManager->getOauthTokenSecret();

    /* @var \Abraham\TwitterOAuth\TwitterOAuth $client */
    $client = $this->networkManager->createInstance('social_auth_twitter')->getSdk2($oauth_token, $oauth_token_secret);

    // Gets the permanent access token.
    $access_token = $client->oauth('oauth/access_token', ['oauth_verifier' => $this->twitterManager->getOauthVerifier()]);

    /* @var \Abraham\TwitterOAuth\TwitterOAuth $connection */
    $connection = $this->networkManager->createInstance('social_auth_twitter')->getSdk2($access_token['oauth_token'], $access_token['oauth_token_secret']);
    $params = [
      'include_email' => 'true',
      'include_entities' => 'false',
      'skip_status' => 'true',
    ];

    // Saves access token so that event subscribers can call Twitter API.
    $this->dataHandler->set('access_token', $access_token);

    // Gets user information.
    $user = $connection->get("account/verify_credentials", $params);

    // If user information could be retrieved.
    if ($user) {
      // Remove _normal from url to get a bigger profile picture.
      $picture = str_replace('_normal', '', $user->profile_image_url_https);

      return $this->userAuthenticator->authenticateUser($user->name, $user->email, $user->id, json_encode($access_token), $picture);
    }

    $this->messenger->addError($this->t('You could not be authenticated, please contact the administrator.'));
    return $this->redirect('user.login');
  }

}
