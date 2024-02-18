<?php

namespace Yeepliva\Core;

use Yeepliva\Exceptions\RouteNotFoundException;
use Yeepliva\Language\LLManager;
use Yeepliva\Language\Translator;
use Yeepliva\Router\Router;

/**
 * Yeepliva application.
 */
class App
{
  /**
   * @var Configurator $configurator Yeepliva configurator.
   */
  private Configurator $configurator;

  /**
   * @var Gateway $gateway Yeepliva gateway.
   */
  private Gateway $gateway;

  /**
   * Initialization.
   * 
   * @return void
   */
  public function __construct()
  {
    // Set data
    $this->configurator = new Configurator();
    $this->gateway = new Gateway();
    $this->gateway->push(['configurator' => $this->configurator]);
  }

  /**
   * Run the application.
   * 
   * @return void
   */
  public function run()
  {
    // Session
    session_start();

    // Create the router
    $router = new Router($this->configurator->domain);

    // Register routes
    $router->registerRoute('home',    'GET',      '/',                    'MainController@home',    'location');
    $router->registerRoute('about',   'GET',      '/about',               'MainController@about',   'language');
    $router->registerRoute('contact', 'GET|POST', '/contact',             'MainController@contact', 'language');
    $router->registerRoute('blog',    'GET',      '/blog',                'MainController@blog',    'language');
    $router->registerRoute('article', 'GET',      '/blog/{i:article_id}', 'MainController@article', 'language');

    // Cookie manager
    $cookie_manager = new CookieManager($this->configurator->domain);

    // Language and location manager
    $ll_manager = new LLManager($this->configurator->ll_default, $this->configurator->ll_supported, $cookie_manager);

    // Try to find a route
    try {
      $route = $router->findRoute();

      // If route uses primary, change language and location from primary param
      if (isset($route->primary['value'])) {
        $ll_manager->detectByRoute($route->primary);
      }
    } catch (RouteNotFoundException $e) {
      $route = $e;
    }

    // Set router primary param
    $router->primary['language'] = $ll_manager->primaryForRouter('language');
    $router->primary['location'] = $ll_manager->primaryForRouter('location');

    // Translator
    $translator = new Translator($ll_manager->langForTranslator());

    // Push to gateway
    $this->gateway->push(compact('cookie_manager', 'll_manager', 'router', 'translator'));

    // Execute the route
    $view = $route->execute($this->gateway);

    // Display the generated view
    $view->display();
  }
}