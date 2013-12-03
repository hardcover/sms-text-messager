<?php
/**
 * logNotice.php - Records logins to an e-mail account
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
$subject = $row['fullName'] . ' log in to SMS text messager';
mail($emailTo . "\r\n", $subject . "\r\n", "\r\n", 'From: ' . $emailFrom . "\r\n", '-f' . $emailFrom . "\r\n");
?>