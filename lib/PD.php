<?php

namespace AnsiDumper

/**
 * Plain Dumper. Can be used to dump data without any ANSI/HTML colors using the PD_STREAM file descriptor.
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
class PD
{

    /**
     * @var resource $_stream
     */
    private static $_stream = null;

    /**
     * Singleton of AnsiDumper.
     * @return AnsiDumper
     */
    public static function getInstance()
    {
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
            ->streamTo(self::$_stream);
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(self::getInstance(), $name), $arguments);
    }

}
