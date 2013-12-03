<?php
/**
 * common.php - Common variables and functions
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
//
// Variables
//
$db = 'sqlite:' . $includesPath . '/databases/sms.sqlite';
$dbl = 'sqlite:' . $includesPath . '/databases/log.sqlite';
//
// Set the default timezone based on the GMT offset in configuration.php
//
$timezone = array(
    -12     => 'Kwajalein',
    -11     => 'Pacific/Midway',
    -10     => 'Pacific/Honolulu',
     -9     => 'America/Anchorage',
     -8     => 'America/Los_Angeles',
     -7     => 'America/Denver',
     -6     => 'America/Tegucigalpa',
     -5     => 'America/New_York',
    "-4.30" => 'America/Caracas',
     -4     => 'America/Halifax',
    "-3.30" => 'America/St_Johns',
     -3     => 'America/Sao_Paulo',
     -2     => 'Atlantic/South_Georgia',
     -1     => 'Atlantic/Azores',
      0     => 'Europe/Dublin',
      1     => 'Europe/Belgrade',
      2     => 'Europe/Minsk',
      3     => 'Asia/Kuwait',
     "3.30" => 'Asia/Tehran',
      4     => 'Asia/Muscat',
      5     => 'Asia/Yekaterinburg',
     "5.30" => 'Asia/Kolkata',
     "5.45" => 'Asia/Katmandu',
      6     => 'Asia/Dhaka',
     "6.30" => 'Asia/Rangoon',
      7     => 'Asia/Krasnoyarsk',
      8     => 'Asia/Brunei',
      9     => 'Asia/Seoul',
     "9.30" => 'Australia/Darwin',
     10     => 'Australia/Canberra',
     11     => 'Asia/Magadan',
     12     => 'Pacific/Fiji',
     13     => 'Pacific/Tongatapu'
);
date_default_timezone_set($timezone[$gmtOffset]);
$today = date("Y-m-d");
/**
 * Function to secure and clean post and get variables
 *
 * @param string $str The value to be secured
 *
 * @return The secure version of the value
 */
function secure($str)
{
    $str = stripslashes($str);
    $str = html_entity_decode($str);
    $str = str_replace(array('[',']'), array('<','>'), $str);
    return strip_tags($str);
}
/**
 * Function to set a value to either null or a secure post variable
 *
 * @param mixed $param The post value
 *
 * @return The secure version of the value
 */
function securePost($param)
{
    if (isset($_POST[$param]) and $_POST[$param] != '') {
        $str = secure($_POST[$param]);
    } else {
        $str = null;
    }
    return $str;
}
/**
 * Function like securePost, also removes new lines and multiple spaces
 *
 * @param mixed $param The post value
 *
 * @return The cleaned version of the value
 */
function inlinePost($param)
{
    if (isset($_POST[$param]) and trim($_POST[$param]) != '') {
        $str = secure($_POST[$param]);
        $str = preg_replace("'\s+'", ' ', $str);
    } else {
        $str = null;
    }
    return $str;
}
/**
 * Function to convert applicable characters to HTML entities
 *
 * @param string $str The string
 *
 * @return The converted HTML version of the string
 */
function html($str)
{
    return @htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, "UTF-8");
}
/**
 * Function to echo information and error messages, when they exist
 *
 * @param string $str The message
 *
 * @return The appropriate HTML and message
 */
function echoIfMessage($str)
{
    if ($str == true) {
        echo "\n" . '  <p class="e">' . $str . "</p>\n";
    }
}
/**
 * Function to echo the input field value, when it exists
 *
 * @param string $str The value
 *
 * @return The appropriate HTML and value
 */
function echoIfValue($str)
{
    if ($str == true) {
        echo ' value="' . html($str) . '"';
    }
}
/**
 * Function to obfuscate a string value for human and simple machine readers
 *
 * @param string $str The original string value
 *
 * @return An encoded muddle
 */
function muddle($str)
{
    if ($str == null or $str == '') {
        return null;
    } else {
        return str_rot13(base64_encode($str));
    }
}
/**
 * Function to make plain a string value obfuscated with muddle
 *
 * @param string $str The encoded muddle
 *
 * @return The original string value before it was muddled
 */
function plain($str)
{
    if ($str == null or $str == '') {
        return null;
    } else {
        return base64_decode(str_rot13($str));
    }
}
/**
 * Function to e-mail attachments
 *
 * @param string $subject The e-mail subject
 * @param string $to      The To e-mail address
 * @param string $from    The From e-mail address
 * @param array  $files   The files to be e-mailed
 *
 * @return Nothing
 */
function mailAttachments($subject, $to, $from, $files)
{
    for ($mimeBoundary = '------------'; strlen($mimeBoundary) < 31;) {
        $mimeBoundary.= rand();
    }
    $headers = 'From: ' . $from . "\n";
    $headers.= "MIME-Version: 1.0\n";
    $headers.= "Content-Type: multipart/mixed;\n";
    $headers.= ' boundary="' . $mimeBoundary . '"';
    $message = "This is a multi-part message in MIME format.\n";
    $message.= '--' . $mimeBoundary . "\n";
    $message.= 'Content-Type: text/plain; charset="iso-8859-1"' . "\n";
    $message.= "Content-Transfer-Encoding: 7bit\n\n";
    for ($i = 0; $i < count($files); $i++) {
        if (is_file($files[$i])) {
            $fp = @fopen($files[$i], "rb");
            $data = @fread($fp, filesize($files[$i]));
            @fclose($fp);
            $data = chunk_split(base64_encode($data));
            $message.= '--' . $mimeBoundary . "\n";
            $message.= "Content-Type: application/octet-stream;\n";
            $message.= ' name="' . basename($files[$i]) . '"' . "\n";
            $message.= "Content-Transfer-Encoding: base64\n";
            $message.= "Content-Disposition: attachment;\n";
            $message.= ' filename="' . basename($files[$i]) . '"' . "\n";
            $message.= ' size="' . filesize($files[$i]) . '"' . "\n\n";
            $message.= $data;
        }
    }
    $message .= '--' . $mimeBoundary . '--';
    mail($to . "\r\n", $subject . "\r\n", $message . "\r\n", $headers . "\r\n", '-f' . $from . "\r\n");
}
?>
