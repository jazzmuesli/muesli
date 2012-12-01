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

	protected $umanager;
	/**
	* prepare UserManager before every test.
	*/
	protected function setUp() {
		$this->umanager = $this->prepareUM();
	}

	/**
	* Use our MailLessMockManager and in-memory SQLite.
	*/
	private function prepareUM()  {
		$dsn = 'sqlite::memory:';
		$dbh = new PDO($dsn);
		$umanager = new MailLessUserManager($dbh);
		$umanager->recreateTables();
		return $umanager;
	}

	public function testGoodRegister() {
		$this->assertNotNull($this->umanager);
		$this->assertEquals(0, count($this->umanager->emails));
		$this->umanager->register(mt_rand(0,time()) . '@localhost', 'pwd-' . time());
		$this->assertEquals(1, count($this->umanager->emails));
 	}

	public function testDuplicateRegister() {
		$this->assertTrue($this->umanager->register('user@localhost', 'pwd'));
		$this->assertFalse($this->umanager->register('user@localhost', 'doors'));
	}

	/**
	@expectedException InvalidEmailException
	*/
	public function testInvalidLogin() {
		$this->umanager->register('invalid', 'invalid');
	}

	/**
	@expectedException InvalidPasswordException
	*/
	public function testInvalidPassword() {
		$this->umanager->login('normal@host', '');
	}

	public function testGoodLogin() {
		$email = 'test@localhost';
		$pwd = 'password';
		$this->assertTrue($this->umanager->register($email, $pwd));
		$this->assertTrue($this->umanager->login($email, $pwd));
	}

	public function testWrongLogin() {
		$this->assertFalse($this->umanager->login('user@localhost', 'beatles'));
	}

	public function testPasswordReset() {
		$this->passwordReset('user@localhost','first');
	}
	public function testPasswordResetUnknownEmail() {
		$this->assertFalse($this->umanager->passwordReset('unknown@localhost'));
		$this->assertEquals(0, count($this->umanager->emails));
	}

	public function passwordReset($email, $pwd) {
		$this->assertTrue($this->umanager->register($email, $pwd));
		$this->assertTrue($this->umanager->login($email, $pwd));
		$this->assertTrue($this->umanager->passwordReset($email));
		$this->assertEquals(2, count($this->umanager->emails));
		$token = $this->umanager->emails[1]['token'];
		$this->assertNotEmpty($token);
		return $token;
	}

	public function testPasswordChangeWrongToken() {
		$email = 'user@localhost';
		$pwd = 'first';
		$this->passwordReset($email,$pwd);
		$pwd = 'second';
		$this->assertFalse($this->umanager->passwordChange($email, 'wrongToken', $pwd));
		$this->assertFalse($this->umanager->login($email, $pwd));
	}

	public function testPasswordChangeGoodLoginInTheMiddle() {
		$email = 'user@localhost';
		$pwd = 'first';
		$token = $this->passwordReset($email, $pwd);
		$this->assertTrue($this->umanager->login($email, $pwd));
		$pwd = 'second';
		$this->assertFalse($this->umanager->passwordChange($email, $token, $pwd));
	}

	public function testPasswordChangeOK() {
		$email = 'user@localhost';
		$pwd = 'first';
		$token = $this->passwordReset($email, $pwd);
		$this->assertTrue($this->umanager->passwordChange($email, $token, $pwd));
		$this->assertEquals(3, count($this->umanager->emails));
		$this->assertEquals($pwd, end($this->umanager->emails)['password']);
		$this->assertTrue($this->umanager->login($email, $pwd));
	}

}
?>
