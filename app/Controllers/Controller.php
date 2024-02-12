<?php

namespace Yeepliva\Controllers;

use Yeepliva\Core\Gateway;

/**
 * Yeepliva controller.
 */
class Controller
{

  /**
   * @var string $title The view title.
   */
  protected string $title = '';

  /**
   * @var string $author The author.
   */
  protected string $author = '';

  /**
   * @var string $description The description.
   */
  protected string $description = '';

  /**
   * Initialization.
   * 
   * @param Gateway $gateway Yeepliva gateway.
   * @return void
   */
  public function __construct(protected Gateway $gateway)
  {
  }

  /**
   * Generate a view.
   * 
   * @param string $path The path of the view.
   * @param null|array $params The params of the view.
   * @return View The gene
   */
  protected function generateView(string $path, ?array $params = null): View
  {
    // Set view path
    $views_path = dirname(__DIR__, 2) . '/views/';

    // Pull from gateway
    [$configurator, $router, $t] = $this->gateway->pull(['configurator', 'router', 'translator']);

    // Set settings view
    $title       = $this->title       !== '' ? $t->e($this->title) . ' - ' . $configurator->view_title : $configurator->view_title;
    $author      = $this->author      !== '' ? $this->author                                           : $configurator->view_author;
    $description = $this->description !== '' ? $t->e($this->description)                               : $t->e($configurator->view_description);

    // Set params if any
    if ($params) {
      extract($params);
    }

    // Load the page content
    ob_start();
    require_once $views_path . $path . '.php';
    $content = ob_get_clean();

    // Load the page model
    ob_start();
    require_once $views_path . 'resources/page.php';
    $content_final = ob_get_clean();

    return new View($content_final);
  }
}