<?php
/**
 * logout.php - Available as a link once logged in, called by index.php in some cases
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <info@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-08-21 database A
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
session_start();
$_SESSION = array();
session_destroy();
setcookie(session_name(), '', time() -90000);
require 'z/includes/INPUTS.php';
$host = $_SERVER["HTTP_HOST"];
$path = rtrim(dirname($_SERVER['PHP_SELF']), "/\\");
header('Location: ' . $uriScheme . '://' . $host . $path . '/');
?>
