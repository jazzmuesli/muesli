<?php
require 'Template.php';
class InvalidEmailException extends Exception {}
class InvalidPasswordException extends Exception {}
/**
* Class responsible for register-login-password-reset functionality.
*/
class UserManager {
	function __construct($dbh) {
		$this->dbh = $dbh;
	}

	/**
	* Re-create table(s).
	*/
	function recreateTables() {
		$sql = <<<EOF
drop table if exists user;
create table user (
	email varchar(200), 
	pwd varchar(200), 
	last_login bigint, 
	reset_token varchar(200),
	primary key (email)
);
EOF;
		$this->dbh->exec($sql);
	}

	/**
	* How many users logged in last minute?
	*/
	function userCount($ago) {
		$sth = $this->dbh->prepare('select count(email) as c from user where dsds last_login >= :t');
		$sth->bindValue('t', time() - $ago);
		$sth->execute();
		if ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			return $row['c'];
		}
		return null;
	}

	/**
	* Bind password and e-mail in one place. 
	* In case you decide to replace md5 with another hashing algorithm.
	*/
	private function bindPasswordAndExecute($stmt, $email, $pwd, $time=0) {
		if (!$stmt) {
			throw new Exception('no statement provided');
		}
		if (strpos($email, '@') === false) {
			throw new InvalidEmailException("E-mail $email is invalid");
		}
		if (strlen($pwd) < 1) {
			throw new InvalidPasswordException('Password is too short');
		}
                $stmt->bindValue(':e', $email);
                $stmt->bindValue(':p', md5($pwd));
		if ($time > 0) {
			$stmt->bindValue(':t', $time);
		}
                return $stmt->execute();
	}

	/**
	* register a user. Thanks to primary token on email column you get 
	* a constraint violation if the user already exists. It is atomic in contrast to select+insert.
	*/
	function register($email, $pwd) {
                $stmt = $this->dbh->prepare('insert into user (email,pwd) values(:e, :p)');
		try {
 	               $result = $this->bindPasswordAndExecute($stmt, $email, $pwd);
		} catch (PDOException $e) {
			if (strpos($e->getMessage(), 'constraint violation') !== false) {
				return FALSE;
			}
			throw $e;
		}
		if ($result) {
			return $this->emailRegister($email, $pwd);
		}
                return FALSE;
        }

	/**
	* Send e-mail after registration. Override if you want to test something else.
	*/
	protected function emailRegister($email, $pwd) {
		$vars = array('email' => $email, 'password' => $pwd);
		$template = new Template("templates/register.email.php");
		return $template->mail($vars, $email, 'Registration successful');
	}

	/**
	* Log in the user, set his last_login time and reset the token.
	*/
	function login($email, $pwd) {
		$sth = $this->dbh->prepare('update user set last_login=:t, reset_token=null where email=:e and pwd=:p');
		$result = $this->bindPasswordAndExecute($sth, $email, $pwd, time());
                if (!$result) {
                        return false;
                }
                return $sth->rowCount() > 0;
	}


	/**
	* Knowing the reset-token, change the password.
	*/
	function passwordChange($email, $token, $password) {
		$sth = $this->dbh->prepare('update user set pwd=:p, reset_token=null where email=:e and reset_token=:k');
		$sth->bindValue('k', $token);
		$success = $this->bindPasswordAndExecute($sth, $email, $password);
		if ($success && $sth->rowCount() == 1) {
			return $this->emailPasswordChanged($email, $password);
		}
		return FALSE;
	}

	protected function emailPasswordChanged($email, $password) {
	        $vars = array('email' => $email, 'password' => $password);
                $template = new Template('templates/password-changed.email.php');
                return $template->mail($vars, $email, 'Your password has been changed');
	}

	/**
	* Generate a reset-token and send it via e-mail.
	*/
	function passwordReset($email) {
		$token = md5($email . mt_rand(0, time()));
		$sth = $this->dbh->prepare('update user set reset_token=:k where email=:e');
		if (!$sth) {
			throw new Exception('No statement created');
		}
		$sth->bindValue('k', $token);
		$sth->bindValue('e', $email);
		$sth->execute();
		$success = $sth->rowCount() == 1;
		if ($success) {
			return $this->emailPasswordReset($email, $token);
		}
		return FALSE;
	}

	protected function emailPasswordReset($email, $token) {
                $vars = array('email' => $email, 'token' => $token);
                $template = new Template('templates/password-reset.email.php');
                return $template->mail($vars, $email, "Password reset instructions");
   
	}
}
?>
