<?php
/**
 * PaymentDepositFixture
 *
 */
class PaymentDepositFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'cash_receipt_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'invoice_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		'transfer_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'bool_deposit_complete' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
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
			'cash_receipt_id' => 1,
			'invoice_id' => 1,
			'transfer_id' => 1,
			'amount' => '',
			'bool_deposit_complete' => 1,
			'created' => '2018-02-12 12:16:23',
			'modified' => '2018-02-12 12:16:23'
		),
	);

}
