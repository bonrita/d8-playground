<?php

namespace Drupal\Tests\modal_field\Kernel;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Link;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Logger\LoggerChannel;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\modal_field\Controller\ModalFieldController;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ModalFieldFormatterTest extends KernelTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'field',
    'text',
    'entity_test',
    'user',
    'modal_field',
    'node'
  ];

  /**
   * @var string
   */
  protected $entityType;

  /**
   * @var string
   */
  protected $bundle;

  /**
   * @var string
   */
  protected $fieldName;

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
   * @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface
   */
  protected $display;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    putenv("SIMPLETEST_DB=mysql://root:welkom@127.0.0.1:3307/drupal_test");
    parent::setUp();

    $this->installConfig(['system']);
    $this->installEntitySchema('entity_test');
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installConfig(['field', 'node']);

    $this->entityType = 'node';
    $this->bundle = mb_strtolower($this->randomMachineName());
    $this->fieldName = mb_strtolower($this->randomMachineName());
    $this->title = $this->getRandomGenerator()->word(10);
    $this->content = $this->getRandomGenerator()->sentences(6);

    // Create a node.
    NodeType::create([
      'type' => $this->bundle,
    ])->save();

    $field_storage = FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityType,
      'type' => 'text_long',
    ]);
    $field_storage->save();

    $instance = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => $this->bundle,
      'label' => $this->randomMachineName(),
    ]);
    $instance->save();

    $this->display = entity_get_display($this->entityType, $this->bundle, 'default')
      ->setComponent($this->fieldName, [
        'type' => 'boolean',
        'settings' => [],
      ]);
    $this->display->save();
  }

  public function testLabel() {

    // Create a node.
    $this->node = Node::create([
      'type' => $this->bundle,
      'title' => $this->title,
      $this->fieldName => [
        'value' => $this->content,
      ]
    ]);
    $this->node->save();



    $field_label = 'entity_test';

    $settings = [];
    $settings['modal_title'] = '';
    $settings['width'] = 400;
    $settings['label'] = $field_label;

    $component = $this->display->getComponent($this->fieldName);
    $component['type'] = 'modal_field_formatter';
    $component['settings'] = $settings;
    $this->display->setComponent($this->fieldName, $component);


    $link_url = Url::fromRoute('modal_field.modal', ['id' => $this->node->id(), 'entity_type' => $this->entityType, 'field_name' => $this->fieldName]);
    $link_text = $this->t('Show more<span class="visually-hidden"> information about @field_label</span>', ['@field_label' => strtolower($field_label)]);

//    $expected = [
//      '#markup' => Link::fromTextAndUrl($link_text, $link_url)->toString(),
//      '#attached' => ['library' => ['core/drupal.dialog.ajax']],
//    ];


    $expected = Link::fromTextAndUrl($link_text, $link_url)->toString();

    $this->renderEntityFields($this->node, $this->display);
    $this->assertRaw($expected);
  }

  /**
   * Renders fields of a given entity with a given display.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity object with attached fields to render.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The display to render the fields in.
   *
   * @return string
   *   The rendered entity fields.
   */
  protected function renderEntityFields(FieldableEntityInterface $entity, EntityViewDisplayInterface $display) {
    $content = $display->build($entity);
    $content = $this->render($content);
    return $content;
  }

}
