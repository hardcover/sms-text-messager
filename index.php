<?php
/**
 * index.php - Log in, when successful redirects to the appropriate page
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
session_start();
require 'z/includes/INPUTS.php';
$uri = $uriScheme . '://' . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . '/';
if (isset($_SESSION['auth']) or isset($_SERVER['HTTP_X_FORWARDED_FOR']) or isset($_SERVER['HTTP_X_FORWARDED']) or isset($_SERVER['HTTP_FORWARDED_FOR']) or in_array($_SERVER['REMOTE_PORT'], array(8080, 80, 6588, 8000, 3128, 553, 554))) {
    require 'z/includes/INPUTS.php';
    $uri = $uriScheme . '://' . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . '/';
    header('Location: ' . $uri . 'logout.php');
}
//
// Programs
//
require 'z/includes/functions.inc';
$message = false;
require 'z/includes/db.php';
$dbh = new PDO($db);
$dbh->beginTransaction();
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "carriers" ("idCarrier" INTEGER PRIMARY KEY, "carrier", "emailSMS")');
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "groups" ("idGroup" INTEGER PRIMARY KEY, "groupName" NOT NULL)');
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "send" ("idSend" INTEGER PRIMARY KEY, "idCarrierInSend" INTEGER, "idGroupInSend" INTEGER, "idUserInSend" INTEGER, "address")');
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "usersRecipients" ("idUser" INTEGER PRIMARY KEY, "user", "pass", "fullName", "userStatus" INTEGER, "phone", "idCarrierInUser" INTEGER, "email")');
$dbh->commit();
$stmt = $dbh->query('SELECT count(*) FROM usersRecipients');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$row = $stmt->fetch();
if ($row['count(*)'] < 1) {
    $stmt = $dbh->prepare('INSERT INTO usersRecipients (user, pass, fullName, userStatus) VALUES (?, ?, ?, ?)');
    $stmt->execute(array('setup', 'eb0f5d35ef21aa7f1b3a67e31007ec0b34b8902e3f81e91dae641bc781a901d6478c0af977dde1ecf5270d9c88e26e735084c757242169d0176092dde4c7c90a', 'Setup user (temporary)', 1));
}
$dbh = null;
if (isset($_POST['user'], $_POST['pass'])) {
    $userPost = secure($_POST['user']);
    //
    // Allow five failed log ins per hour
    //
    date_default_timezone_set('America/Los_Angeles');
    $now = time();
    $lastHour = $now - (60 * 60);
    $legibleTime = date("l, F j, Y, H:i:s", $now);
    $dbh = new PDO($dbl);
    $stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "login" ("idUser" INTEGER PRIMARY KEY, "user", "legibleTime", ipAddress, "time" INTEGER)');
    $stmt = $dbh->prepare('INSERT INTO login (user, legibleTime, ipAddress, time) VALUES (?, ?, ?, ?)');
    $stmt->execute(array($userPost, $legibleTime, $_SERVER['REMOTE_ADDR'], $now));
    $stmt = $dbh->prepare('SELECT count(*) FROM login WHERE user=? AND time > ?');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($userPost, $lastHour));
    $row = $stmt->fetch();
    if ($row['count(*)'] > 5) {
        $dbh = null;
        include 'logout.php';
    }
    $dbh = null;
    //
    // Authenticate
    //
    $hash = hash('sha512', secure($_POST['pass']) . $userPost);
    $dbh = new PDO($db);
    $stmt = $dbh->prepare('SELECT idUser, user, pass, fullName FROM usersRecipients WHERE user=? LIMIT 1');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($userPost));
    $row = $stmt->fetch();
    $dbh = null;
    if (strval($hash) === strval($row['pass'])) {
        $dbh = new PDO($dbl);
        $stmt = $dbh->prepare('UPDATE login SET time=? WHERE user=?');
        $stmt->execute(array(null, $userPost));
        $dbh = null;
        $_SESSION['auth'] = $row['idUser'] . $row['user'] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['userIdS'] = $row['idUser'];
        $_SESSION['userS'] = $row['user'];
        header('Location: ' . $uri . 'message.php');
    } else {
        $message = 'Login credentials are incorrect.';
    }
}
//
// HTML
//
require 'z/includes/header1.inc';
echo '  <title>SMS text messager</title>' . "\n";
require 'z/includes/header2.inc';
?>
<body>
  <h1>SMS text messager log in</h1>
<?php echoIfMessage($message); ?>

  <form action="<?php echo $uri; ?>" method="post" id="login">

    <p>User<br />
    <input id="user" name="user" type="text" maxlength="254" autofocus="autofocus" /></p>

    <p>Password<br />
    <input name="pass" type="password" maxlength="254" /></p>

    <p><input type="submit" class="button" value="Log in" name="login" /></p>
  </form>

  <p><a href="http://smstextmessager.com/">SMS Text Messager Free Open Source Software</a></p>
</body>
</html>
