<?php
/**
 * functions.inc - Functions
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
 * Function to echo input values, when they exist
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
?>
