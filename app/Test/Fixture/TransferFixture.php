<?php
/**
 * TransferFixture
 *
 */
class TransferFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'transfer_date' => array('type' => 'date', 'null' => false, 'default' => null),
		'transfer_code' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 50, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
		'amount' => array('type' => 'decimal', 'null' => false, 'default' => null, 'length' => '10,2', 'unsigned' => false),
		'currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'bank_accounting_code_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'cashbox_accounting_code_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'accounting_register_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'transfer_date' => '2015-06-07',
			'transfer_code' => 'Lorem ipsum dolor sit amet',
			'amount' => '',
			'currency_id' => 1,
			'bank_accounting_code_id' => 1,
			'cashbox_accounting_code_id' => 1,
			'accounting_register_id' => 1,
			'created' => '2015-06-07 19:08:21',
			'modified' => '2015-06-07 19:08:21'
		),
	);

}
