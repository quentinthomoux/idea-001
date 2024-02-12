<?php

namespace Yeepliva\Core;

/**
 * Yeepliva configurator.
 */
class Configurator
{
  /**
   * @var string $domain The domain of the application.
   */
  public string $domain;

  /**
   * @var string $lang_default The default language of the application.
   */
  public string $lang_default;

  /**
   * @var array $lang_supported All supported language of the application.
   */
  public array $lang_supported;

  /**
   * @var string $view_title The view title.
   */
  public string $view_title;

  /**
   * @var string $view_author The author.
   */
  public string $view_author;

  /**
   * @var string $view_description The description.
   */
  public string $view_description;

  /**
   * Initialization.
   * 
   * @return void
   */
  public function __construct() {
    // Set data
    $this->updateParams();
  }

  /**
   * Load or update the params of the application.
   * 
   * @return void
   */
  private function updateParams(): void
  {
    // Get the config file
    $params = require_once dirname(__DIR__, 2) . '/config.php';

    // Set each params only if they exist
    foreach ($params as $param_key => $param_value) {
      if (property_exists(__CLASS__, $param_key)) {
        $this->$param_key = $param_value;
      }
    }
  }
}