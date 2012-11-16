<?php
/**
 * message.php - Sends messages
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
require 'z/includes/authorization.php';
//
// Programs
//
require 'z/includes/functions.inc';
$message = false;
require 'z/includes/db.php';
$_POST['message'] = isset($_POST['message']) ? stripslashes($_POST['message']) : null;
date_default_timezone_set('America/Los_Angeles');
if (isset($_POST['send'])) {
    if (!isset($_POST['group'])) {
        $message = 'No group was selected.';
        $_POST['message'] = isset($_POST['message']) ? $_POST['message'] = stripslashes($_POST['message']) : $_POST['message'] = null;
    }
    if ($_POST['message'] == null) {
        $message = 'No message was entered.';
    }
    if (isset($_POST['group']) and $_POST['message'] != null) {
        $_POST['message'] = stripslashes($_POST['message']);
        //
        // Okay to send message, set the value of From:
        //
        $dbh = new PDO($db);
        $stmt = $dbh->prepare('SELECT fullName FROM usersRecipients WHERE idUser=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($_SESSION['userId']));
        $row = $stmt->fetch();
        isset($row['fullName']) ? extract($row) : $fullName = null;
        $stmt = $dbh->prepare('SELECT DISTINCT address FROM send WHERE idUserInSend=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($_SESSION['userId']));
        $row = $stmt->fetch();
        isset($row['address']) ? extract($row) : $address = null;
        $from = ($fullName != null and $address != null) ? 'From: ' . $fullName . ' <' . plain($address) . ">\r\n" : 'From: IncompleteRecipientRecord@' . $domain . "\r\n";
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
            $sql = 'SELECT DISTINCT address FROM send WHERE idGroupInSend=?';
            if ($groupCount > 1) {
                $count = 1;
                while ($count < $groupCount) {
                    $count++;
                    $sql.= ' OR idGroupInSend=?';
                }
            }
        }
        //
        // Send message
        //
        include 'z/includes/INPUTS.php';
        $stmt = $dbh->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute($_POST['group']);
        foreach ($stmt as $row) {
            extract($row);
            mail(plain($address), "\r\n", $_POST['message'], $from);
        }
        $dbh = null;
        mail($emailTo, "\r\n", $_POST['message'], $from);
        $message = 'The message was sent.';
        $_POST['message'] = null;
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

  <h4><a class="s" href="message.php">&nbsp;Message&nbsp;</a><a class="m" href="groups.php">&nbsp;Groups&nbsp;</a><a class="m" href="users.php">&nbsp;Users&nbsp;</a><a class="m" href="recipients.php">&nbsp;Recipients&nbsp;</a><a class="m" href="carriers.php">&nbsp;Carriers&nbsp;</a></h4>
<?php echoIfMessage($message); ?>

  <h1>Send a message</h1>

  <form action="<?php echo $uri; ?>message.php" method="post">
<?php
$dbh = new PDO($db);
$stmt = $dbh->prepare('SELECT idGroup, groupName FROM groups ORDER BY groupName');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
echo '    <p><input type="checkbox" name="group[]" value="all" /> All&nbsp;&nbsp;&nbsp;&nbsp;';
foreach ($stmt as $row) {
    extract($row);
    echo '<span class="b"><input type="checkbox" name="group[]" value="' . $idGroup . '" /> ' . html($groupName) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;';
}
echo "</p>\n";
$dbh = null;
?>

    <p>600 character limit, 120 suggested: <span id="counterMessage"></span><br />
    <textarea id="message" name="message" rows="15" cols="35" maxlength="601"><?php echo html($_POST['message']); ?></textarea></p>

    <p><input type="submit" value="Send message" name="send" class="button" /></p>
  </form>
</body>
</html>