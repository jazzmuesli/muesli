<? 
$title = 'Password restore';
require 'header.php'; ?>
<form action="password-reset.php" method="post">
E-mail: <input type="text" name="email" value="<?=$email;?>" /><br />
<? if ($full) {?>
Password reset token: <input type="text" name="token" value="<?=$token;?>" /><br />
New password: <input type="password" name="password" /><br />
<? } ?>
<input type="submit" />
</form>
<a href="index.php">Home</a>
<? require 'footer.php'; ?>
