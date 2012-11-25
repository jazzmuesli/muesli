<?php
require 'includes/common.php';
$_SESSION['message'] = 'Logged out successfully';
$_SESSION['email'] = '';
redirect('index.php');
?>
