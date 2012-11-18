<?php
/**
 * logNotice.php - Records logins to an e-mail account
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
require 'z/includes/INPUTS.php';
date_default_timezone_set('America/Los_Angeles');
$subject = $row['fullName'] . " log in to SMS text messager\r\n";
$body = " \r\n";
mail($emailTo . "\r\n", $subject, $body, 'From:' . $emailFrom . "\r\n");
?>