<?php

namespace Yeepliva\Exceptions;

use Yeepliva\Controllers\Controller;
use Yeepliva\Controllers\View;
use Yeepliva\Core\Gateway;

/**
 * Yeepliva exception => Route not found.
 */
class RouteNotFoundException extends YeeplivaException
{
  /**
   * Execute the error.
   * 
   * @param Gateway $gateway Yeepliva gateway.
   * @return View The route display.
   */
  public function execute(Gateway $gateway): View
  {
    $controller = new Controller($gateway);
    return $controller->generateView('error/404');
  }
}