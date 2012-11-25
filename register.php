<?php
require 'includes/common.php';
if (!empty($_POST['email'])) {
	try {
		$success = $um->register($_POST['email'], $_POST['password']);
	} catch (Exception $e) {
		$_SESSION['message'] = $e->getMessage();
		redirect('register.php');
	}
	if ($success) {
		$_SESSION['message'] = 'Registered successfully';
		$_SESSION['email'] = $_POST['email'];
		redirect('index.php');
	} else {
		$_SESSION['message'] = 'Failed to register';
		redirect('register.php');
	}
} else {
	$template = new Template('templates/register.php');
	$template->display(array('message' => $_SESSION['message']));
}
?>
