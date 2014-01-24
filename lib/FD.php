<?php

require_once(dirname(__FILE__) . '/AnsiDumper.php');


/**
 * File Dumper. Can be used to write colorized dumps to a file using the FD_STEAM file descriptor.
 */
class FD {

  /**
   * @var resource $_stream
   */
  private static $_stream = null;

  /**
   * Singleton of AnsiDumper.
   * @return AnsiDumper
   */
  public static function getInstance() {
    if (self::$_stream === null) {
      if (defined('FD_FILE')) {
        self::$_stream = fopen(FD_FILE, 'a+');
      } elseif (isset($_SERVER['FD_FILE'])) {
        self::$_stream = fopen($_SERVER['FD_FILE'], 'a+');
      }
    }

    $instance = new AnsiDumper();
    return $instance->hide('obj.private,obj.protected')
                    ->setMaxDepth(10)
                    ->streamTo(self::$_stream)
                    ;
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}
