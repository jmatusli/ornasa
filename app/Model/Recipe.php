<?php
App::uses('AppModel', 'Model');

class Recipe extends AppModel {

  public function getRecipeList($recipeIds=[]){
    $conditions=[];
    if (!empty($recipeIds)){
      $conditions=['Recipe.id'=>array_values($recipeIds)[0]];
    }
    //pr($conditions);
    return $this->find('list',[
      'fields'=>['Recipe.id','Recipe.name'],
      'conditions'=>$conditions,
      'order'=>'Recipe.name ASC',
    ]);
  }

  public function getRecipeListForProducts($productIds){
    //pr($productIds);
    $allRecipes=$this->find('all',[
      'fields'=>['Recipe.id','Recipe.name'],
      'conditions'=>['Recipe.product_id'=>$productIds],
      'contain'=>[
        'Product'=>[
          'fields'=>['Product.id','Product.name']
        ],
      ],
      'order'=> 'Product.name ASC, Recipe.name ASC'
    ]);
    //pr($allRecipes);
    $recipes =[];
    if (!empty($allRecipes)){
      foreach ($allRecipes as $recipe){
        $recipes[$recipe['Recipe']['id']]=$recipe['Recipe']['name'].' ('.$recipe['Product']['name'].')';
      }
    }
    return $recipes;
  }
  
  public function getProductRecipeList($productIds){
    $allRecipes=$this->find('all',[
      'fields'=>['Recipe.id','Recipe.product_id',],
      'conditions'=>['Recipe.product_id'=>$productIds],
      'recursive'=>-1,
    ]);
    //pr($allRecipes);
    $productRecipes =[];
    
    if (!empty($allRecipes)){
      foreach ($allRecipes as $recipe){
        if (!array_key_exists($recipe['Recipe']['product_id'],$productRecipes)){
          $productRecipes[$recipe['Recipe']['product_id']]=[];
        }
        array_push($productRecipes[$recipe['Recipe']['product_id']],$recipe['Recipe']['id']);
      }
    }
    //pr($productRecipes);  
    return $productRecipes;
  }

  public function getMillConversionProductIdsForRecipes(){
    return $this->find('list',[
      'fields'=>['Recipe.id','Recipe.mill_conversion_product_id'],
    ]);
  }
  
  public function getRecipeIngredients($recipeId){
    return $this->RecipeItem->find('all',[
      'conditions'=>['RecipeItem.recipe_id'=>$recipeId],
      'contain'=>['Product'],
      'recursive'=>-1,
    ]);
    //pr($allRecipes);
  }
  public function getRecipeIngredientList($recipeId){
    return $this->RecipeItem->find('list',[
      'fields'=>['RecipeItem.product_id'],
      'conditions'=>['RecipeItem.recipe_id'=>$recipeId],
    ]);
  }
  
  public function getRecipeConsumables($recipeId){
    return $this->RecipeConsumable->find('all',[
      'conditions'=>['RecipeConsumable.recipe_id'=>$recipeId],
      'contain'=>['Product'],
      'recursive'=>-1,
    ]);
    //pr($allRecipes);
  }
  public function getRecipeConsumableList($recipeId){
    return $this->RecipeConsumable->find('list',[
      'fields'=>['RecipeConsumable.product_id'],
      'conditions'=>['RecipeConsumable.recipe_id'=>$recipeId],
    ]);
  }

  public function getRecipeIdsWithProductIdPending(){
    return $this->find('list',[
      'fields'=>['Recipe.id','Recipe.created'],
      'conditions'=>['Recipe.product_id'=>PLACEHOLDER_ID],
    ]);
  }
  
  function getRecipeById($recipeId){
    return $this->find('first',[
      'conditions'=>[
        'Recipe.id'=>$recipeId,
      ],
      'recursive'=>-1,
    ]);
  }
 
  function getProductId($recipeId){
    $recipe=$this->getRecipeById($recipeId);
    return $recipe['Recipe']['product_id'];
  }
  
	public $validate = [
		'product_id' => [
			'numeric' => [
				'rule' => ['numeric'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
		'name' => [
			'notEmpty' => [
				'rule' => ['notEmpty'],
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			],
		],
	];

	public $belongsTo = [
		'Product' => [
			'className' => 'Product',
			'foreignKey' => 'product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
    'MillConversionProduct' => [
			'className' => 'Product',
			'foreignKey' => 'mill_conversion_product_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		],
	];

	public $hasMany = [
		'RecipeItem' => [
			'className' => 'RecipeItem',
			'foreignKey' => 'recipe_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
    'RecipeConsumable' => [
			'className' => 'RecipeConsumable',
			'foreignKey' => 'recipe_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		],
	];

}
