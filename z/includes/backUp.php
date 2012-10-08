<?php
/**
 * backUp.php - Backs up the database to an e-mail account set in INPUTS.php
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
$dbh = new PDO('sqlite::memory:');
$stmt = $dbh->query('CREATE TABLE "a" ("b");');
$dbh = null;
require 'INPUTS.php';
$attachment = "z/database/sms.sqlite";
$attachmentName = "sms.sqlite";
$emailSubject = $_SESSION['userS'];
date_default_timezone_set('America/Los_Angeles');
$attachmentType = "application/octet-stream";
$headers = 'From: ' . $emailFrom;
$file = fopen($attachment, 'rb');
$data = fread($file, filesize($attachment));
fclose($file);
$semi_rand = md5(time());
$mimeBoundary = "==Multipart_Boundary_x{$semi_rand}x";
$headers.= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mimeBoundary}\"";
$emailBody = "--------------------- sms/z/includes/INPUTS.php ---------------------\r\n";
$emailBody.= file_get_contents('z/includes/INPUTS.php');
$emailBody.= "\r\n---------------------------------------------------------------------\r\n";
$emailBody.= "This is a multi-part message in MIME format.\n\n" . "--{$mimeBoundary}\n" . 'Content-Type: text/plain; charset=ISO-8859-1; format=flowed' . "\r\n" . "Content-Transfer-Encoding: 7bit\n\n" . $emailBody . "\n\n";
$data = chunk_split(base64_encode($data));
$emailBody.= "--{$mimeBoundary}\n" . "Content-Type: {$attachmentType};\n" . " name=\"{$attachmentName}\"\n" . "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n" . "--{$mimeBoundary}--\n";
mail($emailTo . "\r\n", $emailSubject . "\r\n", $emailBody, $headers . "\r\n");
?>
