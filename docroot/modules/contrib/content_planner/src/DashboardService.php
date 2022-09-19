<?php

namespace Drupal\content_planner;

use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Implements DashboardService class.
 */
class DashboardService {

  /**
   * The dashboard settings service.
   *
   * @var \Drupal\content_planner\DashboardSettingsService
   */
  protected $dashboardSettingsService;

  /**
   * Interface for classes that manage a set of enabled modules.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new DashboardService object.
   */
  public function __construct(DashboardSettingsService $dashboard_settings_service, ModuleHandlerInterface $module_handler) {
    $this->dashboardSettingsService = $dashboard_settings_service;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Gets the dashboard settings.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The current dashboard config.
   */
  public function getDashboardSettings() {
    return $this->dashboardSettingsService->getSettings();
  }

  /**
   * Check if the Content Calendar is enabled.
   *
   * @return bool
   *   TRUE if the content calendar is enabled.
   */
  public function isContentCalendarEnabled() {
    return $this->moduleHandler->moduleExists('content_calendar');
  }

  /**
   * Check if the Content Kanban is enabled.
   *
   * @return bool
   *   TRUE if the kanban calendar is enabled.
   */
  public function isContentKanbanEnabled() {
    return $this->moduleHandler->moduleExists('content_kanban');
  }

}
