<?php
/**
 * TransactionFixture
 *
 */
class TransactionFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'order_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'amount' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'transaction_type_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'cashregister_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'transaction_mode_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'customer_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'accounting_id' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_transactions_transaction_modes1_idx' => array('column' => 'transaction_mode_id', 'unique' => 0),
			'fk_transactions_accounting1_idx' => array('column' => 'accounting_id', 'unique' => 0),
			'fk_transactions_currencies1_idx' => array('column' => 'currency_id', 'unique' => 0),
			'fk_transactions_customers1_idx' => array('column' => 'customer_id', 'unique' => 0),
			'fk_transactions_orders1_idx' => array('column' => 'order_id', 'unique' => 0),
			'fk_transactions_users1_idx' => array('column' => 'user_id', 'unique' => 0),
			'fk_transactions_cashregisters1_idx' => array('column' => 'cashregister_id', 'unique' => 0),
			'fk_transactions_transaction_types1' => array('column' => 'transaction_type_id', 'unique' => 0)
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
			'user_id' => 1,
			'order_id' => 1,
			'amount' => 1,
			'currency_id' => 1,
			'transaction_type_id' => 1,
			'cashregister_id' => 1,
			'transaction_mode_id' => 1,
			'customer_id' => 1,
			'accounting_id' => 1,
			'created' => '2014-08-22 19:37:41',
			'modified' => '2014-08-22 19:37:41'
		),
	);

}
