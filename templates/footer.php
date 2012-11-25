<p>Page generated in <?=sprintf('%.2f seconds', mtime() - $GLOBALS['start_time']);?>. Users logged in the past 60 seconds: <?=$GLOBALS['last_minute_users'];?></p>
</body>
</html>
<? 
// Displayed message once, enough.
$_SESSION['message'] = ''; 
?>
