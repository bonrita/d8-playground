<?php

namespace Drupal\mh_migration\Plugin\migrate_plus\data_fetcher;

use Dompdf\Exception;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\Plugin\migrate_plus\data_fetcher\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieve data from a local path or general URL for migration.
 *
 * @DataFetcher(
 *   id = "xml_dir_concat",
 *   title = @Translation("Directory Concatenator")
 * )
 */
class XMLDirConcat extends File implements ContainerFactoryPluginInterface {

  /**
   * The channel logger object.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelFactoryInterface $logger_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('mh_migration');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  /**
   * Return Http Response object for a given url.
   *
   * @param string $url
   *   URL to retrieve from.
   *
   * @return string
   *   The concatenated xml file.
   *
   * @throws \Drupal\migrate\MigrateException
   */
  public function getResponse($url) {
    // Because l am using file paths relative to the drupal root,
    // l add the drupal root here.
    $url = DRUPAL_ROOT . $url;
    $files = scandir($url);
    // This is a quick hack to combine a number of xml files in a directory
    // I merge them, then add one <?xml... tag on top, and wrap everything
    // inside the <items> tag, so migrations will need to add this to its
    // item_selector.
    $output = [
      "<?xml version=\"1.0\" encoding=\"utf-8\"?>",
      "<items>",
    ];
    try {
      if ($files) {
        foreach ($files as $file_name) {
          $file_to_check = $url . DIRECTORY_SEPARATOR;
          if (preg_match("/.*xml$/", $file_name)) {
            $file_to_check .= $file_name;
          }
          else {
            continue;
          }

          // Make sure that only files make it here!
          if (is_file($file_to_check)) {
            $file_contents = file_get_contents($file_to_check);
            // Remove the 1st line in every xml file.
            $file_contents = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $file_contents);
            $output[] = $file_contents;
          }
        }
        $output[] = "</items>";

        return implode("", $output);
      }
      else {
        throw new MigrateException(sprintf('The Directory %s is empty or does not match given pattern', $url));
      }
    }
    catch (Exception $e) {
      $this->logger
        ->error('The Directory' . $url . 'is empty or does not match given pattern');
      sprintf('The Directory %s is empty or does not match given pattern', $url);

    }

  }

}
