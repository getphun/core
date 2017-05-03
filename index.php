<?php
/**
 * Engine starter
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

define('BASEPATH', dirname(__FILE__));
require_once BASEPATH . '/modules/core/Phun.php';
Phun::run();