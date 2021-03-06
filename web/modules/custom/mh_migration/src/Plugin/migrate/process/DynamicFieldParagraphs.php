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
 *   id = "dynamic_field_paragraphs"
 * )
 */
class DynamicFieldParagraphs extends ProcessPluginBase {

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
      $paragraph = Paragraph::create([
        'type' => 'dynamic_field',
        'field_name' => $item->Name,
        'field_value' => $item->Value,
      ]);
      $paragraph->save();
      $paragraphs[] = $paragraph;
    }

    return $paragraphs;
  }

}
