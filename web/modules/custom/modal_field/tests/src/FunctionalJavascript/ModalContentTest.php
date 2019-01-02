<?php

namespace Drupal\Tests\modal_field\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
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

/**
 * Class ModalContentTest.
 *
 * @package Drupal\Tests\modal_field\FunctionalJavascript
 */
class ModalContentTest extends WebDriverTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['field', 'node', 'modal_field', 'user'];

  /**
   * A user with permission to administer content types, node fields, etc.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

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
    putenv("SIMPLETEST_BASE_URL=http://dev.drupal8project.com");
    parent::setUp();

//    \Drupal::service('theme_installer')->install(['bartik', 'seven']);
//    $theme_config = \Drupal::configFactory()->getEditable('system.theme');
//    $theme_config->set('admin', 'seven');
//    $theme_config->set('default', 'bartik');
//    $theme_config->save();
//
//    // Create admin user.
//    $this->adminUser = $this->drupalCreateUser([
//      'access content',
//      'administer content types',
//      'view the administration theme',
////      'administer node fields',
////      'administer node form display',
//      'bypass node access',
//    ]);
//    $this->drupalLogin($this->adminUser);

//    $this->fieldName = mb_strtolower($this->randomMachineName());
//    $this->bundle = mb_strtolower($this->randomMachineName());
//    $this->title = $this->getRandomGenerator()->word(10);
//    $this->content = $this->getRandomGenerator()->sentences(6);
//
//    // Create a node.
//    $this->drupalCreateContentType(['type' => $this->bundle]);
//
//    // Create a text field.
//    $field_storage = FieldStorageConfig::create([
//      'field_name' => $this->fieldName,
//      'entity_type' => 'node',
//      'type' => 'text_long',
//    ]);
//    $field_storage->save();
//
//    FieldConfig::create([
//      'entity_type' => 'node',
//      'field_name' => $this->fieldName,
//      'bundle' => $this->bundle,
//      'label' => 'Test text-field',
//    ])->save();
//
//    // Create view display.
//    $display = EntityViewDisplay::create([
//      'targetEntityType' => 'node',
//      'bundle' => $this->bundle,
//      'mode' => 'full',
//      'status' => TRUE,
//    ])->setComponent($this->fieldName, [
//      'type' => 'modal_field_formatter',
//    ]);
//    $display->save();
//
//    // Create a node.
//    $this->node = $this->createNode([
//      'type' => $this->bundle,
//      'title' => $this->title,
//    ]);
//
//    $this->node->set($this->fieldName, $this->content);
//    $this->node->save();

  }

  public function testLinkShowsOnPage() {
    \Drupal::service('theme_installer')->install(['bartik', 'seven']);
    $theme_config = \Drupal::configFactory()->getEditable('system.theme');
    $theme_config->set('admin', 'seven');
    $theme_config->set('default', 'bartik');
    $theme_config->save();

    // Create admin user.
    $this->adminUser = $this->drupalCreateUser([
//      'access content',
//      'administer content types',
      'view the administration theme',
      //      'administer node fields',
      //      'administer node form display',
//      'bypass node access',
    ]);
    $this->drupalLogin($this->adminUser);


//    $session = $this->getSession();
//    $page = $session->getPage();
//    $assert_session = $this->assertSession();

//    $this->drupalGet("node/{$this->node->id()}");
//    $assert_session->pageTextContains($this->node->label());
  }

}
