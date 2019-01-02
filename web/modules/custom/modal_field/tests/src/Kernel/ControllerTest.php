<?php

namespace Drupal\Tests\modal_field\Kernel;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\modal_field\Controller\ModalFieldController;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ControllerTest.
 *
 * @coversDefaultClass \Drupal\modal_field\Controller\ModalFieldController
 *
 * @group modal_field
 */
class ControllerTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var string[]
   */
  public static $modules = [
    'field',
    'node',
    'user',
    'system',
    'text',
    'modal_field'
  ];

  /**
   * The node object.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The event dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * The modal field controller.
   *
   * @var \Drupal\modal_field\Controller\ModalFieldController
   */
  protected $controller;

  /**
   * @var string
   */
  protected $fieldName;

  /**
   * The bundle.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The title of the node.
   *
   * @var string
   */
  protected $title;

  /**
   * The content of the field.
   *
   * @var string
   */
  protected $content;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // For some reason the variables in phpunit.xml file are not loaded.
    // So fix it here. Remove it from final code.
    putenv("SIMPLETEST_DB=mysql://root:welkom@127.0.0.1:3307/drupal_test");

    parent::setUp();

    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['field', 'node']);

    $this->fieldName = mb_strtolower($this->randomMachineName());
    $this->bundle = mb_strtolower($this->randomMachineName());
    $this->title = $this->getRandomGenerator()->word(10);
    $this->content = $this->getRandomGenerator()->sentences(6);

    // Create a node.
    NodeType::create([
      'type' => $this->bundle,
    ])->save();

    // Create a text field.
    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => 'node',
      'type' => 'text_long',
    ]);
    $field_storage->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => $this->fieldName,
      'bundle' => $this->bundle,
      'label' => 'Test text-field',
    ])->save();

    // Create view display.
    $display = EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => $this->bundle,
      'mode' => 'full',
      'status' => TRUE,
    ])->setComponent($this->fieldName, [
      'type' => 'modal_field_formatter',
    ]);
    $display->save();

    // Create a node.
    $this->node = Node::create([
      'type' => $this->bundle,
      'title' => $this->title,
      $this->fieldName => [
        'value' => $this->content,
      ]
    ]);
    $this->node->save();

    // Mock required services.
    $entityDisplayRepository = $this->container->get('entity_display.repository');
    $eventDispatcher = $this->container->get('event_dispatcher');
    $logger = $this->createMock(LoggerChannelFactory::class);
    $logger->expects($this->any())
      ->method('get')
      ->with('modal_field')
      ->will($this->returnValue(new LoggerChannel('modal_field')));

    // Controller instance.
    $this->controller = new ModalFieldController($entityDisplayRepository, $eventDispatcher, $logger);

    // SEE HOW CONTROLLER EXCEPTIOS ARE TESTED.
    // /usr/local/var/www/drupal-project/web/core/modules/media/tests/src/Kernel/OEmbedIframeControllerTest.php
  }

  /**
   * Test field returns data if configured as expected.
   */
  public function testDataOnConfiguredField() {
    $response = $this->controller->modal('node', $this->node->id(), $this->fieldName);
    $this->assertNotEmpty($response->getCommands()[0]['data']);
  }

  /**
   * Tests that empty data is returned.
   *
   * That is if the path has been supplied with a field that does not exist
   * on an entity.
   */
  public function testEmptyDataOnNonExistingField() {

//    $yy = [
//      0 =>
//        [
//          'command' => 'openDialog',
//          'selector' => '#drupal-modal',
//          'settings' => NULL,
//          'data' => '',
//          'dialogOptions' =>
//            [
//              'dialogClass' => 'popup-dialog-class',
//              'width' => '50%',
//              'modal' => TRUE,
//              'title' => '',
//            ],
//        ],
//    ];

    $response = $this->controller->modal('node', $this->node->id(), 'field_does_not_exist');
    $this->assertEmpty('', $response->getCommands()[0]['data']);

  }

  /**
   * Tests that empty data is returned.
   *
   * That is if the path has been supplied with an entity that does not exist.
   */
  public function testEmptyDataOnNonExistingEntity() {
    $response = $this->controller->modal('node', rand(1000, 3000), 'field_does_not_exist');
    $this->assertEmpty('', $response->getCommands()[0]['data']);
  }

  /**
   * Only a single command should be returned from the controller action method.
   */
  public function testAjaxCommandOpenDialogIsExecuted() {
    $response = $this->controller->modal('node', $this->node->id(), 'field_does_not_exist');
    $this->assertEquals('openDialog', $response->getCommands()[0]['command']);
  }

  /**
   * Only a single command should be returned from the controller action method.
   */
  public function testSingleCommandIsReturned() {
    $response = $this->controller->modal('node', $this->node->id(), 'field_does_not_exist');
    $this->assertCount(1, $response->getCommands());
  }

}
