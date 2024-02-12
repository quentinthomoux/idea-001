<?php

namespace Yeepliva\Controllers;

/**
 * Yeepliva view.
 * 
 * The view to be displayed.
 */
class View
{
  /**
   * Initialization.
   * 
   * @param string $content The generated view.
   * @return void
   */
  public function __construct(private string $content)
  {
  }

  /**
   * Display the view.
   * 
   * @return void
   */
  public function display(): void
  {
    echo $this->content;
  }
}