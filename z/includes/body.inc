<body>
  <div class="h">
    <form class="admin" action="logout.php">
      <p class="admin"><input type="submit" class="button admin" value="Log out <?php echo html($_SESSION['userS']); ?>" /></p>
    </form>

    <div class="logo">
      <a href="<?php echo $uri; ?>"><img src="z/images/logo.png" alt="SMS Text Messager" width="296" height="55" /></a>
    </div>
  </div>