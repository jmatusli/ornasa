<?php
/**
 * ExchangeRateFixture
 *
 */
class ExchangeRateFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
		'conversion_currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'base_currency_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
		'rate' => array('type' => 'float', 'null' => false, 'default' => null, 'unsigned' => false),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1),
			'fk_exchange_rates_currencies1_idx' => array('column' => 'conversion_currency_id', 'unique' => 0),
			'fk_exchange_rates_currencies2_idx' => array('column' => 'base_currency_id', 'unique' => 0)
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
			'conversion_currency_id' => 1,
			'base_currency_id' => 1,
			'rate' => 1,
			'created' => '2014-08-22 19:23:14',
			'modified' => '2014-08-22 19:23:14'
		),
	);

}
