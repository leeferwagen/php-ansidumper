<?php

require_once(dirname(__FILE__) . '/AnsiDumper.php');


/**
 * File Dumper. Can be used to write colorized dumps to a file using the FD_STEAM file descriptor.
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
class FD {

  /**
   * @var resource $_stream
   */
  private static $_stream = null;

  /**
   * @var FD $_instance
   */
  private static $_instance = null;

  /**
   * @var string $_scope
   */
  private static $_scope = null;

  /**
   * Return the singleton instance of AnsiDumper.
   * @return AnsiDumper
   */
  public static function getInstance() {
    if (self::$_instance === null) {
      if (self::$_stream === null) {
        if (defined('FD_FILE')) {
          self::$_stream = fopen(FD_FILE, 'a+');
        } elseif (isset($_SERVER['FD_FILE'])) {
          self::$_stream = fopen($_SERVER['FD_FILE'], 'a+');
        }
      }

      if (self::$_scope === null) {
        if (defined('FD_SCOPE')) {
          self::$_scope = FD_SCOPE;
        } elseif (isset($_SERVER['FD_SCOPE'])) {
          self::$_scope = $_SERVER['FD_SCOPE'];
        }
      }

      self::$_instance = new AnsiDumper();
      self::$_instance->hide('obj.private,obj.protected')
                      ->setMaxDepth(10)
                      ->setScope(self::$_scope)
                      ->streamTo(self::$_stream);
    }
    return self::$_instance;
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}
