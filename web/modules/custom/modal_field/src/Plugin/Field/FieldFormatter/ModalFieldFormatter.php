<?php

namespace Drupal\modal_field\Plugin\Field\FieldFormatter;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Component\Serialization\Json;
use Drupal\modal_field\Event\ModelOverrideTitleEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the Modal formatter.
 *
 * @FieldFormatter(
 *   id = "modal_field_formatter",
 *   label = @Translation("Modal field"),
 *   field_types = {
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *     "string",
 *     "string_long"
 *   },
 *   title_field_types = {
 *     "string",
 *     "text"
 *   }
 * )
 */
class ModalFieldFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * Injected module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity filed manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The event dispatcher.
   *
   * @var \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher
   */
  protected $eventDispatcher;

  /**
   * ModalFieldFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   * @param \Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher $event_dispatcher
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ContainerAwareEventDispatcher $event_dispatcher) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      // Add any services you want to inject here.
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays a read more link which opens a modal.');
    $summary[] = $this->t('Width: @width', ['@width' => $this->getSetting('width')]);

    if (!empty($this->getSetting('label'))) {
      $summary[] = $this->t('Show more link label: @label', ['@label' => $this->getSetting('label')]);
    }

    if (!empty($this->getSetting('modal_title'))) {
      $field_name = $this->getSetting('modal_title');
      $summary[] = $this->t('Field that will be used to extract modal title: @field', ['@field' => $this->getFieldDefinition($field_name)->getLabel()]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [];

    // Fall back to field settings by default.
    $settings['modal_title'] = '';
    $settings['width'] = 400;
    $settings['label'] = '';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['modal_title'] = [
      '#type' => 'select',
      '#title' => $this->t('Field to extract title from'),
      '#default_value' => $this->getSetting('modal_title'),
      '#options' => $this->getFields(),
      '#description' => $this->t('Add a field to extract the modal title'),
    ];

    $form['width'] = [
      '#type' => 'number',
      '#title' => t('Modal width'),
      '#default_value' => $this->getSetting('width'),
      '#required' => TRUE,
      '#description' => t('Modal width.'),
      '#min' => 200,
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Show more link label'),
      '#maxlength' => 60,
      '#default_value' => $this->getSetting('label'),
    ];

    return $form;
  }

  /**
   * Collects fields that will possibly be used as modal title.
   *
   * @return string[]
   *   The array of fields
   */
  protected function getFields() {
    $context = 'view';
    $fields = ['' => $this->t('-- Select field --')];
    $field_definitions = array_filter($this->entityFieldManager->getFieldDefinitions($this->fieldDefinition->getTargetEntityTypeId(), $this->fieldDefinition->getTargetBundle()), function (FieldDefinitionInterface $field_definition) use ($context) {
      return $field_definition->getName() <> $this->fieldDefinition->getName()
        && $field_definition->isDisplayConfigurable($context)
        && $field_definition->getFieldStorageDefinition()->getCardinality() === 1
        && in_array($field_definition->getType(), $this->pluginDefinition['title_field_types'], TRUE);
    });

    /** @var \Drupal\field\Entity\FieldConfig $field_definition */
    foreach ($field_definitions as $field_name => $field_definition) {
      $fields[$field_name] = $field_definition->label();
    }

    return $fields;
  }

  /**
   * Get field definition.
   *
   * @param string $field_name
   *   The field name.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface
   */
  protected function getFieldDefinition($field_name) {
    $fields = $this->entityFieldManager->getFieldDefinitions($this->fieldDefinition->getTargetEntityTypeId(), $this->fieldDefinition->getTargetBundle());
    return $fields[$field_name];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];

    /** @var  $item \Drupal\Core\Field\FieldItemInterface */
    foreach ($items as $delta => $item) {
      $field_name = $item->getFieldDefinition()->get('field_name');
      $entity_type = $item->getFieldDefinition()->get('entity_type');
      $field_label = empty($this->getSetting('label')) ? $item->getFieldDefinition()->getLabel() : $this->getSetting('label');
      $id = $item->getEntity()->id();

      $link_url = Url::fromRoute('modal_field.modal', ['id' => $id, 'entity_type' => $entity_type, 'field_name' => $field_name]);
      $link_url->setOptions([
        'attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'classes' => [
              'ui-dialog' => 'ui-dialog ui-widget',
              'ui-dialog-titlebar-close' => 'button',
            ],
            'closeText' => $this->t('Close'),
            'width' => $this->getSetting('width'),
          ]),
        ],
      ]);

      // Allow other modules to alter the field label.
      $this->moduleHandler->alter('modal_field_show_more_link_label', $field_label, $field_name);

      $link_text = $this->t('Show more<span class="visually-hidden"> information about @field_label</span>', ['@field_label' => strtolower($field_label)]);
      $element[$delta] = [
        '#markup' => Link::fromTextAndUrl($link_text, $link_url)->toString(),
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      ];
    }

    return $element;
  }

  /**
   * get view mode.
   *
   * @return string
   */
  public function getViewMode() {
    return $this->viewMode;
  }

}
