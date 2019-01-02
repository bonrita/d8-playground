<?php

namespace Drupal\mh_migration\Plugin\migrate_plus\data_fetcher;

use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\Http;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

/**
 * Rebuild the xml that is going to be processed.
 *
 * @DataFetcher(
 *   id = "xml_rebuild",
 *   title = @Translation("XML rebuild")
 * )
 */
class XMLRebuild extends Http {

  /**
   * {@inheritdoc}
   */
  public function getResponse($url) {
    $reader = new Reader();
    $reader->open($url);
    $result = $reader->parse();

    foreach ($result['value'] as $index => &$item) {
      if ('{}Vacancy' === $item['name']) {
        $item['value'][] = [
          'name' => '{}VacancyId',
          'value' => $item['attributes']['id'],
          'attributes' => [],
        ];
      }
    }

    $writer = new Service();

    return $writer->write('', $result);
  }

  /**
   * {@inheritdoc}
   */
  public function getResponseContent($url) {
    return $this->getResponse($url);
  }

}