<?php
/**
 * users.php - User maintenance
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
require $includesPath . '/password_compat/password.php';
//
// Variables
//
$adminPassPost = inlinePost('adminPass');
$fullNameEdit = null;
$fullNamePost = inlinePost('fullName');
$hash = null;
$message = null;
$passPost = inlinePost('pass');
$userEdit = null;
$userPost = inlinePost('user');
if (isset($_POST['adminPass']) and ($_POST['adminPass'] == null or $_POST['adminPass'] == '')) {
    $message = 'Your password is required for all user maintenance.';
}
if ($passPost != null) {
    $hash = password_hash($passPost, PASSWORD_DEFAULT);
}
//
// Test password authentication
//
$dbh = new PDO($db);
$stmt = $dbh->prepare('SELECT pass FROM usersRecipients WHERE user=?');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute(array($_SESSION['username']));
$row = $stmt->fetch();
$dbh = null;
if (strval(crypt($adminPassPost, $row['pass'])) === strval($row['pass'])) {
    //
    // Buttons, insert, update, delete
    //
    if (isset($_POST['insert'])) {
        if ($_POST['user'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT user FROM usersRecipients WHERE user=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($userPost));
            $row = $stmt->fetch();
            $dbh = null;
            if (isset($row['user'])) {
                header('Location: ' . $uri . 'users.php');
                exit;
            } else {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('INSERT INTO usersRecipients (user, pass, fullName, userStatus) VALUES (?, ?, ?, ?)');
                $stmt->execute(array($userPost, $hash, $fullNamePost, 1));
                $dbh = null;
                mailAttachments($_SESSION['username'], $emailTo, $emailFrom, array($includesPath . '/databases/sms.sqlite', 'z/system/configuration.php'));
            }
        } else {
            $message = 'No user name was input.';
        }
    }
    if (isset($_POST['update'])) {
        if ($_POST['user'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT user FROM usersRecipients WHERE user=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($userPost));
            $row = $stmt->fetch();
            $dbh = null;
            if (isset($row['user'])) {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('UPDATE usersRecipients SET pass=?, fullName=?, userStatus=? WHERE user=?');
                $stmt->execute(array($hash, $fullNamePost, 1, $userPost));
                $dbh = null;
                mailAttachments($_SESSION['username'], $emailTo, $emailFrom, array($includesPath . '/databases/sms.sqlite', 'z/system/configuration.php'));
            } else {
                $message = 'The user name was not found.';
            }
        } else {
            $message = 'No user name was input.';
        }
    }
    if (isset($_POST['delete'])) {
        if ($userPost != "admin") {
            if ($_POST['user'] != null) {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('SELECT user FROM usersRecipients WHERE user=?');
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute(array($userPost));
                $row = $stmt->fetch();
                $dbh = null;
                if (isset($row['user'])) {
                    $dbh = new PDO($db);
                    $stmt = $dbh->prepare('SELECT idUser FROM usersRecipients WHERE user=?');
                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                    $stmt->execute(array($userPost));
                    $row = $stmt->fetch();
                    extract($row);
                    $stmt = $dbh->prepare('DELETE FROM usersRecipients WHERE idUser=?');
                    $stmt->execute(array($idUser));
                    $stmt = $dbh->prepare('DELETE FROM send WHERE idUser=?');
                    $stmt->execute(array($idUser));
                    $stmt = $dbh->query('VACUUM');
                    $dbh = null;
                    mailAttachments($_SESSION['username'], $emailTo, $emailFrom, array($includesPath . '/databases/sms.sqlite', 'z/system/configuration.php'));
                } else {
                    $message = 'The user name was not found.';
                }
            } else {
                $message = 'No user name was input.';
            }
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
    $stmt = $dbh->prepare('SELECT fullName FROM usersRecipients WHERE user=?');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($userPost));
    $row = $stmt->fetch();
    $dbh = null;
    extract($row);
    $userEdit = $userPost;
    $fullNameEdit = $fullName;
    $message = 'Please note the password was not retrieved and will have to be entered again for update.';
}
//
// HTML
//
require 'z/includes/header1.inc';
echo '  <title>User maintenance</title>' . "\n";
echo '  <script type="text/javascript" src="z/focus.js"></script>' . "\n";
require 'z/includes/header2.inc';
require 'z/includes/body.inc';
?>

  <h4 class="m"><a class="m" href="message.php">&nbsp;Message&nbsp;</a><a class="m" href="groups.php">&nbsp;Groups&nbsp;</a><a class="s" href="users.php">&nbsp;Users&nbsp;</a><a class="m" href="recipients.php">&nbsp;Recipients&nbsp;</a><a class="m" href="carriers.php">&nbsp;Carriers&nbsp;</a></h4>
<?php echoIfMessage($message); ?>

  <h1><span class="r">Users</span></h1>

<?php
$dbh = new PDO($db);
$rowcount = null;
$stmt = $dbh->query('SELECT user, pass, fullName FROM usersRecipients WHERE userStatus = 1 ORDER BY fullName');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
foreach ($stmt as $row) {
    extract($row);
    if (empty($pass)) {
        $printPass = '<b>NOT SET!</b>';
    } else {
        $printPass = 'set.';
    }
    if ($user != "admin") {
        $rowcount++;
        echo '  <form action="' . $uri . 'users.php" method="post">' . "\n";
        echo '    <p><span class="rp">' . html($fullName) . " - Full name<br />\n";
        echo '    ' . html($user) . " - User name, count: $rowcount<br />\n";
        echo "    The password is $printPass<br />\n";
        echo '    <input name="user" type="hidden" value="' . html($user) . '" /><input type="submit" value="Edit" name="edit" class="button" /></span></p>' . "\n";
        echo "  </form>\n\n";
    }
}
$dbh = null;
?>
  <h1>User maintenance</h1>

  <form action="<?php echo $uri; ?>users.php" method="post">
    <p>Users have log ins to send messages. Maintain recipient information for users on the recipient page. Your password is required for all user maintenance.</p>

    <p>Password<br />
    <input id="adminPass" name="adminPass" type="password" required="required" /></p>

    <h1>Add, update and delete users</h1>

    <p>All fields are required for add and update. The user name only is required for delete. User names must be unique. To change a user name, first delete the user, then create a new user.</p>

    <p>Full name<br />
    <input name="fullName" type="text"<?php echoIfValue($fullNameEdit); ?> /></p>

    <p>User name<br />
    <input name="user" type="text" required="required"<?php echoIfValue($userEdit); ?> /></p>

    <p>Password<br />
    <input name="pass" type="text" /></p>

    <p><input type="submit" value="Add" name="insert" class="left" /><input type="submit" value="Update" name="update" class="middle" /><input type="submit" value="Delete" name="delete" class="right" /></p>
  </form>
</body>
</html>
