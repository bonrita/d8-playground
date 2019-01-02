<?php

namespace Drupal\mh_migration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RequestContext;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;
use Sabre\Xml\Writer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ConditionRouter extends ControllerBase {

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructs a new PathController.
   *
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   */
  public function __construct(RequestContext $request_context) {
    $this->requestContext = $request_context;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.request_context')
    );
  }

  /**
   * Testing conditional routes.
   *
   * @return array
   */
  public function condition($name) {
    $configs = [

    ];
    //    libxml_clear_errors();
    $url = "https://www.connexys.nl/omringpublic/run/xml_feed_int.startup?p_pub_id=1&p_code=51FBC25FD26DE4800DA5292D60539ADA";
    //    $xml_data = \Drupal::service('plugin.manager.migrate_plus.data_fetcher')
    //      ->createInstance('http')->getResponseContent($url);
    //
    //    $xml = simplexml_load_string($xml_data);


    $reader = new Reader();
    $reader->open($url);
    $result = $reader->parse();

    foreach ($result['value'] as $index => &$item) {
      if ('{}Vacancy' === $item['name']) {
        $item['value'][] = [
          'name' => '{}VacancyId',
          'value' => $item['attributes']['id'],
          'attributes' => [],
        ];
      }
    }

    $writer = new Service();

    $output = $writer->write('', $result);

    // --------------------
    //    $this->requestContext->
    // /admin/config/condition/box
    $tt = 0;
    return [
      '#markup' => 'Testing conditional routes: ' . $this->requestContext->getPathInfo(),
    ];
  }

}
