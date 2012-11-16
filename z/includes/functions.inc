<?php
/**
 * functions.inc - Functions
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-11-16 database A
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
/**
 * Function to secure text input strings and remove magic quote nonsense
 *
 * @param string $str The string
 *
 * @return The secure version of the string
 */
function secure($str)
{
    return strval(strip_tags(str_replace(array('[',']'), array('<','>'), stripslashes($str))));
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
    return htmlentities($str, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, "UTF-8");
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
?>
