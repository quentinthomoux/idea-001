<?php

namespace Yeepliva\Core;

use Yeepliva\Controllers\View;
use Yeepliva\Exceptions\RouteNotFoundException;
use Yeepliva\Language\Translator;
use Yeepliva\Router\Router;

/**
 * Yeepliva application.
 * 
 * The main part of the application.
 */
class App
{
  /**
   * @var Configurator $configurator All params of the application.
   */
  private Configurator $configurator;

  /**
   * @var Gateway $gateway The gateway of the application.
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
  }

  /**
   * Run the application.
   * 
   * @return void
   */
  public function run(): void
  {
    // Session
    session_start();

    // Set the router
    $router = new Router($this->configurator->domain);

    // Register all routes of the application
    $router->registerRoute('home',    'GET',      '/',                    'MainController@home',    'language');
    $router->registerRoute('about',   'GET',      '/about',               'MainController@about',   'language');
    $router->registerRoute('contact', 'GET|POST', '/contact',             'MainController@contact', 'language');
    $router->registerRoute('blog',    'GET',      '/blog',                'MainController@blog',    'language');
    $router->registerRoute('article', 'GET',      '/blog/{i:article_id}', 'MainController@article', 'language');
    // $router->registerRoute('login',   'GET|POST', 'account@/',            'AccountController@login');

    // Set the cookie manager
    $cookie_manager = new CookieManager($this->configurator->domain);

    // Translator
    $translator = new Translator($this->configurator->lang_default, $this->configurator->lang_supported, $cookie_manager);

    // Push to gateway
    $this->gateway->push([
      'configurator' => $this->configurator,
      'cookie_manager' => $cookie_manager,
    ]);

    try {
      // Set route
      $route = $router->match();

      // If a language as primary param is detected
      if (isset($route->primary['value'])) {
        $translator->getLanguageByRoute($route->primary['value']);
      }

      // Set router primary language
      $router->primary['language'] = $translator->langForRouter();

      // Push to gateway
      $this->gateway->push(compact('router', 'translator'));

      // Execute the route
      $view = $route->execute($this->gateway);
    } catch (RouteNotFoundException $e) {
      // Set router primary language
      $router->primary['language'] = $translator->langForRouter();

      // Push to gateway
      $this->gateway->push(compact('router', 'translator'));

      $view = new View($e->getMessage());
    }

    // Display the view
    if ($view) {
      $view->display();
    }
  }
}