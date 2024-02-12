<?php

namespace Yeepliva\Router;

use Yeepliva\Controllers\View;
use Yeepliva\Core\Gateway;

/**
 * Yeepliva route.
 */
class Route
{
  /**
   * @var array $params The params of the route.
   */
  public array $params;

  /**
   * Initialization.
   * 
   * @param string $name The name of the route.
   * @param string $method The method of the route: "GET", "POST", ... Use "ALL" to match all request method.
   * @param string $path The path of the route: "/", "subdomain@/", "/article-{i:article_id}", ...
   * @param string $action The action of the route: "Controller@method".
   * @param false|array $primary The primary param of the route: "Language", "Location"
   * @return void
   */
  public function __construct(public string $name, public string $method, public string $path, private string $action, public false|array $primary)
  {
  }

  /**
   * Execute the route.
   * 
   * @param Gateway $gateway Yeepliva gateway.
   * @return View|false The route display.
   */
  public function execute(Gateway $gateway): View|false
  {
    // Separate the controller and the method
    $a = explode('@', $this->action);

    // Set controller and method
    $controller = 'Yeepliva\Controllers\\' . $a[0];
    $method = $a[1];

    // Check if the controller and the method exist
    if (class_exists($controller) && method_exists($controller, $method)) {
      // Call the method in the controller with request params
      $controller = new $controller($gateway);
      return $controller->$method($this->params);
    } else {
      return false;
    }
  }
}