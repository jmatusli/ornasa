<?php
App::uses('AccountingRegisterType', 'Model');

/**
 * AccountingRegisterType Test Case
 *
 */
class AccountingRegisterTypeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.accounting_register_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AccountingRegisterType = ClassRegistry::init('AccountingRegisterType');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AccountingRegisterType);

		parent::tearDown();
	}

}
