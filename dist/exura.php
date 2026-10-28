<?php
/* RUNE 1.11 | Created By @anwarachilles */
/* 
 * E X U R A - 1.1.0
 * 
 * created by | Anwar Achilles
 * 
 * */
class ExuraEnvironment {


  public static $configure = [
    'version'=> '1.1.0',
    'url'=> '',
  ];

  public static $method = [];

  public static $response = [];

  public static $notice = [];

  
  
  public static function initialize() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

    self::$configure['url'] = $scheme . "://" . $host . ($path ? $path . '/' : '/');

    self::setResponse('E X U R A - ' . self::$configure['version'], [
      'url'=> self::$configure['url'],
      'state'=> http_response_code(),
      'route'=> self::getRoute(),
      'method'=> self::getMethod(),
      'request'=> self::getRequest(),
      'referer'=> $_SERVER['HTTP_REFERER'] ?? '',
    ]);
  }


  public static function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
  }


  public static function getRequest() {
    return $_SERVER['REQUEST_METHOD'] ?? null;
  }

  public static function getRoute() {
    $uriQuery = $_SERVER['QUERY_STRING'] ?? '';
    $uriPath  = $_SERVER['REQUEST_URI'] ?? '';

    if (preg_match('/^(\/{1,2}[^\?&]*)/', $uriQuery, $m)) {
      $route = $m[1];
    } else {
      $parsed = parse_url($uriPath);
      $route = $parsed['path'] ?? '';
    }

    return preg_replace('/^\/+/', '', $route);
  }

  public static function getMethod() {
    $method   = [];
    $uriQuery = $_SERVER['QUERY_STRING'] ?? '';

    $rawInput = file_get_contents('php://input');
    if (self::isJson($rawInput)) {
      $method = json_decode($rawInput, true) ?? [];
    } else {
      parse_str($rawInput, $method);
    }

    if (!empty($_POST)) {
      $method = $_POST;
    }

    if (!empty($_GET)) {
      $method = array_merge($method, $_GET);
    }

    if (!empty($uriQuery) && preg_match('/(^|&)\w+=/', $uriQuery)) {
      parse_str($uriQuery, $extra);
      $method = array_merge($method, $extra);
    }

    // drop the route key from "?/route" and normalize keys
    $normalized = [];
    foreach ($method as $key => $val) {
      if (str_starts_with((string) $key, '/')) continue;
      $normalized[$key] = $val;
    }

    return $normalized;
  }



  public static function setError() {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
      // respect error_reporting() and the @ operator
      if (!(error_reporting() & $errno)) {
        return true;
      }

      $error = [
        'type'    => $errno,
        'message' => $errstr,
        'file'    => $errfile,
        'line'    => $errline,
      ];

      // deprecations and notices are noted in the trace; the response keeps going
      if (in_array($errno, [E_DEPRECATED, E_USER_DEPRECATED, E_NOTICE, E_USER_NOTICE], true)) {
        ExuraEnvironment::$notice[] = $error;
        ExuraEnvironment::setTrace('notice', ExuraEnvironment::$notice);
        return true;
      }

      // warnings and errors answer 500
      ExuraEnvironment::setResponse('error', $error);
      return true;
    });

    set_exception_handler(function($e) {
      ExuraEnvironment::setResponse('error', [
        'type'    => get_class($e),
        'code'    => $e->getCode(),
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
      ]);
    });
  }



  
  

  public static function setMethod() {
    self::$method = self::getMethod();
  }

  public static function setResponse($key, $data = null) {
    if (is_string($key)) {
      if (isset(self::$response[$key]) && is_array(self::$response[$key]) && is_array($data)) {
        self::$response[$key] = array_merge(self::$response[$key], $data);
      } else {
        self::$response[$key] = $data;
      }
    } elseif (is_array($key)) {
      self::$response = array_merge(self::$response, $key);
    }
  }

  public static function setTrace($key, $value) {
    self::setResponse('E X U R A - ' . self::$configure['version'], [$key=>$value]);
  }






  // the whole route must match from its start; '*' matches the rest (user/* -> user/42)
  public static function matchRoute($pattern) {
    $currentRoute = trim(self::getRoute(), '/');

    // drop the script path (exura-dev/index.php) or its folder (exura-dev) in front of the route
    $script = trim($_SERVER['SCRIPT_NAME'] ?? '', '/');
    foreach ([$script, trim(dirname($script), '/.')] as $prefix) {
      if ($prefix !== '' && str_starts_with($currentRoute . '/', $prefix . '/')) {
        $currentRoute = trim(substr($currentRoute, strlen($prefix)), '/');
        break;
      }
    }

    $parts = array_map(fn($part) => preg_quote($part, '/'), explode('*', trim($pattern, '/')));

    return (bool) preg_match('/^' . implode('.*', $parts) . '$/i', $currentRoute);
  }


  public static function matchRequest($methods) {
    $current = strtoupper(self::getRequest() ?? '');
    
    if (is_string($methods)) {
      $methods = [$methods];
    }

    $methods = array_map('strtoupper', $methods);

    return in_array($current, $methods, true);
  }




}
class Exura {

  // $request: 'GET' or a list ['PUT', 'PATCH']; $route: 'notes', 'user/*'
  public static function module($request, $route, $execute) {
    $request = is_array($request) ? array_map('strtoupper', $request) : strtoupper($request);
    ExuraEnvironment::initialize();

    if (ExuraEnvironment::matchRequest($request)) {
      if (ExuraEnvironment::matchRoute($route)) {
        
        ExuraEnvironment::setMethod();
        ExuraEnvironment::setError(); // errors of the route become JSON

        try {
          self::state(200);
          ExuraEnvironment::setResponse($execute());
        } catch (\Throwable $e) {
          ExuraEnvironment::setResponse('error', [
            'type'    => get_class($e),
            'code'    => $e->getCode(),
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
          ]);
        }

        if (isset(ExuraEnvironment::$response['error'])) {
          self::state(500);
        }

        self::return([]);
      }
    }
  }


  public static function header( $list ) {
    foreach ($list as $value) {
      header($value);
    }
  }

  // method('key', $default) reads one value; method() returns every value of the request
  public static function method( $key = null, $fail = false ) {
    $getMethod = count(ExuraEnvironment::$method) > 0 ? ExuraEnvironment::$method : ExuraEnvironment::getMethod();
    if ($key === null) {
      return $getMethod;
    }
    return $getMethod[$key] ?? $fail;
  }

  public static function state( $code ) {
    http_response_code($code);
    ExuraEnvironment::setResponse('state', $code);
    ExuraEnvironment::setTrace('state', $code);
  }

  public static function data( $key, $data = null ) {
    ExuraEnvironment::setResponse('data', [$key => $data]);
  }

  public static function return( $data, $code = null ) {
    ExuraEnvironment::setResponse($data);
    ExuraEnvironment::setTrace('method', ExuraEnvironment::$method);
    if (!empty($code)) {
      self::state($code);
    }
    if (!headers_sent()) {
      header('Content-Type: application/json');
    }
    echo json_encode(ExuraEnvironment::$response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
  }

  // call after all modules: answers 404 JSON when no route matched
  public static function fallback( $data = [], $code = 404 ) {
    ExuraEnvironment::initialize();
    ExuraEnvironment::setResponse('error', [
      'message' => 'Route not found',
      'route'   => ExuraEnvironment::getRoute(),
    ]);
    self::return($data, $code);
  }


}