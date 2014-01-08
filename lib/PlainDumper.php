<?php

class AnsiPlainDumper {

  private static $_fp = null;

  public static function getInstance() {
    if (self::$_fp === null)
      self::$_fp = fopen(BP.DS.'var'.DS.'log'.DS.'plain.mlog', 'a+');

    $instance = new AnsiDumper();
    return $instance->hide('obj.private,obj.protected')
                    ->setMaxDepth(10)
                    ->usePrinterCache(false)
                    ->enablePlainModus()
                    ->streamTo(self::$_fp);
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}
