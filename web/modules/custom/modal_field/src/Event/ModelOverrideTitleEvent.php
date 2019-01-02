<?php

namespace Drupal\modal_field\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class ModelOverrideTitleEvent.
 *
 * @package Drupal\modal_field\Event
 */
class ModelOverrideTitleEvent extends Event {

  /**
   * The original modal title.
   *
   * @var string
   */
  protected $title;

  /**
   * The context.
   *
   * @var array
   */
  protected $context;

  /**
   * ModelOverrideTitleEvent constructor.
   *
   * @param string $title
   *   The original modal title.
   * @param array $context
   *   The context.
   */
  public function __construct($title, array $context) {
    $this->title = $title;
    $this->context = $context;
  }

  /**
   * The title.
   *
   * @param string $title
   *   The title.
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * Get title.
   *
   * @return string
   *   The title.
   */
  public function getTitle() {
    return $this->title;
  }

}
