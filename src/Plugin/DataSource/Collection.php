<?php

namespace Drupal\islandora_repository_reports\Plugin\DataSource;

/**
 * Data source plugin that gets nodes by Islandora collection.
 */
class Collection implements IslandoraRepositoryReportsDataSourceInterface {

  /**
   * An array of arrays corresponding to CSV records.
   *
   * @var string
   */
  public $csvData;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return t('Repository item count by Collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseEntity() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public function getChartType() {
    return 'pie';
  }

  /**
   * {@inheritdoc}
   */
  public function getChartTitle($total) {
    return t('@total total repository items broken down by collection. (If a collection is not listed here, it\'s because it contains no repository items directly.)', ['@total' => $total]);
  }

  /**
   * {@inheritdoc}
   */
  public function getData() {
    $utilities = \Drupal::service('islandora_repository_reports.utilities');
    if (count($utilities->getSelectedContentTypes()) == 0) {
      return [];
    }

    $entity_type_manager = \Drupal::service('entity_type.manager');
    $node_storage = $entity_type_manager->getStorage('node');

    // get the collections
    $result = $node_storage->getQuery()->condition('type','collection_object')->execute();

    // now see how many items reference the collection
    $collection_counts = [];
    foreach ($result as $col=>$col_id) {
      $items = $node_storage->getQuery()
        ->condition('type', ['islandora_object'], 'IN')
        ->condition('field_member_of', [$col_id], 'IN')
        ->execute();

      if(($num_items = count($items)) > 0) {
        $collection_node = \Drupal::entityTypeManager()->getStorage('node')->load($col_id);
        $collection_counts[$collection_node->getTitle()] = $num_items;
      }
    }

    $this->csvData = [[t('Collection'), 'Count']];
    foreach ($collection_counts as $collection => $count) {
      $this->csvData[] = [$collection, $count];
    }

    return $collection_counts;
  }

}
