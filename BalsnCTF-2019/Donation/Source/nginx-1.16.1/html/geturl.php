<?php
$url = $_POST['url'];
if (parse_url($url)['port'] == 9000) {
    die('blcoked!'); // avoid fastcgi exp
} else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $output = curl_exec($ch);
    curl_close($ch);
    print_r($output);
}
