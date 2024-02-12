<?php

namespace Yeepliva\Language;

use Yeepliva\Core\CookieManager;

/**
 * Yeepliva translator.
 */
class Translator
{
  /**
   * @var string $user_lang The language used by the user.
   */
  public string $user_lang;

  /**
   * @var array $lang_from_locale The list of all locales and their language.
   */
  private array $lang_from_locale;

  /**
   * @var array $translation The translation list.
   */
  private array $translation = [];

  /**
   * Initialization.
   * 
   * @param string $lang_default The default language of the application.
   * @param array $lang_supported All supported language of the application.
   * @param CookieManager $cookie_manager Yeepliva cookie manager.
   * @return void
   */
  public function __construct(private string $lang_default, private array $lang_supported, private CookieManager $cookie_manager)
  {
    // Set data
    $this->lang_from_locale = require_once __DIR__ . '/lang_from_locale.php';
    $this->getLanguage();
  }

  /**
   * Get the user language.
   * 
   * @return void
   */
  private function getLanguage(): void
  {
    // Set data
    $continue = true;

    // By session
    if (isset($_SESSION['user_lang'])) {
      // Set data
      $lang = $_SESSION['user_lang'];

      // Test the language
      $continue = !in_array($lang, $this->lang_supported);
    }

    // By cookie
    if ($continue && $lang = $this->cookie_manager->getCookie('user_lang')) {
      // Test the language
      $continue = !in_array($lang, $this->lang_supported);
    }

    // By device setting
    if ($continue && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      // Set data
      $lang = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);

      // Check if the locale is knwoned
      if (isset($this->lang_from_locale[$lang])) {
        $lang = $this->lang_from_locale[$lang];

        // Test the language
        $continue = !in_array($lang, $this->lang_supported);
      }
    }

    // Default setting
    if ($continue) {
      $lang = $this->lang_default;
    }

    // Set data
    $this->user_lang = $lang;
  }

  /**
   * Get the user language by route.
   * 
   * @param string $lang The language detected by the route.
   * @return void
   */
  public function getLanguageByRoute(string $lang): void
  {
    // Set data
    $lang = locale_accept_from_http($lang);

    // Check if the locale is knwoned
    if (isset($this->lang_from_locale[$lang])) {
      $lang = $this->lang_from_locale[$lang];

      // Test the language
      if (in_array($lang, $this->lang_supported)) {
        $this->user_lang = $lang;
      }
    }
  }

  /**
   * Translate the text.
   * 
   * @param string $text The text to translate.
   * @return string Translated text.
   */
  public function e(string $text): string
  {
    // var_dump($text);
    if ($this->user_lang !== $this->lang_default) {
      // Load the translation file
      if ($this->translation === []) {
        $file_path = dirname(__DIR__, 2) . '/translation/' . $this->user_lang . '.php';
        if (file_exists($file_path)) {
          $this->translation = require_once $file_path;
        }
      }

      $text = isset($this->translation[$text]) ? $this->translation[$text] : $text;
    }

    return $text;
  }

  /**
   * Send the language for router.
   * 
   * @return null|string The language to use.
   */
  public function langForRouter(): null|string
  {
    // Check if it's the default value
    if ($this->user_lang !== $this->lang_default) {
      return $this->user_lang;
    }

    return null;
  }
}