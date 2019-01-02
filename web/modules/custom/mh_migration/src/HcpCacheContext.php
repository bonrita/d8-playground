<?php
/**
 * Created by PhpStorm.
 * User: bonaventure.wani
 * Date: 12/07/2018
 * Time: 22:43
 */

namespace Drupal\mh_migration;


use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines the WorkspaceCacheContext service, for "per workspace" caching.
 *
 * Cache context ID: 'hcp'.
 */
class HcpCacheContext implements CacheContextInterface {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $requestStack;


  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t('Hcp');
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $profile = $this->requestStack->getSession()->get('hcp-mode');
    $profile = empty($profile)? 'none' : $profile;
    return 'hcp.' . $profile;
  }


  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($type = NULL) {
    return new CacheableMetadata();
  }

}
