<?php

namespace Drupal\Tests\modal_field\Functional;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Class ControllerTest.
 *
 * @group modal_field
 */
class ControllerTest extends BrowserTestBase {

  use StringTranslationTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = [
    'field',
    'node',
    'modal_field',
    'entity_test',
    'user',
    'system',
  ];

  /**
   * A user with permission to administer content types, node fields, etc.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    putenv("SIMPLETEST_BASE_URL=http://dev.drupal8project.com");
    parent::setUp();

    // Create default content type.
    $this->drupalCreateContentType(['type' => 'article']);

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'administer content types',
      'administer node fields',
      'administer node form display',
      'bypass node access',
    ]);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests that empty data is returned.
   *
   * That is if the path has been supplied with a field that does not exist
   * on an entity.
   */
//  public function testEmptyDataOnNonExistingField() {
////    'field_test_text' => [
////      'value' => 'no access value',
////      'format' => 'full_html',
////    ],
//
//    $entity_label = $this->getRandomGenerator()->word(10);
//    $field_content_value = $this->getRandomGenerator()->word(7);
//
//    $field = $this->createModalField('string', [
//      'modal_title' => '',
//      'width' => 400,
//      'label' => '',
//    ]);
//
//    $values = [
//      'name' => $entity_label,
//      'user_id' => 1,
//      $field->getName() => [
//        'value' => $field_content_value,
//      ],
//    ];
//    $entity = EntityTest::create($values);
//
//  }


  /**
   * Creates a modal field and set's the correct formatter.
   *
   * @param string $field_type
   *   The field type.
   * @param array $formatter_settings
   *   Settings for the formatter.
   *
   * @return \Drupal\field\Entity\FieldConfig
   *   Newly created modal field.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createModalField($field_type, array $formatter_settings = []) {
    $entity_type = $bundle = 'entity_test';
    $field_name = mb_strtolower($this->randomMachineName());

    FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => $field_type,
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    ])->save();
    $field_config = FieldConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'bundle' => $bundle,
      'settings' => [],
    ]);
    $field_config->save();

    $display = entity_get_display('entity_test', 'entity_test', 'full');
    $display->setComponent($field_name, [
      'type' => 'modal_field_formatter',
      'settings' => $formatter_settings,
    ])->save();

    return $field_config;
  }

  /**
   * Data provider for testRender.
   *
   * @return array
   *   An array of data arrays.
   *   The data array contains:
   *     - The number of expected HTML tags.
   *     - An array of settings for the field formatter.
   */
  public function dataProvider() {
    return [
      [2, []],
      [1, ['multiple_file_display_type' => 'sources']],
    ];
  }


}
