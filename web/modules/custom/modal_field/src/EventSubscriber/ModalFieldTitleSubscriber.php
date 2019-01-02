<?php

namespace Drupal\modal_field\EventSubscriber;


use Drupal\modal_field\Event\ModalFieldEvents;
use Drupal\modal_field\Event\ModelOverrideTitleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ModalFieldTitleSubscriber implements EventSubscriberInterface {


  public function onOverrideTitle(ModelOverrideTitleEvent $event) {
    $event->setTitle('Bona overriding title');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    // Act only when the router rebuild is finished.
    $events[ModalFieldEvents::OVERRIDE_TITLE][] = ['onOverrideTitle'];
    return $events;
  }

}
