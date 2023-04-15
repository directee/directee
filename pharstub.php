<?php
require_once 'phar://directee-backend.phar/vendor/autoload.php';
(new \Directee\WellBackend\Main(__DIR__))->run();
__HALT_COMPILER();
