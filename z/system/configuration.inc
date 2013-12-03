<?php
/**
 * configuration.php - Set configuration variables
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-12-27 database B
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
//
// INPUT: Administrative e-mail addresses for back ups and message copies
//
$domain = 'mysite.org';
$emailTo = 'log@mysite.org';
$emailFrom = 'noreply@mysite.org';
//
// INPUT: Time zone offset from GMT, Greenwich Mean Time
// The following entries are allowed:
//   -12 -11 -10 -9 -8 -7 -6 -5 -4.30 -4 -3.30 -3 -2 -1 0
//   1 2 3 3.30 4 5 5.30 5.45 6 6.30 7 8 9 9.30 10 11 12 13
//
$gmtOffset = '-8';
//
// INPUT: Path the the includes folder
//
$includesPath = 'z/includes';
//
// INPUT: URI scheme, http or https
//
$uriScheme = 'http';
?>