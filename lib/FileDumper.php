<?php

class AnsiFileDumper {

  private static $_fp = null;

  public static function getInstance() {
    if (self::$_fp === null)
      self::$_fp = fopen(BP.DS.'var'.DS.'log'.DS.'mm.log', 'a+');

    $instance = new AnsiDumper();
    return $instance->hide('obj.private,obj.protected')
                    ->setMaxDepth(10)
                    ->usePrinterCache(false)
                    ->streamTo(self::$_fp);
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}