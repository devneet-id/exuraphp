<?php


class Exura {

  public static function mount($request, $route, $execute) {
    $request = strtoupper($request);
    ExuraEnvironment::initialize();

    if (ExuraEnvironment::matchRequest($request)) {
      if (ExuraEnvironment::matchRoute($route)) {
        
        ExuraEnvironment::setMethod();
        ExuraEnvironment::setError(); // langsung aktifin handler

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

        self::header([
          'Content-Type: application/json',
        ]);

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

  public static function method( $key, $fail = false ) {
    if (count(ExuraEnvironment::$method) > 0) {
      $getMethod = ExuraEnvironment::$method;
    }else {
      $getMethod = ExuraEnvironment::getMethod();
    }
    if (isset($getMethod[$key])) {
      return $getMethod[$key];
    }else {
      ExuraEnvironment::$method[$key] = $fail;
      return $fail;
    }
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
    echo json_encode(ExuraEnvironment::$response, JSON_PRETTY_PRINT);
    exit; die;
  }


}