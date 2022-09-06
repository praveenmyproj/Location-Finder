<?php

namespace Drupal\location_finder\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Location & Time' block.
 *
 * @Block(
 *   id = "location_time_Block",
 *   admin_label = @Translation("Location & Time")
 * )
 */
class LocationFront extends BlockBase  implements ContainerFactoryPluginInterface {

  /**
   * @var service variable
   */
  protected $locationService;

  /**
   *  @param array $configuration
   *  @param string $plugin_id
   *  @param mixed $plugin_definition
   *  @param \Drupal\location_finder\LocationService $locationService
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $locationService) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->locationService = $locationService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('location.items.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $locDetails = $this->locationService->getLocation();
    $country = $city = $time = '';
    if (!empty($locDetails)) {
      $country = $locDetails['country'];
      $city = $locDetails['city'];
      $time = $locDetails['time'];
    }
    return [
      '#theme' => 'locationtimeblock',
      '#loc_country' => $country,
      '#loc_city' => $city,
      '#loc_time' => $time,
      '#cache' => ['max-age' => $locDetails['totalSeconds']],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return Cache::mergeTags(parent::getCacheTags(), array('locationtimeblocktags'));
  }
}
