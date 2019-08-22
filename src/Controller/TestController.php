<?php

/**
 *  TestController class
 * 
 * @author saauti
 */

namespace Drupal\c28_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Query\QueryFactory;

class TestController extends ControllerBase {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entity_query;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   An instance of ConfigFactoryInterface.
   * @param \Drupal\Core\Entity\Query\QueryFactory $entity_query
   *   An instance of QueryFactory
   */
  public function __construct(ConfigFactoryInterface $config_factory, QueryFactory $entity_query) {
    $this->configFactory = $config_factory;
    $this->entity_query = $entity_query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
            $container->get('config.factory'), $container->get('entity.query')
    );
  }

  public function content($api_key, $nid) {
    $node = Node::load($nid);
    $json_array = array(
        'data' => array()
    );
    $json_array['data'][] = array(
        'type' => $node->get('type')->target_id,
        'id' => $node->get('nid')->value,
        'attributes' => array(
            'title' => $node->get('title')->value,
            'content' => $node->get('body')->value,
        ),
    );
    return new JsonResponse($json_array);
  }

  /**
   * Checks access for a specific request.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access($api_key, $nid) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.
    $values = $this->entity_query->get('node')
            ->condition('nid', $nid)
            ->condition('type', 'page')
            ->execute();
    $site_api_key = $this->configFactory->getEditable('system.site')->get('siteapikey');
    if ($site_api_key == $api_key) {
      $assess = TRUE;
    }
    return AccessResult::allowedIf(!empty($values) && !empty($assess));
  }

}
