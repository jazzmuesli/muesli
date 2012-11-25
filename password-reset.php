<?php
require 'includes/common.php';

if (!empty($_POST['password'])) {
	$success = $um->passwordChange($_POST['email'], $_POST['token'], $_POST['password']);
	if ($success) {
		$_SESSION['message'] = 'Password changed successfully. Please log in.';
		$_SESSION['email'] = '';
		redirect('login.php');
	} else {
		$_SESSION['message'] = 'E-mail or token is invalid';
		redirect('password-reset.php');
	}
} else if (!empty($_POST['email']) && empty($_POST['token'])) {
	$success = $um->passwordReset($_POST['email']);
	if ($success) {
		$_SESSION['message'] = 'E-mail sent, follow the link or enter the token here';
		redirect('password-reset.php?email=' . $_POST['email']);
	} else {
		$_SESSION['message'] = 'Account not found';
		redirect('password-reset.php');
	}
} else {
	$template = new Template('templates/password-reset.php');
	$vars = array(	'message' => $_SESSION['message'], 
			'token' => $_GET['token'], 
			'email' => $_GET['email'], 
			'full' => $_GET['email'] ? TRUE : FALSE);
	$template->display($vars);
}
?>
