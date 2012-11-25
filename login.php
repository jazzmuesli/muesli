<?php
require 'includes/common.php';
if (!empty($_POST['email'])) {
	try {
		$success = $um->login($_POST['email'], $_POST['password']);
	} catch (Exception $e) {
		$_SESSION['message'] = $e->getMessage();
		redirect('login.php');
	}
	if ($success) {
		$_SESSION['message'] = 'Logged in successfully';
		$_SESSION['email'] = $_POST['email'];
		redirect('index.php');
	} else {
		$_SESSION['message'] = 'Authentication failed';
		redirect('login.php');
	}
} else {
	$template = new Template('templates/login.php');
	$template->display(array('message' => $_SESSION['message']));
}
?>
