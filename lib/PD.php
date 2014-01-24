<?php

require_once(dirname(__FILE__) . '/AnsiDumper.php');


/**
 * Plain Dumper. Can be used to dump data without any ANSI/HTML colors using the PD_STREAM file descriptor.
 */
class PD {

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
      if (defined('PD_FILE')) {
        self::$_stream = fopen(PD_FILE, 'a+');
      } elseif (isset($_SERVER['PD_FILE'])) {
        self::$_stream = fopen($_SERVER['PD_FILE'], 'a+');
      }
    }

    $instance = new AnsiDumper();
    return $instance->hide('obj.private,obj.protected')
                    ->setMaxDepth(10)
                    ->enablePlainModus()
                    ->streamTo(self::$_stream)
                    ;
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}
