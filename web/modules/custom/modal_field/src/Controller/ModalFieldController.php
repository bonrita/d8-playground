<?php

namespace Drupal\modal_field\Controller;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\modal_field\Event\ModalFieldEvents;
use Drupal\modal_field\Event\ModelOverrideTitleEvent;
use Drupal\modal_field\Plugin\Field\FieldFormatter\ModalFieldFormatter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ModalFieldController.
 */
class ModalFieldController extends ControllerBase {

  /**
   * Injected service 'entity_display.repository'.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplay;

  /**
   * The node-object to get the data from.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The field to get the data from.
   *
   * @var string
   */
  protected $fieldName;

  /**
   * The entity type.
   *
   * @var string
   */
  protected $entityType;

  /**
   * The dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * The logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Field\FormatterInterface
   */
  protected $formatter;

  /**
   * Constructs a new ModalFieldController.
   *
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display
   *   The injected entity_display service.
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   *   The dispatcher.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger.
   */
  public function __construct(EntityDisplayRepositoryInterface $entity_display, ContainerAwareEventDispatcher $event_dispatcher, LoggerChannelFactoryInterface $logger) {
    $this->entityDisplay = $entity_display;
    $this->eventDispatcher = $event_dispatcher;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_display.repository'),
      $container->get('event_dispatcher'),
      $container->get('logger.factory')
    );
  }

  /**
   * Ajax callback for the modal.
   *
   * Returns the contents of a single field in a modal.
   *
   * @param string $entity_type
   *   The entity type.
   * @param int $id
   *   The entity id.
   * @param string $field_name
   *   The field to get the data from.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AjaxResponse to be used in a modal.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function modal($entity_type, $id, $field_name) {
    $data = '';
    $title = '';

    $this->entityType = $entity_type;
    $this->entity = $this->entityTypeManager()
      ->getStorage($entity_type)
      ->load($id);
    $this->fieldName = $field_name;

    try {

      if ($this->isAllowed()) {
        $data = $this->getData();
        $title = $this->getTitle();
      }

    } catch (\RuntimeException $e) {
      $this->logger->get('modal_field')
        ->error('The entity type: @type of ID: @id does not exist. Error on line: @line message: @message in file: @file', [
          '@type' => $entity_type,
          '@id' => $id,
          '@line' => $e->getLine(),
          '@message' => $e->getMessage(),
          '@file' => $e->getFile(),
        ]);
    }

    $options = [
      'dialogClass' => 'popup-dialog-class',
      'width' => '50%',
    ];

    $response = new AjaxResponse();
    $response->addCommand(new OpenModalDialogCommand($title, $data, $options));

    return $response;
  }

  /**
   * Check if we are allowed to display the field.
   *
   * We check all view-modes, to see if the field has been setup to use this
   * module as its formatter. If that is the case, we allow access.
   *
   * @return bool
   *   TRUE if we are allowed access, FALSE otherwise.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function isAllowed() {

    if (empty($this->entity)) {
      throw new \RuntimeException('You must provide a valid entity instance.');
    }

    $formats = [];
    $bundle = $this->entity->bundle();

    // Get selected view modes for bundle.
    $view_modes = $this->entityDisplay
      ->getViewModeOptionsByBundle(
        $this->entityType, $bundle
      );

    // Get format settings for field.
    foreach (array_keys($view_modes) as $view_mode) {
      $formats[$view_mode] = $this->entityTypeManager()
        ->getStorage('entity_view_display')
        ->load($this->entityType . '.' . $bundle . '.' . $view_mode)
        ->getRenderer($this->fieldName);
    }

    foreach (array_filter($formats) as $formatter) {
      if ($formatter instanceof ModalFieldFormatter && $formatter->getPluginDefinition()['id'] === 'modal_field_formatter') {
        $this->formatter = $formatter;
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Get the modal data.
   *
   * Get the data that will be displayed in the modal, based on field_name.
   *
   * @return mixed
   *   The contents of the field.
   */
  private function getData() {
    $data = $this->entity->get($this->fieldName)->isEmpty() ? '' : $this->entity->get($this->fieldName)->view([
      'label' => 'hidden',
    ]);

    return $data;
  }

  /**
   * Get the modal title.
   *
   * Defaults to the node title. Other modules can alter the title.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|null|string
   *   A title for use in the modal.
   */
  private function getTitle() {

    $field_title = $this->formatter->getSetting('modal_title');

    if (!empty($field_title) && !$this->entity->get($field_title)->isEmpty()) {
      $title = $this->entity->get($field_title)->get(0)->get('value')->getCastedValue();
    }
    else {
      $title = $this->entity->label();
    }

    $context = [
      'entity_type' => $this->entityType,
      'field_name' => $this->fieldName,
      'bundle' => $this->entity->bundle(),
      'id' => $this->entity->id(),
      'field_title' => $field_title,
    ];

    $event = new ModelOverrideTitleEvent($title, $context);
    $this->eventDispatcher->dispatch(ModalFieldEvents::OVERRIDE_TITLE, $event);

    return $event->getTitle();
  }

}
