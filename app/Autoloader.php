<?php

namespace Yeepliva;

/**
 * Yeepliva autoloader.
 * 
 * Automatic loading of required classes.
 */
class Autoloader
{
  /**
   * @var array $vendors All vendor namespaces ans their paths.
   */
  private static array $vendors = [
    'Yeepliva' => 'app',
  ];

  /**
   * Register the autoloader.
   * 
   * @return void
   */
  public static function register(): void
  {
    spl_autoload_register([__CLASS__, 'loadClass']);
  }

  /**
   * Load the required class.
   * 
   * @param string $class The fully qualified name of the required class.
   * @return void
   */
  private static function loadClass(string $class): void
  {
    // Get the vendor namespace
    $vendor = explode('\\', $class)[0];

    // Check if the vendor namespace is known, and if so, replace it with its path
    if (isset(self::$vendors[$vendor])) {
      $class_path = dirname(__DIR__) . '/' . preg_replace('`^' . $vendor . '`', self::$vendors[$vendor], $class) . '.php';
      $class_path = str_replace('\\', '/', $class_path);

      // Import the class if the file exists
      if (file_exists($class_path)) {
        require_once $class_path;
      }
    }
  }
}