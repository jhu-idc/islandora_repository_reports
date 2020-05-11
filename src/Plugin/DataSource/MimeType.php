<?php

namespace Drupal\islandora_repository_reports\Plugin\DataSource;

use Drupal\islandora_repository_reports\Plugin\DataSource\IslandoraRepositoryReportsDataSourceInterface;

/**
 * Data source that gets media counts by MIME type.
 */
class MimeType implements IslandoraRepositoryReportsDataSourceInterface {

  /**
   * Returns the data source's name.
   *
   * @return string
   *   The name of the data source.
   */
  public function getName() {
    return t('Media MIME Type');
  }
 
  /**
   * Returns the data source's chart type.
   *
   * @return string
   *   Either 'pie' or 'bar'.
   */
  public function getChartType() {
    return 'pie';
  }

  /**
   * Gets the data.
   *
   * @return array
   *   An assocative array containing formatlabel => count members. 
   */
  public function getData() {

    if ($tempstore = \Drupal::service('user.private_tempstore')->get('islandora_repository_reports')) {
      if ($form_state = $tempstore->get('islandora_repository_reports_report_form_values')) {
        $media_use_term_ids = $form_state->getValue('islandora_repository_reports_media_use_terms');
      }
    }
    else {
      $config = \Drupal::config('islandora_repository_reports.settings');
      $media_use_term_ids = explode(',', $config->get('islandora_repository_reports_media_use_terms'));
    }

    $entity_type_manager = \Drupal::service('entity_type.manager');
    $media_storage = $entity_type_manager->getStorage('media');
    $result = $media_storage->getAggregateQuery()
      ->groupBy('field_mime_type')
      ->aggregate('field_mime_type', 'COUNT')
      ->condition('field_media_use', $media_use_term_ids, 'IN')
      ->execute();
    $format_counts = [];
    foreach ($result as $format) {
      $format_counts[$format['field_mime_type']] = $format['field_mime_type_count'];
    }
    return $format_counts;
  }

}
