<?php
require '../includes/UserManager.php';
class MailLessUserManager extends UserManager {
	var $emails = array();
	function emailRegister($email, $pwd) {
		$this->emails[] = array('type' => __METHOD__, 'email' => $email, 'password' => $pwd);
		return TRUE;
	}

	function emailPasswordReset($email, $token) {
		$this->emails[] = array('type' => __METHOD__, 'email' => $email, 'token' => $token);
		return TRUE;
	}

	function emailPasswordChanged($email, $password) {
		$this->emails[] = array('type' => __METHOD__, 'email' => $email, 'password' => $password);
		return TRUE;
	}
}

class UserManagerTest extends PHPUnit_Framework_TestCase
{

	protected $um;
	/**
	* prepare UserManager before every test.
	*/
	protected function setUp() {
		$this->um = $this->prepareUM();
	}

	/**
	* Use our MailLessMockManager and in-memory SQLite.
	*/
	private function prepareUM()  {
		$dsn = 'sqlite::memory:';
		$db = new PDO($dsn);
		$um = new MailLessUserManager($db);
		$um->recreateTables();
		return $um;
	}

	public function testGoodRegister() {
		$this->assertNotNull($this->um);
		$this->assertEquals(0, count($this->um->emails));
		$this->um->register(mt_rand(0,time()) . '@localhost', 'pwd-' . time());
		$this->assertEquals(1, count($this->um->emails));
 	}

	public function testDuplicateRegister() {
		$this->assertTrue($this->um->register('user@localhost', 'pwd'));
		$this->assertFalse($this->um->register('user@localhost', 'doors'));
	}

	/**
	@expectedException InvalidEmailException
	*/
	public function testInvalidLogin() {
		$this->um->register('invalid', 'invalid');
	}

	/**
	@expectedException InvalidPasswordException
	*/
	public function testInvalidPassword() {
		$this->um->login('normal@host', '');
	}

	public function testGoodLogin() {
		$email = 'test@localhost';
		$pwd = 'password';
		$this->assertTrue($this->um->register($email, $pwd));
		$this->assertTrue($this->um->login($email, $pwd));
	}

	public function testWrongLogin() {
		$this->assertFalse($this->um->login('user@localhost', 'beatles'));
	}

	public function testPasswordReset() {
		$this->passwordReset('user@localhost','first');
	}
	public function testPasswordResetUnknownEmail() {
		$this->assertFalse($this->um->passwordReset('unknown@localhost'));
		$this->assertEquals(0, count($this->um->emails));
	}

	public function passwordReset($email, $pwd) {
		$this->assertTrue($this->um->register($email, $pwd));
		$this->assertTrue($this->um->login($email, $pwd));
		$this->assertTrue($this->um->passwordReset($email));
		$this->assertEquals(2, count($this->um->emails));
		$token = $this->um->emails[1]['token'];
		$this->assertNotEmpty($token);
		return $token;
	}

	public function testPasswordChangeWrongToken() {
		$email = 'user@localhost';
		$pwd = 'first';
		$token = $this->passwordReset($email,$pwd);
		$pwd = 'second';
		$this->assertFalse($this->um->passwordChange($email, 'wrongToken', $pwd));
		$this->assertFalse($this->um->login($email, $pwd));
	}

	public function testPasswordChangeGoodLoginInTheMiddle() {
		$email = 'user@localhost';
		$pwd = 'first';
		$token = $this->passwordReset($email, $pwd);
		$this->assertTrue($this->um->login($email, $pwd));
		$pwd = 'second';
		$this->assertFalse($this->um->passwordChange($email, $token, $pwd));
	}

	public function testPasswordChangeOK() {
		$email = 'user@localhost';
		$pwd = 'first';
		$token = $this->passwordReset($email, $pwd);
		$this->assertTrue($this->um->passwordChange($email, $token, $pwd));
		$this->assertEquals(3, count($this->um->emails));
		$this->assertEquals($pwd, end($this->um->emails)['password']);
		$this->assertTrue($this->um->login($email, $pwd));
	}

}
?>
