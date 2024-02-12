<?php

namespace Yeepliva\Exceptions;

/**
 * Yeepliva exception => route not found.
 */
class RouteNotFoundException extends YeeplivaException
{
  protected $message = '404 - Route Not Found!';
}