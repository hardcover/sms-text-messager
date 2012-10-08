<?php
/**
 * authorization.php - Allows logged in users by, sends others to logout.php
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <info@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-10-7 database A
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
$host = $_SERVER["HTTP_HOST"]; // Host and path are used by all header functions
$path = rtrim(dirname($_SERVER['PHP_SELF']), "/\\");
require 'z/includes/INPUTS.php';
$uri = $uriScheme . '://' . $host . $path . '/';
if (!isset($_SESSION['auth']) || $_SESSION['auth'] != $_SESSION['userIdS'] . $_SESSION['userS'] . $_SERVER['REMOTE_ADDR']) {
    header("Location: http://$host$path/logout.php");
}
?>
