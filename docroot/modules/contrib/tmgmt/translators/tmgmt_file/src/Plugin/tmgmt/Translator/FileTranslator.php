<?php

namespace Drupal\tmgmt_file\Plugin\tmgmt\Translator;

use Drupal\Core\File\FileSystemInterface;
use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\Translator\TranslatableResult;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt\TranslatorPluginBase;

/**
 * File translator.
 *
 * @TranslatorPlugin(
 *   id = "file",
 *   label = @Translation("File exchange"),
 *   description = @Translation("Provider to export and import files."),
 *   ui = "Drupal\tmgmt_file\FileTranslatorUi"
 * )
 */
class FileTranslator extends TranslatorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function checkTranslatable(TranslatorInterface $translator, JobInterface $job) {
    // Anything can be exported.
    return TranslatableResult::yes();
  }

  /**
   * {@inheritdoc}
   */
  public function requestTranslation(JobInterface $job) {
    $name = "JobID" . $job->id() . '_' . $job->getSourceLangcode() . '_' . $job->getTargetLangcode();

    $export = \Drupal::service('plugin.manager.tmgmt_file.format')->createInstance($job->getSetting('export_format'), $job->getSetting('format_configuration'));

    $path = $job->getSetting('scheme') . '://tmgmt_file/' . $name . '.' .  $job->getSetting('export_format');
    $dirname = dirname($path);
    if (\Drupal::service('file_system')->prepareDirectory($dirname, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      $file = \Drupal::service('file.repository')->writeData($export->export($job), $path, FileSystemInterface::EXISTS_REPLACE);
      \Drupal::service('file.usage')->add($file, 'tmgmt_file', 'tmgmt_job', $job->id());
      $job->submitted('Exported file can be downloaded <a href="@link" download>here</a>.', array('@link' => \Drupal::service('file_url_generator')->generateAbsoluteString($path)));
    }
    else {
      $job->rejected('Failed to create writable directory @dirname, check file system permissions.', ['@dirname' => $dirname]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasCheckoutSettings(JobInterface $job) {
    return $job->getTranslator()->getSetting('allow_override');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultSettings() {
    return array(
      'export_format' => 'xlf',
      'allow_override' => TRUE,
      'scheme' => 'public',
      // Making this setting TRUE by default is more appropriate, however we
      // need to make it FALSE due to backwards compatibility.
      'xliff_processing' => FALSE,
      'xliff_cdata' => FALSE,
      'format_configuration' => [],
    );
  }

}
