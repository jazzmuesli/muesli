<?php
require 'includes/common.php';
if (!empty($_SESSION['email'])) {
	$template = new Template('templates/protected-home.php');
	$template->display(array('message' => $_SESSION['message'], 'email' => $_SESSION['email']));
} else {
	$template = new Template('templates/home.php');
	$template->display(array('message' => $_SESSION['message']));
}
?>
