<?php

namespace Yeepliva\Language;

use Yeepliva\Core\CookieManager;

/**
 * Yeepliva language and location manager.
 */
class LLManager
{
  /**
   * @var string $user_language The user language.
   */
  public string $user_language;

  /**
   * @var string $user_location The user location.
   */
  public string $user_location;

  /**
   * @var array $locales The locales list.
   */
  private array $locales;

  /**
   * @var array $locales_code The code of all locales.
   */
  private array $locales_code;

  /**
   * Initialization.
   * 
   * @param string $default The default language and location.
   * @param array $supported All language and location.
   * @param CookieManager $cookie_manager Yeepliva cookie manager.
   * @return void
   */
  public function __construct(private string $default, private array $supported, private CookieManager $cookie_manager)
  {
    // Set data
    $this->loadLocales();
    $this->detectLanguageLocation();
  }

  /**
   * Load the locales.
   * 
   * @return void
   */
  private function loadLocales(): void
  {
    // Load the locale for each supported locales
    foreach ($this->supported as $locale_name) {
      // Set data
      $locale_class = 'Yeepliva\Language\Locales\\' . $locale_name . '__Locale';

      // Check if the locale exists
      if (class_exists($locale_class)) {
        // Set data
        $locale = new $locale_class();
        $this->locales[$locale_name] = $locale;
        $this->locales_code[$locale->code] = $locale_name;
      }
    }
  }

  /**
   * Detect the language and location to use.
   * 
   * @return void
   */
  private function detectLanguageLocation(): void
  {
    $continue = true;

    // By session
    if ($continue && isset($_SESSION['user_ll'])) {
      // Set data
      $locale = htmlspecialchars($_SESSION['user_ll']);

      // Check the locale found
      if (array_key_exists($locale, $this->locales)) {
        $locale = $this->locales[$locale];
        $continue = false;
      }
    }

    // By cookie
    if ($continue && $locale = $this->cookie_manager->getCookie('user_ll')) {
      // Check the locale found
      if (array_key_exists($locale, $this->locales)) {
        $locale = $this->locales[$locale];
        $continue = false;
      }
    }

    // By device settings
    if ($continue && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      // Set data
      $locale = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);

      // Check the locale found
      if (array_key_exists($locale, $this->locales)) {
        $locale = $this->locales[$locale];
        $continue = false;
      }
    }

    // By default
    if ($continue) {
      $locale = $this->locales[$this->default];
    }

    // Set data
    $this->user_language = $locale->language;
    $this->user_location = $locale->location;
  }

  /**
   * Change language and location from primary param.
   * 
   * @param array $primary The primary param.
   * @return void
   */
  public function detectByRoute(array $primary): void
  {
    // Set data
    $locale = null;

    // Check if the primary is a language or location method
    if ($primary['method'] !== 'location') {
      // Check the language found
      if (isset($this->locales_code[$primary['value']])) {
        // Set data
        $locale = $this->locales_code[$primary['value']];
      }
    } else {
      // Set data
      $locale = locale_accept_from_http($primary['value']);
    }

    // Check the locale found
    if ($locale && array_key_exists($locale, $this->locales)) {
      $locale = $this->locales[$locale];
      $this->user_language = $locale->language;
      $this->user_location = $locale->location;
    }
  }

  /**
   * Set primary param for router.
   * 
   * @param string $method The method of primary param: "language", "location".
   * @return null|string The language or location to use.
   */
  public function primaryForRouter(string $method): null|string
  {
    // Set data
    $user_method = 'user_' . $method;

    // Compare with default
    if ($this->$user_method !== $this->locales[$this->default]->$method) {
      return $this->$user_method;
    }

    return null;
  }

  /**
   * Set language for translator.
   * 
   * @return null|string The language to use.
   */
  public function langForTranslator(): null|string
  {
    // Compare with default
    if ($this->user_language !== $this->locales[$this->default]->language) {
      return $this->user_language;
    }

    return null;
  }
}