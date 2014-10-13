<?php

require_once(dirname(__FILE__) . '/AnsiDumper.php');


/**
 * Standard Dumper (used to write colorized dumps to STDOUT).
 *
 * @method static AnsiDumper pause() Pause the output
 * @method static AnsiDumper resume() Resume the output
 * @method static AnsiDumper val(mixed $value) Dump a mixed value
 * @method static AnsiDumper tval(mixed $value) Dump a mixed value with prepending time
 * @method static AnsiDumper hide(string|array $keys) Pass an array or a comma-seperated string of unwanted stuff
 * @method static AnsiDumper enableCliModus() Enable colorized CLI dumps (no HTML-Tags)
 * @method static AnsiDumper enableHtmlModus() Enable HTML dumps
 * @method static AnsiDumper enablePlainModus() Enable Plain Text dumps
 * @method static AnsiDumper clearScreen() Clear Screen (only supported in CLI modus)
 * @method static AnsiDumper setMaxDepth(int $maxDepth) Set maximum depth in Arrays, Objects, Iterables, etc.
 * @method static AnsiDumper streamTo(resource $stream) Stream to File
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
