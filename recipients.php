<?php
/**
 * recipients.php - Recipient maintenance
 *
 * PHP version 5
 *
 * @category  Messaging
 * @package   SMS-Text-Messager
 * @author    Hardcover Web Design LLC <useTheContactForm@hardcoverwebdesign.com>
 * @copyright 2012 Hardcover Web Design LLC
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 *.@license   http://www.gnu.org/licenses/gpl-2.0.txt  GNU General Public License, Version 2
 * @version   GIT: 2012-11-17 database B
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
if (isset($_POST['adminPass']) and ($_POST['adminPass'] == null or $_POST['adminPass'] == '')) {
    $message = 'Your password is required for all recipient maintenance.';
}
//
// Prepare post data
//
$adminPassPost = isset($_POST['adminPass']) ? secure($_POST['adminPass']) : null;
$fullNamePost = isset($_POST['fullName']) ? secure($_POST['fullName']) : null;
$phonePost = isset($_POST['phone']) ? secure($_POST['phone']) : null;
$phonePost = preg_replace("/\D/", "", $phonePost);
$phonePost = $phonePost == '' ? null : $phonePost;
$idCarrier = isset($_POST['idCarrier']) ? $_POST['idCarrier'] : null;
$idCarrier = $idCarrier == '' ? null : $idCarrier;
$emailPost = isset($_POST['email']) ? secure($_POST['email']) : null;
$emailPost = $emailPost == '' ? null : $emailPost;
$emailPost = ($phonePost == null and $idCarrier == null) ? $emailPost : null;
$group = isset($_POST['group']) ? $_POST['group'] : null;
$address = null;
if ($phonePost != null and $idCarrier != null) {
    $dbh = new PDO($db);
    $stmt = $dbh->prepare('SELECT emailSMS FROM carriers WHERE idCarrier=?');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($idCarrier));
    $row = $stmt->fetch();
    $dbh = null;
    extract($row);
    $address = $phonePost . $emailSMS;
} elseif ($emailPost != null) {
    $address = $emailPost;
}
$fullNameEdit = false;
$phoneEdit = false;
$idCarrierEdit = null;
$emailEdit = false;
$idGroupEdit = null;
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
    // Buttons, insert, update and delete
    //
    if (isset($_POST['insert'])) {
        if ($_POST['fullName'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT fullName FROM usersRecipients WHERE fullName=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($fullNamePost));
            $row = $stmt->fetch();
            $dbh = null;
            if (isset($row['fullName'])) {
                header('Location: ' . $uri . 'recipients.php');
                exit;
            } else {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('INSERT INTO usersRecipients (fullName, phone, idCarrier, email) VALUES (?, ?, ?, ?)');
                $stmt->execute(array($fullNamePost, muddle($phonePost), $idCarrier, muddle($emailPost)));
                $stmt = $dbh->prepare('SELECT idUser FROM usersRecipients WHERE fullName=?');
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute(array($fullNamePost));
                $row = $stmt->fetch();
                extract($row);
                if (isset($_POST['group'])) {
                    foreach ($_POST['group'] as $group) {
                        $stmt = $dbh->prepare('INSERT INTO send (idCarrier, idGroup, idUser, address) VALUES (?, ?, ?, ?)');
                        $stmt->execute(array($idCarrier, $group, $idUser, muddle($address)));
                    }
                }
                $dbh = null;
                include 'z/includes/backUp.php';
            }
        } else {
            $message = 'No full name was input.';
        }
    }
    if (isset($_POST['update'])) {
        if ($_POST['fullName'] != null) {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT fullName FROM usersRecipients WHERE fullName=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($fullNamePost));
            $row = $stmt->fetch();
            $dbh = null;
            if (isset($row['fullName'])) {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('UPDATE usersRecipients SET phone=?, idCarrier=?, email=? WHERE fullName=?');
                $stmt->execute(array(muddle($phonePost), $idCarrier, muddle($emailPost), $fullNamePost));
                $stmt = $dbh->prepare('SELECT idUser FROM usersRecipients WHERE fullName=?');
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute(array($fullNamePost));
                $row = $stmt->fetch();
                extract($row);
                $stmt = $dbh->prepare('DELETE FROM send WHERE idUser=?');
                $stmt->execute(array($idUser));
                if (isset($_POST['group'])) {
                    foreach ($_POST['group'] as $group) {
                        $stmt = $dbh->prepare('INSERT INTO send (idCarrier, idGroup, idUser, address) VALUES (?, ?, ?, ?)');
                        $stmt->execute(array($idCarrier, $group, $idUser, muddle($address)));
                    }
                }
                $dbh = null;
                include 'z/includes/backUp.php';
            } else {
                $message = 'Full name did not match an existing entry.';
            }
        } else {
            $message = 'No full name was input.';
        }
    }
    if (isset($_POST['delete'])) {
        if ($_POST['fullName'] != null and $_POST['fullName'] != 'Administrator') {
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('SELECT fullName FROM usersRecipients WHERE fullName=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($fullNamePost));
            $row = $stmt->fetch();
            $dbh = null;
            if (isset($row['fullName'])) {
                $dbh = new PDO($db);
                $stmt = $dbh->prepare('SELECT idUser FROM usersRecipients WHERE fullName=?');
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->execute(array($fullNamePost));
                $row = $stmt->fetch();
                extract($row);
                $stmt = $dbh->prepare('DELETE FROM usersRecipients WHERE idUser=?');
                $stmt->execute(array($idUser));
                $stmt = $dbh->prepare('DELETE FROM send WHERE idUser=?');
                $stmt->execute(array($idUser));
                $stmt = $dbh->query('VACUUM');
                $dbh = null;
                include 'z/includes/backUp.php';
            } else {
                $message = 'Full name did not match an existing entry.';
            }
        } else {
            $message = 'No full name was input.';
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
    $stmt = $dbh->prepare('SELECT idUser, phone, idCarrier, email FROM usersRecipients WHERE fullName=?');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($fullNamePost));
    $row = $stmt->fetch();
    extract($row);
    $fullNameEdit = $fullNamePost;
    $phoneEdit = $phone;
    $idCarrierEdit = $idCarrier;
    $emailEdit = $email;
    $stmt = $dbh->prepare('SELECT idGroup FROM send WHERE idUser=?');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($idUser));
    foreach ($stmt as $row) {
        $idGroupEdit[].= $row['idGroup'];
    }
    $dbh = null;
}
//
// HTML
//
require 'z/includes/header1.inc';
echo '  <title>Recipient maintenance</title>' . "\n";
echo '  <script type="text/javascript" src="z/scroll.js"></script>' . "\n";
require 'z/includes/header2.inc';
require 'z/includes/body.inc';
?>

  <h4><a class="m" href="message.php">&nbsp;Message&nbsp;</a><a class="m" href="groups.php">&nbsp;Groups&nbsp;</a><a class="m" href="users.php">&nbsp;Users&nbsp;</a><a class="s" href="recipients.php">&nbsp;Recipients&nbsp;</a><a class="m" href="carriers.php">&nbsp;Carriers&nbsp;</a></h4>
<?php echoIfMessage($message); ?>

  <h1><span class="r">Recipients</span></h1>

<?php
$rowcount = null;
$dbh = new PDO($db);
$stmt = $dbh->prepare('SELECT idUser, user, fullName, phone, idCarrier, email FROM usersRecipients ORDER BY fullName');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
foreach ($stmt as $row) {
    extract($row);
    if ($user != 'admin') {
        $rowcount++;
        if ($idCarrier != null) {
            $stmt = $dbh->prepare('SELECT carrier FROM carriers WHERE idCarrier=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($idCarrier));
            $row = $stmt->fetch();
            extract($row);
        } else {
            $carrier = null;
        }
        echo '  <form action="' . $uri . 'recipients.php" method="post">' . "\n";
        echo '    <p><span class="rp">' . $fullName . " - Full name, count: $rowcount<br />\n";
        echo '    ' . plain($email) . " - E-mail<br />\n";
        echo '    ' . plain($phone) . " - Mobile phone number<br />\n";
        echo '    ' . html($carrier) . " - Mobile phone carrier<br />\n";
        $stmt = $dbh->prepare('SELECT idGroup FROM send WHERE idUser=?');
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array($idUser));
        foreach ($stmt as $row) {
            $stmt = $dbh->prepare('SELECT groupName FROM groups WHERE idGroup=?');
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array($row['idGroup']));
            $row = $stmt->fetch();
            if (isset($row['groupName'])) {
                echo '    ' . $row['groupName'] . " - Group<br />\n";
            }
        }
        echo '    <input name="fullName" type="hidden" value="' . $fullName . '" /><input type="submit" value="Edit" name="edit" class="button" /></span></p>' . "\n";
        echo "  </form>\n\n";
    }
}
$dbh = null;
?>
  <h1>Recipient maintenance</h1>

  <p>Users are also recipients and their group affiliations are set here. Recipients who are not users do not have log ins. Recipients must have either 1) an e-mail address, or 2) both a mobile phone number and mobile phone carrier in order to receive messages. Your password is required for all recipient maintenance.</p>

  <form action="<?php echo $uri; ?>recipients.php" method="post">
    <p>Password<br />
    <input name="adminPass" type="password" autofocus="autofocus" required="required" /></p>

    <h1>Add, update and delete recipients</h1>

    <p>Full name is required for add, update and delete. Full names must be unique.</p>

    <p>Full name<br />
    <input name="fullName" type="text" required="required"<?php echoIfValue($fullNameEdit); ?> /></p>

    <p>Enter either, 1) an e-mail address, or 2) a mobile phone number and select the mobile phone carrier. When both 1 and 2 are filled in, the mobile phone information, 2, is given priority and the e-mail address, 1, is not recorded. To enter multiple receiving locations for a single person, enter the person multiple times with unique variations of their full name.</p>

    <p>1) E-mail address, or<br />
    <input name="email" type="email"<?php echoIfValue(plain($emailEdit)); ?> /></p>

    <p>2) Mobile phone number and mobile phone carrier<br />
    <input name="phone" type="number"<?php echoIfValue(plain($phoneEdit)); ?> /></p>

<?php
$dbh = new PDO($db);
$stmt = $dbh->query('SELECT idCarrier, carrier FROM carriers ORDER BY carrier');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
foreach ($stmt as $row) {
    extract($row);
    $checked = $idCarrier == $idCarrierEdit ? ' checked="checked"' : null;
    echo '    <p class="b"><input name="idCarrier" type="radio" value="' . $idCarrier . '"' . $checked . ' /> ' . html($carrier) . "</p>\n\n";
}
echo "    <p>Select the appropriate group(s) for the recipient. Unless the intent is to remove the recipient from all groups, then select the appropriate groups.</p>\n\n";
$stmt = $dbh->prepare('SELECT idGroup, groupName FROM groups ORDER BY groupName');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute();
foreach ($stmt as $row) {
    extract($row);
    $rowcount++;
    $checked = null;
    if (isset($idGroupEdit)) {
        foreach ($idGroupEdit as $maybe) {
            if ($maybe == $idGroup) {
                $checked = ' checked="checked"';
            }
        }
    }
    echo '    <p class="b"><input name="group[]" type="checkbox" value="' . $idGroup . '"' . $checked . ' /> ' . html($groupName) . "</p>\n\n";
}
$dbh = null;
?>
    <p><input type="submit" value="Add" name="insert" class="left" /><input type="submit" value="Update" name="update" class="middle" /><input type="submit" value="Delete" name="delete" class="right" /></p>
  </form>
</body>
</html>
