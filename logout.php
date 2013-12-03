<?php
/**
 * logout.php - Available as a link once logged in, called by index.php in some cases
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2013 Hardcover LLC
 * @license   http://hardcoverwebdesign.com/license  MIT License
 *.@license   http://hardcoverwebdesign.com/gpl-2.0  GNU General Public License, Version 2
 * @version   GIT: 2013-12-1 database B
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
session_start();
$_SESSION = array();
session_destroy();
require 'z/system/configuration.php';
$uri = $uriScheme . '://' . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . '/';
header('Location: ' . $uri);
?>
