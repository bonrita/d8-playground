<?php

namespace Drupal\dev_test\Command;

use Drupal\developer_suite\Command\Command;

/**
 * Class ValidateNameCommand.
 *
 * @package Drupal\dev_test\Command
 */
class ValidateNameCommand extends Command {

  /**
   * {@inheritdoc}
   */
  public function getCommandHandlerPluginId() {
    return 'validate_name_command_handler';
  }

}
