<?php
/* 
 * E X U R A
 * 
 * created by | Anwar Achilles
 * 
 * */
class ExuraEnvironment {


  public static $configure = [
    'version'=> '1.0',
    'url'=> '',
  ];

  public static $method = [];

  public static $response = [];

  
  
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

    // normalisasi key
    $normalized = [];
    foreach ($method as $key => $val) {
      $cleanKey = ltrim($key, '/');
      $normalized[$cleanKey] = $val;
    }

    return $normalized;
  }



  public static function setError() {
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
      ExuraEnvironment::setResponse('error', [
        'type'    => $errno,
        'message' => $errstr,
        'file'    => $errfile,
        'line'    => $errline,
      ]);
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






  public static function matchRoute($pattern) {
    $currentRoute = self::getRoute();

    $currentRoute = preg_replace('/^[^\/]+\.php\/?/', '', $currentRoute);

    $pattern = str_replace('*', '.*', preg_quote($pattern, '/'));

    return (bool) preg_match('/(^|\/)' . $pattern . '($|\/)/i', $currentRoute);
  }


  public static function matchRequest($methods) {
    $current = strtoupper(self::getRequest() ?? '');
    
    if (is_string($methods)) {
      $methods = [$methods];
    }

    $methods = array_map('strtoupper', $methods);

    return in_array($current, $methods, true);
  }




  public static function methodShouldBe() {

  }


}