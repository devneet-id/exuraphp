<?php


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