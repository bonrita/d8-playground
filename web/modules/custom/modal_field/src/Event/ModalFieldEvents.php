<?php

namespace Drupal\modal_field\Event;

/**
 * Class ModalFieldEvents.
 *
 * Defines Modal field events.
 *
 * @package Drupal\modal_field\Event
 */
final class ModalFieldEvents {

  /**
   * The name of the event that is fired to override the modal title.
   *
   *  @see \Drupal\modal_field\Event\ModelOverrideTitleEvent
   */
  const OVERRIDE_TITLE = 'drupal.modal_field.override_title';

  /**
   * The name of the event that is fired to override the modal title.
   *
   *  @see \Drupal\modal_field\Event\ModelOverrideShowMoreLinkLabelEvent
   */
  const OVERRIDE_SHOW_MORE_LINK_LABEL = 'drupal.modal_field.override_show_more_link_label';

}
