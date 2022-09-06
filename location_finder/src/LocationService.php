<?php

namespace Drupal\location_finder;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Cache\Cache;

/**
 * Defines an importer of location items.
 */
class LocationService {

  /**
   * The location.settings config object.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Constructs an Importer object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->config = $configFactory->get('location.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function getLocation() {
    $cache = \Drupal::cache();
    $cacheData = $cache->get('location_time');
    if (empty($cacheData)) {
      $location = [];
      if (!empty($this->config->get('country'))) {
        $location['country'] = $this->config->get('country');
      }
      if (!empty($this->config->get('city'))) {
        $location['city'] = $this->config->get('city');
      }
      if (!empty($this->config->get('timezone'))) {
        $date = new DrupalDateTime();
        $date->setTimezone(new \DateTimeZone($this->config->get('timezone')));
        $location['time'] = $date->format('jS M Y - g:i A');
        $seconds = $date->format('s');
        $location['totalSeconds'] = 60 - $seconds;
      }
      $cache->set('location_time', $location, \Drupal::time()->getRequestTime() + $location['totalSeconds'], ['location_time_tag']);
      return $location;
    }
    else {
      return $cacheData->data;
    }
  }

}
