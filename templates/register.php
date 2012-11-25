<?
 $title='Registration';
 require 'header.php';
?>
<form action="register.php" method="post">
E-mail: <input type="text" name="email" /><br />
Password: <input type="password" name="password" /></br />
<input type="submit" />
</form>
<a href="login.php">Log in here</a>
<? require 'footer.php';?>
