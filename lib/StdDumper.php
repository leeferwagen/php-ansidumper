<?php

class AnsiStdDumper {

  public static function getInstance() {
    $instance = new AnsiDumper();
    return $instance->hide('obj.private,obj.protected')
                    ->setMaxDepth(10)
                    ->usePrinterCache(false)
                    ->streamTo(STDOUT)
                    ;
  }

  public static function __callStatic($name, $arguments) {
    return call_user_func_array(array(self::getInstance(), $name), $arguments);
  }

}