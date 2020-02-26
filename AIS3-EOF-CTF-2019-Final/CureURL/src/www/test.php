<?php

require_once('config.php');

var_dump($redis->get('test'));
var_dump($redis->set('test', 'success'));
var_dump($redis->get('test'));

phpinfo();