<?php

session_start();
if (!$sess = session_id()) {
    die('session error.');
}

if (!isset($_REQUEST['url'])) {
    die('parameter error');
}

$base = '/tmp/sandbox/'.$sess;
$home = $base.'/home';
$temp = $base.'/temp';
mkdir($home, 0700, true);
mkdir($temp, 0700, true);
chdir($temp);
$env = array(
    'PATH' => '/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin',
    'HOME' => $home,
    'PWD' => $temp,
);

$fdSpecs = array(
    1 => array('pipe', 'w'),
    2 => array('pipe', 'w'),
);
$process = proc_open(['wget', $_REQUEST['url']], $fdSpecs, $pipes, $temp, $env);
echo stream_get_contents($pipes[1]);
echo stream_get_contents($pipes[2]);
