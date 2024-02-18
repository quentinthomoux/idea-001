<?php

namespace Yeepliva\Language;

/**
 * Yeepliva translator.
 */
class Translator
{
  /**
   * @var false|array $translation The translation list.
   */
  private false|array $translation = false;

  /**
   * Initialization.
   * 
   * @param null|string $language The language to use if not default.
   * @return void
   */
  public function __construct(?string $language)
  {
    // Set data
    $this->loadTranslation($language);
  }

  /**
   * Load the translation data.
   * 
   * @param null|string $language The language to use if not default.
   * @return void
   */
  private function loadTranslation(?string $language): void
  {
    // Set data
    if ($language) {
      // Set data
      $file_path = dirname(__DIR__, 2) . '/translation/' . $language . '.php';

      // Check if file exits
      if (file_exists($file_path)) {
        $this->translation = require_once $file_path;
      }
    }
  }

  /**
   * Translate the text.
   * 
   * @param string $text Text to translate.
   * @return string Translated text.
   */
  public function e(string $text): string
  {
    return $this->translation && isset($this->translation[$text]) ? $this->translation[$text] : $text;
  }
}