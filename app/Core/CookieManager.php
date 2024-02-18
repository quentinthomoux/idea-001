<?php

namespace Yeepliva\Core;

use PHPUnit\Framework\Constraint\IsTrue;

/**
 * Yeepliva cookie manager.
 */
class CookieManager
{
  /**
   * @var array $cookie_authorization Authorization level of cookie usage.
   */
  private array $cookie_authorization = [
    'required' => false,
    'optional' => false,
    'analytic' => false,
  ];

  /**
   * Initialization.
   * 
   * @param string $domain The domain of the application.
   * @param int $duration_default The default duration of cookie: 30 days.
   * @return void
   */
  public function __construct(private string $domain, private int $duration_default = 30)
  {
    // Set data
    $this->getCookieAuthorization();
  }

  /**
   * Get the authorization of cookie usage.
   * 
   * @return void
   */
  private function getCookieAuthorization(): void
  {
    if ($cookie_authorization = $this->getCookie('cookie_authorization')) {
      // Set data
      switch ($cookie_authorization) {
        case 'required':
          $this->cookie_authorization['required'] = true;
          break;
        case 'optional':
          $this->cookie_authorization['required'] = true;
          $this->cookie_authorization['optional'] = true;
          break;
        case 'analytic':
          $this->cookie_authorization['required'] = true;
          $this->cookie_authorization['optional'] = true;
          $this->cookie_authorization['analytic'] = true;
          break;
        default:
          $this->deleteCookie('cookie_authorization');
          break;
      }
    }
  }

  /**
   * Create or update a cookie.
   * 
   * @param string $name The aname of the cookie.
   * @param true|string $group The group of authorization: "required", "optional", "analytic".
   * @param string|array|int|float $value The value of the cookie.
   * @param array $options The options of the cookie.
   * @return bool If the cookie has been set.
   */
  public function setCookie(string $name, true|string $group, string|array|int|float $value, array $options = []): bool
  {
    // Check for authorization
    if ($group === true || $this->cookie_authorization[$group] === true) {
      // If it's not an array, set the cookie, otherwise, call this function
      if (!is_array($value)) {
        // Set data
        $options['expires']  = isset($options['expires'])  && is_numeric($options['expires']) && $options['expires'] >= 0    ? $options['expires']*3600*24 + time()     : $this->duration_default*3600*24 + time();
        $options['path']     = isset($options['path'])     && is_string($options['path'])     && $options['path'] !== ''     ? $options['path']                         : '/';
        $options['domain']   = isset($options['domain'])   && is_string($options['domain'])   && $options['domain'] !== ''   ? $options['domain'] . '.' . $this->domain : $this->domain;
        $options['secure']   = isset($options['secure'])   && is_bool($options['secure'])                                    ? $options['secure']                       : true;
        $options['httponly'] = isset($options['httponly']) && is_bool($options['httponly'])                                  ? $options['httponly']                     : true;
        $options['samesite'] = isset($options['samesite']) && is_string($options['samesite']) && $options['samesite'] !== '' ? $options['samesite']                     : 'strict';

        setcookie($name, $value, $options);
      } else {
        foreach ($value as $data_key => $data_value) {
          $this->setCookie($name . '[' . $data_key . ']', true, $data_value, $options);
        }
      }

      return true;
    } else {
      return false;
    }
  }

  /**
   * Get the value of a cookie.
   * 
   * @param string $name The aname of the cookie.
   * @param bool $delete_if_empty If true, the cookie will be deleted if the value is empty.
   * @return null|string|array|int|float The value of the cookie.
   */
  public function getCookie(string $name, bool $delete_if_empty = true): null|string|array|int|float
  {
    // Set data
    $value = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;

    // Delete the cookie if the value is empty
    if ($delete_if_empty && $value === '') {
      $this->deleteCookie($name);
      $value = null;
    }

    // Change the type of value if a value is found
    if ($value) {
      $value = $this->transformValue($value);
    }

    return $value;
  }

  /**
   * Change the type of value if it's a a number.
   * 
   * @param string|array $value The initial value.
   * @return string|array|int|float The new value.
   */
  private function transformValue(string|array $value): string|array|int|float
  {
    // Convert to an array
    if (!is_array($value)) {
      $value = [$value];
      $not_array = true;
    } else {
      $not_array = false;
    }

    foreach ($value as $data_key => $data_value) {
      if (!is_array($data_value)) {
        // Clean value
        $data_value = htmlspecialchars($data_value);

        // Transform to number type
        if (is_numeric($data_value)) {
          if (str_contains($data_value, '.')) {
            $data_value = floatval($data_value);
          } else {
            $data_value = intval($data_value);
          }
        }

        // Set data
        $value_clean[$data_key] = $data_value;
      } else {
        $value_clean[$data_key] = $this->transformValue($data_value);
      }
    }

    // Extract from array
    if ($not_array) {
      $value_clean = $value_clean[0];
    }

    return $value_clean;
  }

  /**
   * Delete a cookie.
   * 
   * @param string $name The aname of the cookie.
   * @return void
   */
  public function deleteCookie(string $name): void
  {
    // Set data
    $options['expires']  = 1;
    $options['path']     = '/';
    $options['domain']   = $this->domain;
    $options['secure']   = false;
    $options['httponly'] = false;
    $options['samesite'] = 'strict';

    if (isset($_COOKIE[$name]) && is_array($_COOKIE[$name])) {
      foreach ($_COOKIE[$name] as $data_key => $data_value) {
        $this->deleteCookie($name . '[' . $data_key . ']');
      }
    } else {
      setcookie($name, '', $options);
    }
  }
}