<?php

namespace Yeepliva\Core;

/**
 * Yeepliva configurator.
 * 
 * Load all config for the application.
 */
class Configurator
{
  /**
   * @var string $domain The domain of the application.
   */
  public string $domain;

  /**
   * @var string $ll_default The default language and location.
   */
  public string $ll_default;

  /**
   * @var array $ll_supported All language and location.
   */
  public array $ll_supported;

  /**
   * @var string $view_title The default title of the view.
   */
  public string $view_title;

  /**
   * @var string $view_author The default author of the application & view.
   */
  public string $view_author;

  /**
   * @var string $view_description The default description of the view.
   */
  public string $view_description;

  /**
   * Initialization.
   * 
   * @return void
   */
  public function __construct()
  {
    // Set data
    $this->updateParams();
  }

  /**
   * Update config settings.
   * 
   * @return void
   */
  private function updateParams(): void
  {
    // Get the config file
    $params = require_once dirname(__DIR__, 2) . '/config.php';

    // Set each params
    foreach ($params as $param_key => $param_value) {
      if (property_exists(__CLASS__, $param_key)) {
        $this->$param_key = $param_value;
      }
    }
  }
}