<?php

namespace Yeepliva\Router;

use Yeepliva\Exceptions\RouteNotFoundException;

/**
 * Yeepliva router.
 */
class Router
{
  /**
   * @var array $routes All registered routes.
   */
  private array $routes;

  /**
   * @var string $route_matched_name The name of the route match.
   */
  private string $route_matched_name;

  /**
   * @var string $param_pattern The Regex pattern for param.
   */
  private string $param_pattern = '`(/|_++|-++|){([^:}]++)(?::([^:}]++))?}(\?|)`';

  /**
   * @var array $param_types Regex param types.
   */
  private array $param_types = [
    'i' => '[0-9]++',
    'a' => '[0-9a-zA-Z]++',
    'f' => '[0-9a-zA-Z_\-]++',
    '__language' => '[a-z]{2}',
    '__location' => '[a-z]{2}-[a-z]{2}',
  ];

  /**
   * @var array $primary The primary param supported.
   */
  public array $primary = [
    'language' => null,
    'location' => null,
  ];

  /**
   * Initialization.
   * 
   * @param string $domain The domain of the application.
   * @param array param_types Regex param types.
   * @return void
   */
  public function __construct(private string $domain, array $param_types = [])
  {
    // Set data
    $this->updateParamTypes($param_types);
  }

  /**
   * Register the route to the router.
   * 
   * @param string $name The name of the route.
   * @param string $method The method of the route: "GET", "POST", ... Use "ALL" to match all request method.
   * @param string $path The path of the route: "/", "subdomain@/", "/article-{i:article_id}", ...
   * @param string $action The action of the route: "Controller@method".
   * @param false|string $primary The primary param of the route: "Language", "Location"
   * @return void
   */
  public function registerRoute(string $name, string $method, string $path, string $action, false|string $primary = false): void
  {
    // Set the primary param if route uses it
    if ($primary && array_key_exists($primary, $this->primary)) {
      $primary = [
        'method' => $primary,
        'value' => null,
      ];
    }

    // Register the route
    $this->routes[$name] = new Route($name, $method, $path, $action, $primary);
  }

  /**
   * Update the Regex param types.
   * 
   * @param array $param_types Regex param types.
   * @return void
   */
  private function updateParamTypes(array $param_types): void
  {
    // Set all new Regex param types
    foreach ($param_types as $param_type_key => $param_type_value) {
      $this->param_types[$param_type_key] = $param_type_value;
    }
  }

  /**
   * Try to find a route from the request.
   * 
   * @return Route
   */
  public function match(): Route
  {
    // Set request
    $request_method = isset($_SERVER['REQUEST_METHOD']) ? htmlspecialchars($_SERVER['REQUEST_METHOD']) : 'GET';
    $request_domain = isset($_SERVER['SERVER_NAME']) ? htmlspecialchars($_SERVER['SERVER_NAME']) : $this->domain;
    $request_path = isset($_SERVER['REQUEST_URI']) ? htmlspecialchars(explode('?', $_SERVER['REQUEST_URI'])[0]) : '/';
    $request_params = [];

    // Remove "index.php"
    $request_path = str_replace(['/index.php', '/index.php/'], '/', $request_path);

    // Remove last "/"
    $request_path = '/' . trim($request_path, '/');

    // Add domain to request
    $request_path = $request_domain . $request_path;

    // var_dump('REQUEST: ' . $request_method . ' => ' . $request_path);

    // Test all routes
    foreach ($this->routes as $route) {
      // Set data
      $route_method = $route->method;

      // Check if the method match
      if (str_contains($route_method, $request_method) || $route_method === 'ALL') {
        // Set data
        $route_path = $route->path;
        $route_primary = $route->primary;

        // Generate the route path
        $route_path = $this->compileRoutePath($route_path, $route_primary);

        if (str_ends_with($route_path, '*')) {
          // Match all
          $request_match = true;
        } elseif (!str_contains($route_path, '{')) {
          // Route without params, string comparison
          $request_match = $route_path === $request_path;
        } else {
          // Regex comparison
          $route_regex = $this->complieRouteRegex($route_path);
          $request_match = preg_match($route_regex, $request_path, $request_params);
        }

        // If a route if found, return the route
        if ($request_match) {
          // Set the primary param
          if ($route_primary && isset($request_params['primary'])) {
            $route_primary['value'] = $request_params['primary'] !== '' ? $request_params['primary'] : null;
            unset($request_params['primary']);
          }

          // Keep only named params
          if ($request_params !== []) {
            foreach ($request_params as $request_param_key => $request_param_value) {
              if (is_numeric($request_param_key)) {
                unset($request_params[$request_param_key]);
              }
            }
          }

          // Update the route
          $route->primary = $route_primary;
          $route->params = $request_params;

          // Set matched route name
          $this->route_matched_name = $route->name;

          // var_dump('ROUTE  : ' . $route_method . ' => ' . $route_path);

          return $route;
        }
      }
    }

    return throw new RouteNotFoundException();
  }

  /**
   * Create the full route path.
   * 
   * @param string $path The path of the route.
   * @param false|array $primary The primary param of the route.
   * @return string The full route path.
   */
  private function compileRoutePath(string $path, false|array $primary): string
  {
    // Separate the subdomain
    if (str_contains($path, '@')) {
      $a = explode('@', $path, 2);
      $subdomain = $a[0] . '.';
      $path = $a[1];
    } else {
      $subdomain = null;
    }

    // If the route uses a primary param, add it to the path
    if ($primary) {
      $path = '/{__' . $primary['method'] . ':primary}?' . $path;
      $path = '/' . trim($path, '/');
    }

    // Generate the full route path
    $path = $subdomain . $this->domain . $path;

    return $path;
  }

  /**
   * Create the Regex route path.
   * 
   * @param string $path The path of the root.
   * @return string The Regex route path.
   */
  private function complieRouteRegex(string $path): string
  {
    // Replace "." by "\."
    $path = str_replace('.', '\.', $path);

    // Find all params
    if (preg_match_all($this->param_pattern, $path, $params, PREG_SET_ORDER)) {
      // Replace each params to Regex param
      foreach ($params as $param) {
        // Set data
        [$param_block, $param_pre, $param_type, $param_key, $param_optional] = $param;

        // If param have a "/", "_", "-", add it to Regex
        $param_pre = $param_pre !== '' ? $param_pre : null;

        // Replace the param type to the associate Regex
        $param_type = isset($this->param_types[$param_type]) ? $this->param_types[$param_type] : $param_type;

        // If param's key is set, add it to Regex
        $param_key = $param_key !== '' ? '?P<' . $param_key . '>' : null;

        // If param is optional, add it to Regex
        $param_optional = $param_optional === '?' ? '?' : null;

        // Replace the param block by param Regex
        $param_regex = '(?:' . $param_pre . '(' . $param_key . $param_type . ')' . $param_optional . ')' . $param_optional;
        $path = str_replace($param_block, $param_regex, $path);
      }
    }

    return '`^' . $path . '$`u';
  }

  /**
   * Create a link of the domain.
   * 
   * @return string Link of the domain.
   */
  public function generateDomainLink(): string
  {
    return 'https://' . $this->domain;
  }

  /**
   * Create a link to a page of the application.
   * 
   * @param string $name The name of the route.
   * @param array $params The params of the route.
   * @return string The link of a page.
   */
  public function generateLink(string $name, array $params = [], false|array $alternate = false): string
  {
    // Check if the route exists
    if (isset($this->routes[$name])) {
      // Set data
      $route = $this->routes[$name];
      $path = $route->path;
      $primary = $route->primary;

      // Check for primary params and set data
      if ($primary) {
        if ($alternate) {
          $params['primary'] = $alternate[0];
        } else {
          $params['primary'] = $this->primary[$primary['method']];
        }
      }

      // Generate the route path
      $path = $this->compileRoutePath($path, $primary);

      // If it's match all route, remove the "*"
      if (str_ends_with($path, '*')) {
        $path = trim($path, '*');
      }

      // Replace params by value only if the route uses params
      if (str_contains($path, '{')) {
        $path = $this->compileRouteLink($path, $params);
      }

      // Check if no errors
      if ($path) {
        // Remove last "/"
        $path = trim($path, '/');

        return 'https://' . $path;
      }
    }

    return 'https://' . $this->domain;
  }

  /**
   * Compile route link.
   * 
   * @param string $path The path of the route.
   * @param array $params_data The params of the route.
   * @return false|string The The link of the route with params value.
   */
  private function compileRouteLink(string $path, array $params_data): false|string
  {
    // Detect all params
    if (preg_match_all($this->param_pattern, $path, $params, PREG_SET_ORDER)) {
      // Set all params
      foreach ($params as $param) {
        // Set data
        [$param_block, $param_pre, $param_type, $param_key, $param_optional] = $param;

        // If param have a "/", "_", "-", add it to link
        $param_pre = $param_pre !== '' ? $param_pre : null;

        // Try to add the value of the param to link
        if (isset($params_data[$param_key])) {
          // Replace the param block by param value
          $param_link = $param_pre . $params_data[$param_key];
          $path = str_replace($param_block, $param_link, $path);
        } elseif ($param_key === 'primary' && $params_data['primary'] === null) {
          // Default param primary, remove it from link
          $path = str_replace($param_block, '', $path);
        } else {
          return false;
        }
      }
    }

    return $path;
  }

  /**
   * Create a link of the current page for alternate language.
   * 
   * @param null|string $alternate The alternate value.
   * @return string The link of a page.
   */
  public function generateAltermateLink(?string $alternate = null)
  {
    // Set data
    $route = $this->routes[$this->route_matched_name];
    $params = $route->params;
    $alternate = [$alternate];

    return $this->generateLink($this->route_matched_name, $params, $alternate);
  }
}