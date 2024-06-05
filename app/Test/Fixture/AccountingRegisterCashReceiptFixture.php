<?php
/**
 * AccountingRegisterCashReceiptFixture
 *
 */
class AccountingRegisterCashReceiptFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'accounting_register_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'cash_receipt_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'accounting_register_id' => 1,
			'cash_receipt_id' => 1,
			'created' => '2015-07-20 18:16:59',
			'modified' => '2015-07-20 18:16:59'
		),
	);

}
