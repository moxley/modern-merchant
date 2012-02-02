<?php
/**
 * @package build
 */

/**
 * A console prompt.
 *
 * @package build
 */
class prompt {
  var $tty;

  function prompt() {
    if (substr(PHP_OS, 0, 3) == "WIN") {
      $this->tty = fOpen("\con", "rb");
    } else {
      if (!($this->tty = fOpen("/dev/tty", "r"))) {
        $this->tty = fOpen("php://stdin", "r");
      }
    }
  }

  function get($string, $length = 1024) {
    echo $string;
    $str = fGets($this->tty, $length);
    if ($str === false || $str === null) {
      echo "\n";
      return "\004";
    }
    $result = trim($str);
    return $result;
  }
}
