<?php

require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['url'])) {
    $url = strval($_POST['url']);
    if (!in_array(parse_url($url)['scheme'], ['http', 'https'], true)) {
        header('Location: /');
        exit();
    }

    $screenshot = take_screenshot($url);
    $id = gen_random_str(ID_LENGTH);
    $redis->set($id, $url);
    $redis->set($id.':screenshot', $screenshot);
    echo url('/'.$id);
    exit();
}

$uri = trim(substr($_SERVER['REQUEST_URI'], 1));
if (preg_match(ID_PATTERN, $uri)) {
    $id = $uri;
    $url = $redis->get($id);
    $screenshot_realpath = $redis->get($id.':screenshot');
    $screenshot = SCREENSHOT_PATH . basename($screenshot_realpath);

    if (isset($_POST['regen_screenshot'])) {
        take_screenshot($url, $screenshot_realpath);
    }

    if (isset($_REQUEST['go'])) {
        header("Location: $url");
        exit();
    }

    if (!$url) {
        header('Location: /');
        exit();
    }
    render('warning', ['url' => $url, 'screenshot' => $screenshot]);
    exit();
}

render('main');