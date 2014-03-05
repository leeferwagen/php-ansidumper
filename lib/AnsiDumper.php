<?php

require_once(dirname(__FILE__).'/AnsiPrinter.php');


/**
 * Dumper Class
 *
 * @author  Muslim Idris <gelamu@gmail.com>
 * @license MIT
 * @package AnsiDumper
 */

class AnsiDumper {

  private $_stream = null;
  private $_hide = array();
  private $_paused = false;
  private $_printer = null;
  private $_maxDepth = 5;

  private $_fnmap = array(
    'NULL'     => array('print_type' => false, 'method' => '_format_null_value'),
    'string'   => array('print_type' => false, 'method' => '_format_string_value'),
    'double'   => array('print_type' => false, 'method' => '_format_double_value'),
    'integer'  => array('print_type' => false, 'method' => '_format_integer_value'),
    'boolean'  => array('print_type' => false, 'method' => '_format_boolean_value'),
    'array'    => array('print_type' => false, 'braces' => array('[',  ']'), 'method' => '_format_array_value'),
    'resource' => array('print_type' => true,  'braces' => array('(',  ')'), 'method' => '_format_resource_value'),
    'object'   => array('print_type' => true,  'braces' => array(' {', '}'), 'method' => '_format_object_value'),
  );

  /**
   * Constructor
   */
  public function __construct() {
    $this->_printer = new AnsiPrinter();
  }


  /**
   * Pause the output.
   * @return AnsiDumper
   */
  public function pause() {
    $this->_paused = true;
    return $this;
  }


  /**
   * Resume the output.
   * @return AnsiDumper
   */
  public function resume() {
    $this->_paused = false;
    return $this;
  }


  /**
   * Dump a mixed value.
   *
   * @param mixed   $value
   * @return AnsiDumper
   */
  public function val($value) {
    if ($this->_paused || !is_resource($this->_stream)) {
      return $this;
    }
    return $this->_prepareDumpAndWrite('<{green>' . $this->_any($value, 0) . '<}>');
  }
  
  /**
   * Dump a mixed value with additionally prepended time.
   * 
   * @param mixed $value
   * @return AnsiDumper
   */
  public function tval($value) {
    if ($this->_paused || !is_resource($this->_stream)) {
      return $this;
    }
    $mt = explode('.', (string)microtime(true));
    $ts = date('Y-m-d H:i:s', (int)$mt[0]) . '.' . substr(str_pad($mt[1], 3, '0'), 0, 3);
    return $this->_prepareDumpAndWrite('<{green><{magenta>[' . $ts . ']<}> ' . $this->_any($value, 0) . '<}>');
  }

  /**
   * Pass an array or a comma-seperated string of unwanted stuff.
   * Valid "stuff" is:
   *   obj            hide objects
   *   obj.static     hide static object properties and methods
   *   obj.public     hide public object properties and methods
   *   obj.private    hide private object properties and methods
   *   obj.protected  hide protected object properties and methods
   *   obj.iterable  hide iterable key-value-pairs in objects
   *   arr            hide arrays
   *
   * @param string|array $keys
   * @return AnsiDumper
   */
  public function hide($keys) {
    if (is_string($keys)) {
      $keys = array_map('trim', explode(',', $keys));
    }
    $this->_hide = array_merge($this->_hide, $keys);
    return $this;
  }


  /**
   * Enable colorized CLI dumps (no HTML-Tags).
   *
   * @return AnsiDumper
   */
  public function enableCliModus() {
    $this->_printer->enableCliModus();
    return $this;
  }


  /**
   * Enable HTML dumps.
   *
   * @return AnsiDumper
   */
  public function enableHtmlModus() {
    $this->_printer->enableHtmlModus();
    return $this;
  }


  /**
   * Enable Plain Text dumps.
   *
   * @return AnsiDumper
   */
  public function enablePlainModus() {
    $this->_printer->enablePlainModus();
    return $this;
  }


  /**
   * Clear Screen (only supported in CLI modus).
   *
   * @return AnsiDumper
   */
  public function clearScreen() {
    if ($this->_paused || !is_resource($this->_stream)) {
      return $this;
    }
    fwrite($this->_stream, $this->_printer->clearScreen());
    return $this;
  }


  /**
   * Set maximum depth in Arrays, Objects, Iterables, etc.
   *
   * @param int $maxDepth
   * @return AnsiDumper
   */
  public function setMaxDepth($maxDepth) {
    $this->_maxDepth = abs((int)$maxDepth);
    return $this;
  }


  /**
   * Stream to File
   *
   * @param resource $stream
   * @return AnsiDumper
   */
  public function streamTo($stream) {
    if (is_resource($stream)) {
      $this->_stream = $stream;
    }
    return $this;
  }


  /*  +---------------------+
     ++---------------------++
     ||   Private Methods   ||
     ++---------------------++
      +---------------------+  */

  /**
   * Private method to format mixed values.
   *
   * @param mixed   $value
   * @param int     $depth
   * @return string
   */
  private function _any($value, $depth) {
    if ($depth >= $this->_maxDepth) {
      return '';
    }

    $type = gettype($value);
    if (!isset($this->_fnmap[$type])) {
      return '<{cyan>' . $type . '<}>';
    }

    $fn = $this->_fnmap[$type];
    $ret = call_user_func(array($this, $fn['method']), $value, $depth);
    $typeString = $fn['print_type']
                ? ('<{cyan>' . (is_object($value) ? get_class($value) : (isset($fn['label']) ? $fn['label'] : $type)) . '<}>')
                : '';
    if (isset($fn['braces']) && count($fn['braces']) === 2) {
      $ret = "{$fn['braces'][0]}{$ret}{$fn['braces'][1]}";
    }
    return "{$typeString}{$ret}";
  }


  /**
   * Return a formatted string of parameters.
   *
   * @param array   $params
   * @return string
   */
  private function _formatParameters(array $params) {
    $requiredList = $optionalList = array();
    foreach ($params as $param) {
      $format = $this->_formatParameter($param);
      if ($param->isOptional()) {
        $optionalList[] = $format;
      } else {
        $requiredList[] = $format;
      }
    }
    $ret = implode(', ', $requiredList);
    if ($optionalList) {
      $ret .= ($requiredList ? ' [, ' : '[ ');
      $ret .= implode(' [, ', $optionalList) . ' ' . str_repeat(']', count($optionalList));
    }
    return $ret;
  }


  /**
   * Return a formatted string of a single parameter.
   *
   * @param ReflectionParameter $param
   * @return string
   */
  private function _formatParameter(ReflectionParameter $param) {
    $res = '';
    $parts = array();
    if ($class = $param->getClass()) {
      $res = '<{cyan>' . $class->getName() . '<}> ';
    }
    if ($param->isPassedByReference()) {
      $res .= '<{red>&<}>';
    }
    $res .= '<{blue>$' . $param->getName() . '<}>';
    return $res;
  }


  /**
   * Private function which returns true if the Property/Method fits the currently desired accessibilities.
   *
   * @param Reflector $propOrMethod
   * @return bool
   */
  private function _filterByDesiredAccessibility($propOrMethod) {
    return !(in_array('obj.static', $this->_hide) && $propOrMethod->isStatic())
        && !(in_array('obj.public', $this->_hide) && $propOrMethod->isPublic())
        && !(in_array('obj.private', $this->_hide) && $propOrMethod->isPrivate())
        && !(in_array('obj.protected', $this->_hide) && $propOrMethod->isProtected());
  }


  /**
   * Colorize and write the dump.
   *
   * @param string  $dump
   * @return AnsiDumper
   */
  private function _prepareDumpAndWrite($dump) {
    $dump = $this->_printer->colorize($dump);
    if (substr($dump, -1) !== "\n") {
      $dump .= "\n";
    }
    fwrite($this->_stream, $dump);
    return $this;
  }





  /**
   * Formatter for Array values.
   *
   * @param array   $array
   * @param int     $depth
   * @return string
   */
  private function _format_array_value($array, $depth) {
    $res = '';
    if (!in_array('arr', $this->_hide) && count($array)) {
      $res .= "\n";
      $depth++;
      $tab = $this->_printer->tab($depth);
      foreach ($array as $key => $value) {
        if (is_string($key)) {
          $res .= sprintf("%s\"<{red>%s<}>\" => %s\n",
                          $tab, addcslashes($key, "'"), $this->_any($value, $depth));
        } else {
          $res .= sprintf("%s<{yellow>%s<}> => %s\n",
                          $tab, $key, $this->_any($value, $depth));
        }
      }
      $depth--;
      $res .= $this->_printer->tab($depth);
    }
    return $res;
  }


  /**
   * Formatter for Class Instance values.
   *
   * @param object  $object
   * @param int     $depth
   * @return string
   */
  private function _format_object_value($object, $depth) {
    $res = '';
    if (in_array('obj', $this->_hide)) {
      return $res;
    }

    $depth++;
    $rc = new ReflectionClass($object);
    if (($fileName = $rc->getFileName()) !== false) {
      $res .= sprintf("%sClass File: <{cyan>%s<}>\n",
                      $this->_printer->tab($depth), $fileName);
    }

    $methods = array_filter($rc->getMethods(), array($this, '_filterByDesiredAccessibility'));
    if (count($methods)) {
      $res .= sprintf("%sMethods:\n", $this->_printer->tab($depth));
      $depth++;
      $tab = $this->_printer->tab($depth);
      foreach ($methods as $method) {
        $modifierNames = Reflection::getModifierNames($method->getModifiers());
        $res .= sprintf("%s<{magenta>%s<}> <{yellow>%s<}>(%s)\n",
                        $tab, implode(' ', $modifierNames), $method->getName(),
                        $this->_formatParameters($method->getParameters()));
      }
      $depth--;
    }

    $properties = array_filter($rc->getProperties(), array($this, '_filterByDesiredAccessibility'));
    if (count($properties)) {
      $res .= sprintf("%sProperties:\n", $this->_printer->tab($depth));
      $depth++;
      $tab = $this->_printer->tab($depth);
      foreach ($properties as $prop) {
        $modifierNames = Reflection::getModifierNames($prop->getModifiers());
        $prop->setAccessible(true);
        $propValue = $prop->getValue($object);
        $res .= sprintf("%s<{magenta>%s<}> <{yellow>%s<}>: %s\n",
                        $tab, implode(' ', $modifierNames), $prop->getName(),
                        $this->_any($propValue, $depth + 1));
      }
      $depth--;
    }

    if (!in_array('obj.iterable', $this->_hide)) {
      if ($object instanceof stdClass || $object instanceof Traversable || $object instanceof ArrayAccess) {
        try {
          $vars = array();
          foreach ($object as $propName => $propValue) {
            $vars[$propName] = $propValue;
          }
          if (count($vars)) {
            $res .= sprintf("%sIterables:\n", $this->_printer->tab($depth));
            $depth++;
            $tab = $this->_printer->tab($depth);
            foreach ($vars as $propName => $propValue) {
              $res .= sprintf("%s<{yellow>%s<}>: %s\n",
                              $tab, $propName,
                              $this->_any($propValue, $depth));
            }
            $depth--;
          }
        } catch(Exception $e) {
          $res .= sprintf("%sIterables: <{red><{inverse> %s <}>\n",
                          $this->_printer->tab($depth), $e->getMessage());
        }
      }
    }
    if ($res !== '') {
      $res = "\n" . $res . $this->_printer->tab($depth-1);
    }
    return $res;
  }


  /**
   * Formatter for Resource values.
   *
   * @param resource $r
   * @return string
   */
  private function _format_resource_value($r) {
    return "<{magenta>{$r}<}>";
  }


  /**
   * Formatter for NULL values.
   *
   * @return string
   */
  private function _format_null_value() {
    return '<{yellow>null<}>';
  }


  /**
   * Formatter for String values.
   *
   * @param string  $s
   * @return string
   */
  private function _format_string_value($s) {
    return '<{red>"' . $s . '"<}>';
  }


  /**
   * Formatter for Integer values.
   *
   * @param int     $i
   * @return string
   */
  private function _format_integer_value($i) {
    return "<{yellow>{$i}<}>";
  }


  /**
   * Formatter for Double values.
   *
   * @param unknown $d
   * @return string
   */
  private function _format_double_value($d) {
    return "<{magenta>{$d}<}>";
  }


  /**
   * Formatter for Boolean values.
   *
   * @param bool    $b
   * @return string
   */
  private function _format_boolean_value($b) {
    return $b ? '<{yellow>true<}>' : '<{red>false<}>';
  }

}
