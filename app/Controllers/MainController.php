<?php

namespace Yeepliva\Controllers;

/**
 * Yeepliva controller => Main.
 */
class MainController extends Controller
{
  public function home(): View
  {
    // Set data
    $this->title = 'Home';

    return $this->generateView('main/home');
  }

  public function about(): View
  {
    // Set data
    $this->title = 'About';

    return $this->generateView('main/about');
  }

  public function contact(): View
  {
    // Set data
    $this->title = 'Contact';

    return $this->generateView('main/contact');
  }

  public function blog(): View
  {
    // Set data
    $this->title = 'Blog';

    return $this->generateView('main/blog');
  }

  public function article(array $params): View
  {
    // Set data
    $this->title = 'Article';
    $article_id = intval($params['article_id']);

    return $this->generateView('main/article', compact('article_id'));
  }
}