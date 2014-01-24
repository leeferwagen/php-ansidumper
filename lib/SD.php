<?php

require_once(dirname(__FILE__) . '/AnsiDumper.php');


/**
 * Standard Dumper (used to write colorized dumps to STDOUT).
 */
class SD {

  /**
   * Singleton of AnsiDumper.
   * @return AnsiDumper
   */
  public static function getInstance() {
    $instance = new AnsiDumper();
    return $instance->hide('obj.private,obj.protected')
                    ->setMaxDepth(10)
                    ->streamTo(STDOUT)
                    ;
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}
