<?php

namespace Yeepliva\Core;

use Yeepliva\Language\Translator;
use Yeepliva\Router\Route;
use Yeepliva\Router\Router;

/**
 * Yeepliva gateway.
 */
class Gateway
{
  /**
   * @var Configurator $configurator Yeepliva configurator.
   */
  private Configurator $configurator;

  /**
   * @var CookieManager $cookie_manager Yeepliva cookie manager.
   */
  private CookieManager $cookie_manager;

  /**
   * @var Router $router Yeepliva router.
   */
  private Router $router;

  /**
   * @var Translator $translator Yeepliva translator.
   */
  private Translator $translator;

  /**
   * Push object(s) to gateway.
   * 
   * @param array $objects The object(s) to be push.
   * @return void
   */
  public function push(array $objects): void
  {
    foreach ($objects as $object_name => $object_data) {
      if (property_exists(__CLASS__, $object_name)) {
        $this->$object_name = $object_data;
      }
    }
  }

  /**
   * Pull object(s) from gateway.
   * 
   * @param string|array $objects The object(s) to be pull.
   * @return string|array Object(s)
   */
  public function pull(string|array $objects): string|array
  {
    // Convert to an array
    if (!is_array($objects)) {
      $objects = [$objects];
      $not_array = true;
    } else {
      $not_array = false;
    }

    foreach ($objects as $object_name) {
      if (property_exists(__CLASS__, $object_name)) {
        $objects_to_return[] = $this->$object_name;
      } else {
        $objects_to_return[] = null;
      }
    }

    // Extract of array if it was not one at the beginning
    if ($not_array) {
      $objects_to_return = $objects_to_return[0];
    }

    return $objects_to_return;
  }
}