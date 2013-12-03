<?php
/**
 * index.php - Log in, when successful redirects to the appropriate page
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
session_start();
session_regenerate_id(true);
//
// Check for existing configuration file, create one if not found
//
if (file_exists('z/system/configuration.php')) {
    if (file_exists('z/system/configuration.inc')) {
        unlink('z/system/configuration.inc');
    }
} else {
    rename('z/system/configuration.inc', 'z/system/configuration.php');
}
require 'z/system/configuration.php';
$uri = $uriScheme . '://' . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . '/';
if (isset($_SESSION['auth']) or isset($_SERVER['HTTP_X_FORWARDED_FOR']) or isset($_SERVER['HTTP_X_FORWARDED']) or isset($_SERVER['HTTP_FORWARDED_FOR']) or in_array($_SERVER['REMOTE_PORT'], array(8080, 80, 6588, 8000, 3128, 553, 554))) {
    $uri = $uriScheme . '://' . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['PHP_SELF']), "/\\") . '/';
    header('Location: ' . $uri . 'logout.php');
}
require $includesPath . '/common.php';
require $includesPath . '/password_compat/password.php';
//
// Variables
//
$message = null;
$passPost = inlinePost('pass');
$userPost = inlinePost('user');
//
// Programs
//
$dbh = new PDO($db);
$dbh->beginTransaction();
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "carriers" ("idCarrier" INTEGER PRIMARY KEY, "carrier", "emailSMS")');
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "groups" ("idGroup" INTEGER PRIMARY KEY, "groupName" NOT NULL)');
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "send" ("idSend" INTEGER PRIMARY KEY, "idCarrier" INTEGER, "idGroup" INTEGER, "idUser" INTEGER, "address")');
$stmt = $dbh->query('CREATE TABLE IF NOT EXISTS "usersRecipients" ("idUser" INTEGER PRIMARY KEY, "user", "pass", "fullName", "userStatus" INTEGER, "phone", "idCarrier" INTEGER, "email")');
$dbh->commit();
$stmt = $dbh->query('SELECT count(*) FROM usersRecipients');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$row = $stmt->fetch();
if ($row['count(*)'] < 1) {
    $stmt = $dbh->prepare('INSERT INTO usersRecipients (user, pass, fullName, userStatus) VALUES (?, ?, ?, ?)');
    $stmt->execute(array('setup', password_hash('setup', PASSWORD_DEFAULT), 'Setup user (temporary)', 1));
}
$dbh = null;
if (isset($_POST['user'], $_POST['pass'])) {
    $userPost = secure($_POST['user']);
    //
    // Allow five failed log ins per hour
    //
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
    $dbh = new PDO($db);
    $stmt = $dbh->prepare('SELECT idUser, user, pass, fullName FROM usersRecipients WHERE user=? LIMIT 1');
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array($userPost));
    $row = $stmt->fetch();
    $dbh = null;
    if (password_verify($passPost, $row['pass'])) {
        if (password_needs_rehash($row['pass'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($passPost, PASSWORD_DEFAULT);
            $dbh = new PDO($db);
            $stmt = $dbh->prepare('UPDATE usersRecipients SET pass=? WHERE idUser=?');
            $stmt->execute(array($newHash, $row['idUser']));
            $dbh = null;
        }
        $dbh = new PDO($dbl);
        $stmt = $dbh->prepare('UPDATE login SET time=? WHERE user=?');
        $stmt->execute(array(null, $userPost));
        $dbh = null;
        $_SESSION['auth'] = hash('sha256', $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']) . hash('sha512', $row['user'] . $row['idUser']);
        $_SESSION['userID'] = hash('sha512', $row['user'] . $row['idUser']);
        $_SESSION['userId'] = $row['idUser'];
        $_SESSION['username'] = $row['user'];
        include 'z/includes/logNotice.php';
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
    <input id="user" name="user" type="text" maxlength="254" autofocus /></p>

    <p>Password<br />
    <input name="pass" type="password" maxlength="254" /></p>

    <p><input type="submit" class="button" value="Log in" name="login" /></p>
  </form>

  <p><a href="http://smstextmessager.com/">SMS Text Messager Free Open Source Software</a></p>
</body>
</html>
