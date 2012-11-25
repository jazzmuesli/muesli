<? 
$title = 'Log in page';
require 'header.php'; 
?>
<form action="login.php" method="post">
E-mail: <input type="text" name="email" value="<?=$email;?>" /><br />
Password: <input type="password" name="password" /><br />
<input type="submit" />
</form>
<a href="password-reset.php">Forgot your password?</a>
<? require 'footer.php'; ?>
