<?php

namespace Drupal\content_planner;

use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Implements DashboardSettingsService class.
 */
class DashboardSettingsService {

  /**
   * Config name.
   */
  const CONFIG_NAME = 'content_planner.dashboard_settings';

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new DashboardSettingsService object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * Gets the complete settings config of the module.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The complete settings config of the module.
   */
  public function getSettings() {
    return $this->configFactory->get(self::CONFIG_NAME);
  }

  /**
   * Get Block configurations.
   *
   * @return array
   *   All block configurations.
   */
  public function getBlockConfigurations() {

    if ($settings = $this->getSettings()) {

      if ($block_configurations = $settings->get('blocks')) {
        return $block_configurations;
      }

    }

    return [];
  }

  /**
   * Get the configuration of a specific block.
   *
   * @param string $block_id
   *   The block id to retrieve the config for.
   *
   * @return array|mixed
   *   The block configuration.
   */
  public function getBlockConfiguration($block_id) {

    if ($block_configurations = $this->getBlockConfigurations()) {

      if (array_key_exists($block_id, $block_configurations)) {
        return $block_configurations[$block_id];
      }

    }

    return [];
  }

  /**
   * Save configuration of a specific block.
   *
   * @param string $block_id
   *   The block id to save the config for.
   * @param array $configuration
   *   The config to save.
   *
   * @return bool
   *   TRUE if config was found and saved, FALSE otherwise.
   */
  public function saveBlockConfiguration($block_id, array $configuration) {

    if ($block_configurations = $this->getBlockConfigurations()) {

      if (array_key_exists($block_id, $block_configurations)) {

        $block_configurations[$block_id] = $configuration;

        $this->saveBlockConfigurations($block_configurations);
        return TRUE;
      }

    }

    return FALSE;
  }

  /**
   * Save all block configurations.
   *
   * @param array $configuration
   *   The config to save.
   */
  public function saveBlockConfigurations(array $configuration) {

    $this->configFactory->getEditable(self::CONFIG_NAME)
      ->set('blocks', $configuration)
      ->save();
  }

}
