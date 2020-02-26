<?php

class Redis {
    private $fp = null;
    private $buffer = '';

    function __construct() {
        $this->_connect();
    }

    function __destruct() {
        fclose($this->fp);
    }

    function get($key) {
        $this->_do_exec(['get', $key]);
        return $this->_read_response();
    }

    function set($key, $value) {
        $this->_do_exec(['set', $key, $value]);
        return $this->_read_response();
    }

    function del($key) {
        $this->_do_exec(['del', $key]);
        return $this->_read_response();
    }

    private function _connect() {
        if (!is_resource($this->fp)) {
            $this->fp = fsockopen(REDIS_HOST, REDIS_PORT);
        }
    }

    private function _readline() {
        $buff = $this->buffer;
        while (is_resource($this->fp) && $this->_strpos($buff, "\r\n") === False) {
            $buff = fread($this->fp, 256);
            $this->buffer .= $buff;
        }
        $buff = $this->_strstr($this->buffer, "\r\n", true);
        $this->buffer = $this->_strstr($this->buffer, "\r\n");
        $this->buffer = $this->_substr($this->buffer, 2);
        return $buff;
    }

    private function _read($length) {
        $buff = $this->_substr($this->buffer, 0, $length);
        $this->buffer = $this->_substr($this->buffer, $length);
        return $buff;
    }

    private function _read_response() {
        $response = $this->_readline();
        $flag = $this->_substr($response, 0, 1);
        $response = $this->_substr($response, 1);
        switch ($flag) {
            case '+':
                return $response;
            case '-':
                return $response;
            case ':':
                return intval($response);
            case '$':
                $length = intval($response);
                if ($length === -1) {
                    return null;
                }
                $response = $this->_read($length);
                $this->_read(2); // \r \n
                return $response;
            case '*':
                $length = intval($response);
                if ($length === -1) {
                    return null;
                }
                $response = [];
                for (; $length>0; $length--) {
                    $response[] = $this->_read_response();
                }
                return $response;
        }
    }

    private function _do_exec($commands=[]) {
        $this->_connect();
        $buff = "*" . count($commands) . "\r\n";
        foreach ($commands as $command) {
            $buff .= '$' . $this->_strlen($command) . "\r\n";
            $buff .= $command . "\r\n";
        }
        fwrite($this->fp, $buff);
    }

    // Compatible strlen function
    private function _strlen($string)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string);
        } else {
            return strlen($string);
        }
    }

    // Compatible substr function
    private function _substr($string, $start, $length = 2147483647)
    {
        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length);
        } else {
            return substr($string, $start, $length);
        }
    }

    // Compatible strpos function
    private function _strpos($haystack, $needle, $offset=0) {
        if (function_exists('mb_strpos')) {
            return mb_strpos($haystack, $needle, $offset);
        } else {
            return strpos($haystack, $needle, $offset);
        }
    }

    // Compatible strstr function
    private function _strstr($haystack, $needle, $before_needle=FALSE) {
        if (function_exists('mb_strstr')) {
            return mb_strstr($haystack, $needle, $before_needle);
        } else {
            return strstr($haystack, $needle, $before_needle);
        }
    }
}