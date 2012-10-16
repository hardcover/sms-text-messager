<?php
/**
 * groups.php - Group maintenance
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-10-15 database A
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
//
// Prepare post data
//
if (isset($_POST['adminPass']) and ($_POST['adminPass'] == null or $_POST['adminPass'] == '')) {
    $message = 'Your password is required for all recipient maintenance.';
}
if (isset($_POST['groupName']) and ($_POST['groupName'] == null or $_POST['groupName'] == '')) {
    $message = 'No group name was input.';
}
//
// Prepare post data
//
$adminPassPost = isset($_POST['adminPass']) ? secure($_POST['adminPass']) : null;
$groupName = isset($_POST['groupName']) ? secure($_POST['groupName']) : null;
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
    // Buttons, insert and delete
    //
    if (isset($_POST['insert']) and $message == null) {
        $dbh = new PDO($db);
        $stmt = $dbh->prepare('SELECT groupName FROM groups WHERE groupName=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($groupName));
        $row = $stmt->fetch();
        $dbh = null;
        if (isset($row['groupName'])) {
            header('Location: ' . $uri . 'groups.php');
            exit;
        }
        $dbh = new PDO($db);
        $stmt = $dbh->prepare('INSERT INTO groups (groupName) VALUES (?)');
        $stmt->execute(array($groupName));
        $dbh = null;
        include 'z/includes/backUp.php';
    }
    if (isset($_POST['delete']) and $message == null) {
        $dbh = new PDO($db);
        $stmt = $dbh->prepare('SELECT idGroup, groupName FROM groups WHERE groupName=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($groupName));
        $row = $stmt->fetch();
        if (isset($row['groupName'])) {
            $stmt = $dbh->prepare('DELETE FROM groups WHERE idGroup=?');
            $stmt->execute(array($row['idGroup']));
            $stmt = $dbh->prepare('DELETE FROM send WHERE idGroupinSend=?');
            $stmt->execute(array($row['idGroup']));
        }
        $stmt = $dbh->query('VACUUM');
        $dbh = null;
        include 'z/includes/backUp.php';
    }
} elseif (isset($_POST['insert']) or isset($_POST['update']) or isset($_POST['delete'])) {
    $message = 'The password is invalid.';
}
//
// Button, edit
//
$groupNameEdit = isset($_POST['edit']) ? $groupName : false;
//
// HTML
//
require 'z/includes/header1.inc';
echo '  <title>Group maintenance</title>' . "\n";
require 'z/includes/header2.inc';
require 'z/includes/body.inc';
?>

  <h4><a class="m" href="message.php">&nbsp;Message&nbsp;</a><a class="s" href="groups.php">&nbsp;Groups&nbsp;</a><a class="m" href="users.php">&nbsp;Users&nbsp;</a><a class="m" href="recipients.php">&nbsp;Recipients&nbsp;</a><a class="m" href="carriers.php">&nbsp;Carriers&nbsp;</a></h4>
<?php echoIfMessage($message); ?>

  <h1><span class="r">Groups</span></h1>

<?php
$rowcount = null;
$dbh = new PDO($db);
$stmt = $dbh->prepare('SELECT groupName FROM groups ORDER BY groupName');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
foreach ($stmt as $row) {
    extract($row);
    $rowcount++;
    echo '  <form action="' . $uri . 'groups.php" method="post">' . "\n";
    echo "    <p><span class=\"rp\">$groupName - count: $rowcount<br />\n";
    echo '    <input name="groupName" type="hidden" value="' . html($groupName) . '" /><input type="submit" value="Edit" name="edit" class="button" /></span></p>' . "\n";
    echo "  </form>\n\n";
}
$dbh = null;
?>
  <h1>Group maintenance</h1>

  <p>Recipients must belong to a group in order to receive messages. Not even messages sent to, All, will be delivered unless the recipient belongs to a defined group. Your password is required for all group maintenance.</p>

  <form action="<?php echo $uri; ?>groups.php" method="post">
    <p>Password<br />
    <input name="adminPass" type="password" autofocus="autofocus" required="required" /></p>

    <h1>Add or delete groups</h1>

    <p>Group names must be unique.</p>

    <p>Group name<br />
    <input name="groupName" type="text" autofocus="autofocus" required="required"<?php echoIfValue($groupNameEdit); ?> /></p>

    <p><input type="submit" value="Add" name="insert" class="left" /><input type="submit" value="Delete" name="delete" class="right" /></p>
  </form>
</body>
</html>
