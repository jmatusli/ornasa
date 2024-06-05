<?php
/**
 * AccountingRegisterInvoiceFixture
 *
 */
class AccountingRegisterInvoiceFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'accounting_register_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'invoice_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
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
			'invoice_id' => 1,
			'created' => '2015-05-25 19:10:06',
			'modified' => '2015-05-25 19:10:06'
		),
	);

}
