<?php
App::uses('AppModel', 'Model');
/**
 * RecipeConsumable Model
 *
 * @property Recipe $Recipe
 * @property Product $Product
 * @property Unit $Unit
 */
class RecipeConsumable extends AppModel {


	//The Associations below have been created with all possible keys, those that are not needed can be removed

	public $belongsTo = [
		'Recipe' => [
			'className' => 'Recipe',
			'foreignKey' => 'recipe_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
		'Unit' => [
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];
}
