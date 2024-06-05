<?php
/**
 * CashReceiptFixture
 *
 */
class CashReceiptFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'receipt_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'receipt_code' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cash_receipt_type_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'client_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'concept' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 500, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'bool_cash' => array('type' => 'boolean', 'null' => true, 'default' => null),
		'cheque_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'cheque_bank' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 45, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
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
			'receipt_date' => '2015-05-26',
			'receipt_code' => 'Lorem ipsum dolor sit amet',
			'cash_receipt_type_id' => 1,
			'amount' => '',
			'currency_id' => 1,
			'client_id' => 1,
			'concept' => 'Lorem ipsum dolor sit amet',
			'bool_cash' => 1,
			'cheque_number' => 'Lorem ipsum dolor sit amet',
			'cheque_bank' => 'Lorem ipsum dolor sit amet',
			'created' => '2015-05-26 18:10:16',
			'modified' => '2015-05-26 18:10:16'
		),
	);

}
