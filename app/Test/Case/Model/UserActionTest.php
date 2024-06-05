<?php
App::uses('UserAction', 'Model');

/**
 * UserAction Test Case
 *
 */
class UserActionTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.user_action',
		'app.user',
		'app.role',
		'app.user_log'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->UserAction = ClassRegistry::init('UserAction');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->UserAction);

		parent::tearDown();
	}

}
