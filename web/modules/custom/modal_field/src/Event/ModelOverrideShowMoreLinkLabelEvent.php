<?php

namespace Drupal\modal_field\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ModelOverrideShowMoreLinkLabelEvent.
 *
 * @package Drupal\modal_field\Event
 */
class ModelOverrideShowMoreLinkLabelEvent extends Event {

  /**
   * The original modal title.
   *
   * @var string
   */
  protected $label;

  /**
   * The context.
   *
   * @var array
   */
  protected $context;

  /**
   * ModelOverrideTitleEvent constructor.
   *
   * @param string $label
   *   The original modal title.
   * @param array $context
   *   The context.
   */
  public function __construct($label, array $context) {
    $this->label = $label;
    $this->context = $context;
  }

  /**
   * The title.
   *
   * @param string $label
   *   The label.
   */
  public function setLabel($label) {
    $this->label = $label;
  }

  /**
   * Get title.
   *
   * @return string
   *   The label.
   */
  public function getLabel() {
    return $this->label;
  }

}
