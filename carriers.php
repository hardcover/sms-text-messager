<?php
/**
 * carriers.php - Carrier maintenance
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <info@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-08-21 database A
 * @link      http://smstextmessager.com/
 * @link      http://hardcoverwebdesign.com/
 */
session_start();
require 'z/includes/authorization.php';
//
// Programs
//
require 'z/includes/functions.inc';
$message = false;
require 'z/includes/db.php';
if (isset($_POST['adminPass']) and ($_POST['adminPass'] == null or $_POST['adminPass'] == '')) {
    $message = 'Your password is required for all user maintenance.';
}
//
// Prepare post data
//
$adminPassPost = isset($_POST['adminPass']) ? stripslashes($_POST['adminPass']) : null;
$carrierPost = isset($_POST['carrier']) ? stripslashes($_POST['carrier']) : null;
$emailSMSPost = isset($_POST['emailSMS']) ? stripslashes($_POST['emailSMS']) : null;
$carrierEdit = false;
$emailSMSEdit = false;
//
// Test password authentication
//
$hashAdmin = hash('sha512', $adminPassPost . $_SESSION['userS']);
$dbh = new PDO($db);
$stmt = $dbh->prepare('SELECT pass FROM usersRecipients WHERE user=?');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute(array($_SESSION['userS']));
$row = $stmt->fetch();
$dbh = null;
if ($hashAdmin == $row['pass']) {
    //
    // Buttons, insert, update, delete
    //
    if (isset($_POST['insert'])) {
        if ($_POST['carrier'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT carrier FROM carriers WHERE carrier=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($carrierPost));
            $row = $stmt->fetch();
            $dbh = null;
            if (isset($row['carrier'])) {
                header('Location: ' . $uri . 'users.php');
                exit;
            } else {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('INSERT INTO carriers (carrier, emailSMS) VALUES (?, ?)');
                $stmt->execute(array($carrierPost, $emailSMSPost));
                $dbh = null;
                include 'z/includes/backUp.php';
            }
        } else {
            $message = 'No carrier name was input.';
        }
    }
    if (isset($_POST['update'])) {
        if ($_POST['carrier'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('UPDATE carriers SET emailSMS=? WHERE carrier=?');
            $stmt->execute(array($emailSMSPost, $carrierPost));
            $dbh = null;
            include 'z/includes/backUp.php';
        } else {
            $message = 'No carrier name was input.';
        }
    }
    if (isset($_POST['delete'])) {
        if ($_POST['carrier'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT idCarrier FROM carriers WHERE carrier=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($carrierPost));
            $row = $stmt->fetch();
            extract($row);
            $stmt = $dbh->prepare('DELETE FROM carriers WHERE idCarrier=?');
            $stmt->execute(array($idCarrier));
            $stmt = $dbh->prepare('DELETE FROM send WHERE idCarrierInSend=?');
            $stmt->execute(array($idCarrier));
            $stmt = $dbh->prepare('UPDATE usersRecipients SET idCarrierInUser=? WHERE idCarrierInUser=?');
            $stmt->execute(array(null, $idCarrier));
            $stmt = $dbh->query('VACUUM');
            $dbh = null;
            include 'z/includes/backUp.php';
        } else {
            $message = 'No user name was input.';
        }
    }
} elseif (isset($_POST['insert']) or isset($_POST['update']) or isset($_POST['delete'])) {
    $message = 'The password is invalid.';
}
//
// Button, edit
//
if (isset($_POST['edit'])) {
    $dbh = new PDO($db);
    $stmt = $dbh->prepare('SELECT emailSMS FROM carriers WHERE carrier=?');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($carrierPost));
    $row = $stmt->fetch();
    $dbh = null;
    extract($row);
    $carrierEdit = $carrierPost;
    $emailSMSEdit = $emailSMS;
}
//
// HTML
//
require 'z/includes/header1.inc';
echo '  <title>Carrier maintenance</title>' . "\n";
require 'z/includes/header2.inc';
require 'z/includes/body.inc';
?>

  <h4><a class="m" href="message.php">&nbsp;Message&nbsp;</a><a class="m" href="groups.php">&nbsp;Groups&nbsp;</a><a class="m" href="users.php">&nbsp;Users&nbsp;</a><a class="m" href="recipients.php">&nbsp;Recipients&nbsp;</a><a class="s" href="carriers.php">&nbsp;Carriers&nbsp;</a></h4>
<?php echoIfMessage($message); ?>

  <h1><span class="r">Carriers</span></h1>

<?php
$rowcount = null;
$dbh = new PDO($db);
$stmt = $dbh->query('SELECT carrier, emailSMS FROM carriers ORDER BY carrier');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
foreach ($stmt as $row) {
    extract($row);
    $rowcount++;
    echo '  <form action="' . $uri . 'carriers.php" method="post">' . "\n";
    echo '    <p><span class="rp">' . html($carrier) . " - Carrier, count: $rowcount<br />\n";
    echo '    ' . html($emailSMS) . " - Address<br />\n";
    echo '    <input name="carrier" type="hidden" value="' . html($carrier) . '" /><input type="submit" value="Edit" name="edit" class="button" /></span></p>' . "\n";
    echo "  </form>\n\n";
}
$dbh = null;
?>
  <h1>Carrier maintenance</h1>

  <form action="<?php echo $uri; ?>carriers.php" method="post">
    <p>Your password is required for all carrier maintenance.</p>

    <p>Password<br />
    <input name="adminPass" type="password" autofocus="autofocus" required="required" /></p>

    <h1>Add, update and delete carriers</h1>

    <p>Both fields are required for add and update. The carrier name only is required for delete. Carrier names must be unique.</p>

    <p>Carrier name<br />
    <input name="carrier" type="text" required="required"<?php echoIfValue($carrierEdit); ?> /></p>

    <p>E-mail to SMS address<br />
    <input name="emailSMS" type="text"<?php echoIfValue($emailSMSEdit); ?> /></p>

    <p><input type="submit" value="Add" name="insert" class="left" /><input type="submit" value="Update" name="update" class="middle" /><input type="submit" value="Delete" name="delete" class="right" /></p>
  </form>
</body>
</html>