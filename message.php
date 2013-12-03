<?php
/**
 * message.php - Sends messages
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
require 'z/system/configuration.php';
require $includesPath . '/authorization.php';
require $includesPath . '/common.php';
//
// Variables
//
$message = null;
$messagePost = securePost('message');
//
// Program
//
if (isset($_POST['send'])) {
    if (!isset($_POST['group'])) {
        $message = 'No group was selected.';
    }
    if ($_POST['message'] == null) {
        $message = 'No message was entered.';
    }
    if (isset($_POST['group']) and $_POST['message'] != null) {
        //
        // Okay to send message, set the value of From:
        //
        $dbh = new PDO($db);
        $stmt = $dbh->prepare('SELECT fullName FROM usersRecipients WHERE idUser=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($_SESSION['userId']));
        $row = $stmt->fetch();
        isset($row['fullName']) ? extract($row) : $fullName = null;
        $stmt = $dbh->prepare('SELECT DISTINCT address FROM send WHERE idUser=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($_SESSION['userId']));
        $row = $stmt->fetch();
        isset($row['address']) ? extract($row) : $address = null;
        if ($fullName != null and $address != null) {
            $from = $fullName . ' <' . plain($address) . '>';
        } else {
            $from = 'IncompleteRecipientRecord@' . $domain;
        }
        //
        // Assemble the appropriate sql statement
        //
        if ($_POST['group']['0'] == 'all') {
            $sql = 'SELECT DISTINCT address FROM send';
            $_POST['group'] = null;
        } else {
            $groupCount = null;
            foreach ($_POST['group'] as $group) {
                $groupCount++;
            }
            $sql = 'SELECT DISTINCT address FROM send WHERE idGroup=?';
            if ($groupCount > 1) {
                $count = 1;
                while ($count < $groupCount) {
                    $count++;
                    $sql.= ' OR idGroup=?';
                }
            }
        }
        //
        // Send message
        //
        $stmt = $dbh->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute($_POST['group']);
        foreach ($stmt as $row) {
            extract($row);
            mail(plain($address) . "\r\n", "\r\n", $messagePost . "\r\n", 'From: ' . $from . "\r\n");
        }
        $dbh = null;
        mail($emailTo . "\r\n", "\r\n", $messagePost . "\r\n", 'From: ' . $from . "\r\n", '-f' . $from . "\r\n");
        $message = 'The message was sent.';
        $messagePost = null;
    }
}
//
// HTML
//
require 'z/includes/header1.inc';
echo '  <title>Send message</title>' . "\n";
echo '  <script type="text/javascript" src="z/counter.js"></script>' . "\n";
require 'z/includes/header2.inc';
require 'z/includes/body.inc';
?>

  <h4 class="m"><a class="s" href="message.php">&nbsp;Message&nbsp;</a><a class="m" href="groups.php">&nbsp;Groups&nbsp;</a><a class="m" href="users.php">&nbsp;Users&nbsp;</a><a class="m" href="recipients.php">&nbsp;Recipients&nbsp;</a><a class="m" href="carriers.php">&nbsp;Carriers&nbsp;</a></h4>
<?php echoIfMessage($message); ?>

  <h1>Send a message</h1>

  <form action="<?php echo $uri; ?>message.php" method="post">
<?php
$dbh = new PDO($db);
$stmt = $dbh->prepare('SELECT idGroup, groupName FROM groups ORDER BY groupName');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
echo '    <p><span class="b"><input type="checkbox" name="group[]" value="all" /> All</span>&nbsp;&nbsp;&nbsp;&nbsp;';
foreach ($stmt as $row) {
    extract($row);
    echo '<span class="b"><input type="checkbox" name="group[]" value="' . $idGroup . '" /> ' . html($groupName) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;';
}
echo "</p>\n";
$dbh = null;
?>

    <p>600 character limit, 120 suggested: <span id="counterMessage"></span><br />
    <textarea id="message" name="message" rows="15" cols="35" maxlength="601"><?php echo html($messagePost); ?></textarea></p>

    <p><input type="submit" value="Send message" name="send" class="button" /></p>
  </form>
</body>
</html>