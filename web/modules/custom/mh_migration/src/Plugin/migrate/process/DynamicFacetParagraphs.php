<?php

namespace Drupal\mh_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Add necessary paragraphs.
 *
 * @MigrateProcessPlugin(
 *   id = "dynamic_facet_paragraphs"
 * )
 */
class DynamicFacetParagraphs extends ProcessPluginBase {

  /**
   * Performs the associated process.
   *
   * @param mixed $value
   *   The value to be transformed.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migration in which this process is being executed.
   * @param \Drupal\migrate\Row $row
   *   The row from the source to process. Normally, just transforming the value
   *   is adequate but very rarely you might need to change two columns at the
   *   same time or something like that.
   * @param string $destination_property
   *   The destination property currently worked on. This is only used together
   *   with the $row above.
   *
   * @return string|array
   *   The newly transformed value.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function create($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $paragraphs = [];

    $xmlstr = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<items>
$value
</items>
XML;

    $items = new \SimpleXMLElement($xmlstr);
    foreach ($items as $item) {

      // Create nested/child paragraphs.
      $facets = [];
      foreach ($item->Facets as $facet) {

        $paragraph = Paragraph::create([
          'type' => 'facet_item',
          'field_group_name' => $facet->Facet->GroupName,
          'field_value' => $facet->Facet->Value,
        ]);
        $paragraph->save();
        $facets[] = $paragraph;
      }

      // Create the parent paragraph.
      $paragraph = Paragraph::create([
        'type' => 'dynamic_facet',
        'field_name' => $item->Name,
        'field_facet' => $facets,
      ]);
      $paragraph->save();
      $paragraphs[] = $paragraph;
    }

    return $paragraphs;
  }

}
