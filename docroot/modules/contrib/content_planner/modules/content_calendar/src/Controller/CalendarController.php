<?php

namespace Drupal\content_calendar\Controller;

use Drupal\content_calendar\Component\Calendar;
use Drupal\content_calendar\ContentCalendarService;
use Drupal\content_calendar\DateTimeHelper;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\content_calendar\ContentTypeConfigService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Implements CalendarController class.
 */
class CalendarController extends ControllerBase {

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The request service.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Drupal\content_calendar\ContentTypeConfigService definition.
   *
   * @var \Drupal\content_calendar\ContentTypeConfigService
   */
  protected $contentTypeConfigService;

  /**
   * Calendar Service.
   *
   * @var \Drupal\content_calendar\ContentCalendarService
   */
  protected $contentCalendarService;

  /**
   * Provides an interface for redirect destinations.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * Constructs a new CalendarController object.
   */
  public function __construct(
    ThemeManagerInterface $theme_manager,
    RequestStack $request_stack,
    ContentTypeConfigService $content_type_config_service,
    ContentCalendarService $content_calendar_service,
    AccountProxyInterface $current_user,
    RedirectDestinationInterface $redirect_destination
  ) {
    $this->themeManager = $theme_manager;
    $this->request = $request_stack->getCurrentRequest();
    $this->contentTypeConfigService = $content_type_config_service;
    $this->contentCalendarService = $content_calendar_service;
    $this->currentUser = $current_user;
    $this->redirectDestination = $redirect_destination;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme.manager'),
      $container->get('request_stack'),
      $container->get('content_calendar.content_type_config_service'),
      $container->get('content_calendar.content_calendar_service'),
      $container->get('current_user'),
      $container->get('redirect.destination')
    );
  }

  /**
   * Show Calendar year.
   */
  public function showCurrentCalendarYear() {

    $year = date('Y');

    return $this->showCalendarYear($year);
  }

  /**
   * Show Calendar year.
   */
  public function showCalendarYear($year) {

    $calendars = [];

    // Get content type config entities.
    $content_type_config_entities = $this->contentTypeConfigService->loadAllEntities();

    // Check if Content Calendar has been configured.
    if (!$content_type_config_entities) {
      $this->messenger()->addMessage($this->t('Content Calendar is not configured yet. Please do this in the settings tab.'), 'error');
      return [];
    }

    // Generate calendar structures.
    foreach (range(1, 12) as $month) {

      // Create new Calendar.
      $calender = new Calendar(
        $this->themeManager,
        $this->contentTypeConfigService,
        $this->contentCalendarService,
        $month,
        $year,
        $this->currentUser
      );

      $calendars[] = $calender->build();
    }

    // Get Filter Form.
    $form_params = [
      'current_year' => date('Y'),
      'selected_year' => $year,
    ];
    $filters_form = $this->formBuilder()->getForm('Drupal\content_calendar\Form\CalenderOverviewFilterForm', $form_params);

    if ($this->currentUser->hasPermission('administer content calendar settings')) {
      $has_permission = TRUE;
    }
    else {
      $has_permission = FALSE;
    }

    $build = [
      '#theme' => 'content_calendar_overview',
      '#calendars' => $calendars,
      '#filters_form' => $filters_form,
      '#has_permission' => $has_permission,
    ];

    return $build;
  }

  /**
   * Update creation date of a given Node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   * @param string $date
   *   The date timestamp.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Return a Json Response.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function updateNodePublishDate(NodeInterface $node, $date) {

    $data = [
      'success' => FALSE,
      'message' => NULL,
    ];

    // Get content type config entities.
    $content_type_config_entities = $this->contentTypeConfigService->loadAllEntities();

    // Check for allowed types, marked in the Content Calendar settings.
    if (!array_key_exists($node->getType(), $content_type_config_entities)) {

      $data['message'] = $this->t('Action is not allowed for Nodes of type @type', ['@type' => $node->getType()]);
      return new JsonResponse($data);
    }

    // First – Update created on date!
    // Get the Node's "created on" date.
    $created_on_timestamp = $node->get('created')->getValue();
    $created_on_timestamp_value = $created_on_timestamp[0]['value'];
    // Return a date object.
    $original_created_on_datetime = DateTimeHelper::convertUnixTimestampToDatetime($created_on_timestamp_value);

    // Extract hour, minutes and seconds.
    $hour = $original_created_on_datetime->format('H');
    $minutes = $original_created_on_datetime->format('i');
    $seconds = $original_created_on_datetime->format('s');

    // Create a new datetime object from the given date.
    $new_created_on_datetime = \DateTime::createFromFormat('Y-m-d', $date);

    // Set hour, minutes and seconds.
    $new_created_on_datetime->setTime($hour, $minutes, $seconds);

    // Set created time.
    $node->set('created', $new_created_on_datetime->getTimestamp());

    // Second - Update publish on date! (only if publish on date is set)
    // Get publish on timestamp.
    $publish_on_timestamp = $node->get('publish_on')->getValue();
    $publish_on_timestamp_value = $publish_on_timestamp[0]['value'];

    // Only change scheduler publish on timestamp, when "publish on" is set.
    if ($publish_on_timestamp_value) {

      // Get the Node's publish ondate and return a datetime object.
      $original_publish_datetime = DateTimeHelper::convertUnixTimestampToDatetime($publish_on_timestamp_value);

      // Extract hour, minutes and seconds.
      $hour = $original_publish_datetime->format('H');
      $minutes = $original_publish_datetime->format('i');
      $seconds = $original_publish_datetime->format('s');

      // Create a new datetime object from the given date.
      $new_publish_datetime = \DateTime::createFromFormat('Y-m-d', $date);

      // Set hour, minutes and seconds.
      $new_publish_datetime->setTime($hour, $minutes, $seconds);

      // Set publish on datetime.
      $node->set('publish_on', $new_publish_datetime->getTimestamp());

      // Set created on datetime.
      $node->set('created', $new_publish_datetime->getTimestamp());
    }

    // Save.
    if ($node->save() == SAVED_UPDATED) {
      $data['success'] = TRUE;
      $data['message'] = $this->t('The creation date for Node @id has been updated', ['@id' => $node->id()]);
    }

    return new JsonResponse($data);
  }

  /**
   * Redirect to current Calendar.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function redirectToCurrentCalendar() {

    $calendar_id = date('Y-n');

    return $this->redirect('content_calendar.calendar', [], ['fragment' => $calendar_id]);
  }

  /**
   * Redirect and jump to a given Calendar directly.
   *
   * @param string $year
   *   Calendar year.
   * @param string $month
   *   Calendar month.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   */
  public function redirectToCalendar($year, $month) {

    $fragment = $year . '-' . $month;

    return $this->redirect(
      'content_calendar.calendar',
      ['year' => $year],
      ['fragment' => $fragment]
    );
  }

  /**
   * Duplicate Node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node object.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response object that may be returned by the controller.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function duplicateNode(NodeInterface $node) {

    $duplicate = $node->createDuplicate();

    $duplicate->setTitle($duplicate->getTitle() . ' clone');

    $duplicate->save();

    $destination = $this->redirectDestination->get();

    return new RedirectResponse($destination);
  }

}
