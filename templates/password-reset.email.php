<? require 'header.email.php'; ?>
<p>
Now you can change your password.<br />

E-mail: <?=$email;?><br />
Password reset token: <?=$token;?><br />
<a href="<?=$baseurl;?>password-reset.php?email=<?=$email;?>&token=<?=$token;?>">Follow this link</a>
</p>
<? require 'footer.email.php'; ?>
