<?php

namespace Drupal\mh_migration\Plugin\migrate_plus\data_parser;

use Dompdf\Exception;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\SimpleXml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Obtain XML data for migration using the SimpleXML API.
 *
 * @DataParser(
 *   id = "mh_simple_xml",
 *   title = @Translation("Mh Simple XML")
 * )
 */
class MhSimpleXml extends SimpleXml implements ContainerFactoryPluginInterface {

  /**
   * Collection of unique ids.
   *
   * @var array
   */
  protected $uniqueIds = [];

  /**
   * The channel logger object.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('mh_migration');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $target_element = array_shift($this->matches);

    // If i find the desired element, i populate the currentItem and
    // currentId with its data.
    if ($target_element !== FALSE && !is_null($target_element)) {
      try {
        foreach ($this->fieldSelectors() as $field_name => $xpath) {
          foreach ($target_element->xpath($xpath) as $value) {
            // Going true each value in xml defined by previous foreach.
            if ($value->count() < 1) {
              // Collecting data for all single valued field.
              $this->currentItem[$field_name][] = (string) $value;
            }
            else {
              // I want to maintain the literal expression as it appears on the
              // XML document.
              $parts = [];
              foreach ($value->children() as $child) {
                // Collecting all sub fields for the main category.
                /** @var \SimpleXMLElement $child */
                $parts[] = $child->asXML();
              }

              // Implode all data into element.
              $this->currentItem[$field_name][] = implode("\n", $parts);
            }
          }
        }
      }
      catch (Exception $exception) {
        $this->logger->error("Wrong format" . $this->fieldSelectors());
      }

      // Reduce single-value results to scalars.
      foreach ($this->currentItem as $field_name => $values) {
        if (count($values) == 1) {
          $this->currentItem[$field_name] = reset($values);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * The method is overridden because incases where the data contains duplicate
   * ids, the migration dependency requirement will fail as this method
   * returns a count of duplicated ids.
   *
   * @see \Drupal\migrate\Plugin\Migration::allRowsProcessed()
   */
  public function count() {
    if (!isset($this->configuration["unique"])) {
      return parent::count();
    }
    $id_key = $this->getIdKey();
    $count = 0;
    foreach ($this as $item) {
      if (in_array($item[$id_key], $this->uniqueIds)) {
        continue;
      }
      $this->uniqueIds[$item[$id_key]] = $item[$id_key];
      $count++;
    }
    return $count;
  }

  /**
   * Get the migration id.
   */
  protected function getIdKey() {
    return reset(array_keys($this->configuration["ids"]));
  }

}
