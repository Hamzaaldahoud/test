<?php

namespace Drupal\content_calendar\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\scheduler\SchedulerEvent;
use Drupal\scheduler\SchedulerEvents;

/**
 * Implements SchedulerPublishSubScriber class.
 */
class SchedulerPublishSubScriber implements EventSubscriberInterface {

  /**
   * Interface for classes that manage a set of enabled modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a SchedulerPublishSubScriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   Interface for classes that manage a set of enabled modules.
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // The values in the arrays give the function names below.
    $events[SchedulerEvents::PUBLISH][] = ['onNodePublish'];
    return $events;
  }

  /**
   * Act upon a node publish.
   *
   * @param \Drupal\scheduler\SchedulerEvent $event
   *   Drupal Schedule Event.
   */
  public function onNodePublish(SchedulerEvent $event) {

    // If the Content Kanban module exists.
    if ($this->moduleHandler->moduleExists('content_kanban')) {

      /** @var \Drupal\node\Entity\Node $node */
      $node = $event->getNode();

      // Set status to published.
      $node->setPublished(TRUE);

      // Set Moderation state to published.
      $node->moderation_state->value = 'published';

      // Return updated node to event
      // which in turn returns it to the scheduler module.
      $event->setNode($node);

    }
  }

}
