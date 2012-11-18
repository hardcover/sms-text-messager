<?php
/**
 * authorization.php - Allows logged in users by, sends others to logout.php
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-11-17 database B
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
session_start();
//
// Set $uri and $oldCookie
//
require 'z/includes/INPUTS.php';
$uri = $uriScheme . '://' . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . '/';
$oldCookie = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : null;
//
// Test authorization
//
if (!isset($_SESSION['auth']) or (strval(session_id()) !== strval($oldCookie)) or (strval($_SESSION['auth']) !== strval(hash('sha256', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']) . $_SESSION['userID'])) or isset($_SERVER['HTTP_X_FORWARDED_FOR']) or isset($_SERVER['HTTP_X_FORWARDED']) or isset($_SERVER['HTTP_FORWARDED_FOR']) or in_array($_SERVER['REMOTE_PORT'], array(8080, 80, 6588, 8000, 3128, 553, 554))) {
    header('Location: ' . $uri . 'logout.php');
}
session_regenerate_id(true);
?>
