<?php
/**
 * SMS Text Messager for sending SMS messages from the command line
 *
 * PHP version 7
 *
 * @category  SMS
 * @package   SMS-Text-Messager
 * @author    Hardcover LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2016 Hardcover LLC
 * @license   http://hardcoverwebdesign.com/license  MIT License
 * @version:  2016 01 06
 * @link      http://hardcoverwebdesign.com/
 * @link      https://github.com/hardcover/
 *
 * Some common e-mail to SMS addresses
 *
 * AT&T Mobility    @txt.att.net
 * Cricket Wireless @sms.mycricket.com
 * Sprint Nextel    @messaging.sprintpcs.com
 * T-Mobile USA     @tmomail.net
 * U.S. Cellular    @email.uscc.net
 * Verizon Wireless @vtext.com
 *
 * Execute the following command line
 * php sms.php from to "message"
 */
if (isset($argv['1']) and isset($argv['2']) and isset($argv['3'])) {
    $to = $argv['2'] . "\r\n";
    $subject = "\r\n";
    $body = $argv['3'] . "\r\n";
    $headers = 'From: ' . $argv['1'] . "\r\n";
    $headers.= 'MIME-Version: 1.0' . "\r\n";
    $headers.= 'Content-Type: text/plain; charset=utf-8; format=flowed' . "\r\n";
    $headers.= 'Content-Transfer-Encoding: 7bit' . "\r\n";
    mail($to, $subject, $body, $headers);
}
?>