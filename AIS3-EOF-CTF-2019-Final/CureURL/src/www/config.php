<?php
define('ID_LENGTH', 6);
define('ID_PATTERN', '/^\w{'.ID_LENGTH.'}$/i');
define('WKHTML_TO_IMAGE', '/usr/bin/wkhtmltoimage');
define('SCREENSHOT_STORAGE', '/var/www/html/screenshots/');
define('SCREENSHOT_PATH', '/screenshots/');

define('REDIS_HOST', 'redis');
define('REDIS_PORT', 6379);

require_once('utils.php');
require_once('redis.php');

$redis = new Redis();