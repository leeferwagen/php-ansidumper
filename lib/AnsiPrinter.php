<?php

/**
 * Printer class used in AnsiDumper.
 */
class AnsiPrinter {

  private $_colors = array();
  private $_modus = '';
  private $_clearScreen = '';
  private $_tab = '';
  private $_rx = null;

  public function __construct() {
    $this->enableCliModus();
  }

  /**
   * Return current print mode.
   * @return string
   */
  public function getModus() {
    return $this->_modus;
  }

  /**
   * Enable CLI mode.
   * @return AnsiPrinter
   */
  public function enableCliModus() {
    $this->_colors = array(
      // styles
      'bold' => array("\033[1m", "\033[22m"),
      'italic' => array("\033[3m", "\033[23m"),
      'underline' => array("\033[4m", "\033[24m"),
      'inverse' => array("\033[7m", "\033[27m"),
      // colors
      'black' => array("\033[30m", "\033[39m"),
      'red' => array("\033[31m", "\033[39m"),
      'green' => array("\033[32m", "\033[39m"),
      'yellow' => array("\033[33m", "\033[39m"),
      'blue' => array("\033[34m", "\033[39m"),
      'magenta' => array("\033[35m", "\033[39m"),
      'cyan' => array("\033[36m", "\033[39m"),
      'white' => array("\033[37m", "\033[39m"),
      'grey' => array("\033[90m", "\033[3m9")
    );
    $this->_modus = 'cli';
    $this->_clearScreen = "\033c\033[3J";
    $this->_tab = '  ';
    $this->_rx = '/<(?:\{(' . implode('|', array_keys($this->_colors)) . ')|\})>/i';
    return $this;
  }

  /**
   * Enable HTML mode.
   * @return AnsiPrinter
   */
  public function enableHtmlModus() {
    $this->_colors = array(
      // styles
      'bold' => array('<span style="font-weight:bold;">', '</span>'),
      'italic' => array('<span style="font-style:italic;">', '</span>'),
      'underline' => array('<span style="text-decoration:underline;">', '</span>'),
      'inverse' => array('', ''),
      // colors
      'black' => array('<span style="color:black;">', '</span>'),
      'red' => array('<span style="color:red;">', '</span>'),
      'green' => array('<span style="color:green;">', '</span>'),
      'yellow' => array('<span style="color:yellow;">', '</span>'),
      'blue' => array('<span style="color:blue;">', '</span>'),
      'magenta' => array('<span style="color:magenta;">', '</span>'),
      'cyan' => array('<span style="color:cyan;">', '</span>'),
      'white' => array('<span style="color:white;">', '</span>'),
      'grey' => array('<span style="color:grey;">', '</span>')
    );
    $this->_modus = 'html';
    $this->_clearScreen = '';
    $this->_tab = '&nbsp;&nbsp;';
    $this->_rx = '/<(?:\{(' . implode('|', array_keys($this->_colors)) . ')|\})>/i';
    return $this;
  }

  /**
   * Enable Plain Text mode.
   * @return AnsiPrinter
   */
  public function enablePlainModus() {
    $this->_colors = array(
      // styles
      'bold' => array('', ''),
      'italic' => array('', ''),
      'underline' => array('', ''),
      'inverse' => array('', ''),
      // colors
      'black' => array('', ''),
      'red' => array('', ''),
      'green' => array('', ''),
      'yellow' => array('', ''),
      'blue' => array('', ''),
      'magenta' => array('', ''),
      'cyan' => array('', ''),
      'white' => array('', ''),
      'grey' => array('', ''),
    );
    $this->_modus = 'plain';
    $this->_clearScreen = '';
    $this->_tab = '  ';
    $this->_rx = '/<(?:\{(' . implode('|', array_keys($this->_colors)) . ')|\})>/i';
    return $this;
  }

  /**
   * Return the "clearing" ANSI sequence (only available in CLI mode).
   * @return string
   */
  public function clearScreen() {
    return $this->_clearScreen;
  }

  /**
   * Transform the string in $format to ANSI or HTML.
   * @param string $format
   * @return string
   */
  public function colorize($format) {
    if (func_num_args() > 1) {
      $format = vsprintf($format, array_slice(func_get_args(), 1));
    }

    $res = '';
    $offset = 0;
    $history = array();
    while (preg_match($this->_rx, $format, $m, PREG_OFFSET_CAPTURE, $offset)) {
      $res .= substr($format, $offset, $m[0][1] - $offset);
      if (isset($m[1])) {
        // Opening tag found. Append the starting escape string sequence.
        $esc = $this->_colors[$m[1][0]];
        $history[] = $esc;
        $res .= $esc[0];
      } else if (count($history)) {
        // Closing tag found. Append the closing escape string sequence of the current color.
        $esc = array_pop($history);
        $res .= $esc[1];
        if (count($history) && $this->_modus === 'cli') {
          // Continue with previous starting escape string sequence.
          $res .= $history[ count($history) - 1][0];
        }
      }
      $offset = $m[0][1] + strlen($m[0][0]);
    }
    $res .= substr($format, $offset);
    while (count($history)) {
      // Close all remaining tags.
      $esc = array_pop($history);
      $res .= $esc[1];
    }

    if ($this->_modus === 'html') {
      $res = str_replace("\n", "<br>\n", $res);
    }
    return $res;
  }

  /**
   * Return the Tabulator of the current print mode.
   * @param int $depth
   * @return string
   */
  public function tab($depth) {
    return str_repeat($this->_tab, $depth);
  }

}
