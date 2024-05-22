<?php

use App\Kernel;

// Use doi2pmh-server/.env file if not in a Docker container
if (file_exists(dirname(__FILE__) . '../../.env')){
    $_SERVER['APP_RUNTIME_OPTIONS']['dotenv_path'] = '../.env';
}

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};