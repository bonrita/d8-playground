<?php

namespace Drupal\dev_test\Plugin\CommandHandler;

use Drupal\developer_suite\Collection\ViolationCollectionInterface;
use Drupal\developer_suite\Command\CommandHandler;

/**
 * Class ValidateNameCommandHandler.
 *
 * @CommandHandler(
 *   id = "validate_name_command_handler",
 *   label = @Translation("Validate username supplied"),
 * )
 *
 * @package Drupal\dev_test\Plugin\CommandHandler
 */
class ValidateNameCommandHandler extends CommandHandler {

  /**
   * {@inheritdoc}
   */
  public function handle() {
    // TODO: Implement handle() method.
  }

  /**
   * {@inheritdoc}
   */
  public function postValidationFailed(ViolationCollectionInterface $violationCollection) {
    // TODO: Implement postValidationFailed() method.
  }

  /**
   * {@inheritdoc}
   */
  public function preValidationFailed(ViolationCollectionInterface $violationCollection) {
    // TODO: Implement preValidationFailed() method.
  }

}
