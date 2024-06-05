<?php
App::uses('Cheque', 'Model');

/**
 * Cheque Test Case
 *
 */
class ChequeTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'app.cheque',
		'app.currency',
		'app.purchase_order_product',
		'app.bank_accounting_code',
		'app.accounting_register',
		'app.accounting_register_type',
		'app.accounting_movement',
		'app.accounting_code',
		'app.purchase',
		'app.cheque_type'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->Cheque = ClassRegistry::init('Cheque');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Cheque);

		parent::tearDown();
	}

}
