<?php

function take_screenshot($url, $path=null) {
    if ($path && file_exists($path)) {
        unlink($path);
    }
    if (!$path) {
        $path = SCREENSHOT_STORAGE.gen_random_str(10).'.jpg';
    }
    $url = escapeshellarg($url);
    shell_exec('xvfb-run ' . WKHTML_TO_IMAGE . " --disable-javascript $url $path");
    return file_exists($path) ? $path : null;
}

function gen_random_str($length=10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function render($template, $data=[]) {
    $from_render = true;
    foreach ($data as $key => $value) {
        $$key = $value;
    }
    require_once('templates/'.$template.'.php');
}

function url($uri){
    if(isset($_SERVER['HTTPS'])){
        $protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
    }
    else{
        $protocol = 'http';
    }
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $uri;
}