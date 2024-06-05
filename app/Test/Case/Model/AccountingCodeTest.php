<?php
App::uses('AccountingCode', 'Model');

/**
 * AccountingCode Test Case
 *
 */
class AccountingCodeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.accounting_code'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->AccountingCode = ClassRegistry::init('AccountingCode');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->AccountingCode);

		parent::tearDown();
	}

}
