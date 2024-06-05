<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class RecipesController extends AppController {

  public $components = array('Paginator','RequestHandler');
	public $helpers = array('PhpExcel');
  
  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('saveRecipe','showRecipeSelectorAndIngredients','getRecipeIngredients','getRecipeConsumables');		
	}
  
  public function saveRecipe() {
		$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
    $this->loadModel('RecipeItem');
    $this->loadModel('RecipeConsumable');
    
    //pr($_POST);
    
		$recipeId=trim($_POST['recipeId']);
		
    $recipeProductId=trim($_POST['recipeProductId']);
    $recipeName=trim($_POST['recipeName']);
    $recipeDescription=trim($_POST['recipeDescription']);
		$recipeMillConversionProductId=trim($_POST['recipeMillConversionProductId']);
    
    $recipeItems=$_POST['recipeItems'];
    $recipeConsumables=[];
    if (isset($_POST['recipeConsumables'])){
      $recipeConsumables=$_POST['recipeConsumables'];
    }
    
    $boolRecipeValid=true;
    if ($recipeId == 0){
      $this->Recipe->recursive=-1;
      $existingRecipesForProductWithThisName=$this->Recipe->find('all',[
        'fields'=>['Recipe.id'],
        'conditions'=>[
          'Recipe.name'=>$recipeName,
          'Recipe.product_id'=>$recipeProductId,
        ],
      ]);
    }
    if (!empty($existingRecipesForProductWithThisName)){
      return "Ya existe una receta para este producto con el mismo nombre.  No se guardó la receta.";
    }
    elseif (empty($recipeProductId)){
      return "La receta debe tener un producto fabricado.  No se guardó la receta.";
    }
    elseif (empty($recipeName)){
      return "La receta debe tener un nombre.  No se guardó la receta.";
    }
    elseif (empty($recipeItems)){
      return $recipeItems;
      return "La receta tiene que tener por lo menos un ingrediente.  No se guardó la receta.";
    }
    else {
      $datasource=$this->Recipe->getDataSource();
      $datasource->begin();
      try {
        if ($recipeId > 0){
          $previousRecipeItems=$this->RecipeItem->find('list',[
            'fields'=>'RecipeItem.id',
            'conditions'=>['RecipeItem.recipe_id'=>$recipeId],
            'recursive'=>-1,
          ]);
          if (!empty($previousRecipeItems)){
            foreach ($previousRecipeItems as $previousRecipeItemId){
              $this->RecipeItem->id=$previousRecipeItemId;
              if (!$this->RecipeItem->delete($previousRecipeItemId)) {
                echo "Problema eliminando los ingredientes anteriores";
                pr($this->validateErrors($this->RecipeItem));
                throw new Exception();
              }  
            }              
          }
        
          $previousRecipeConsumables=$this->RecipeConsumable->find('list',[
            'fields'=>'RecipeConsumable.id',
            'conditions'=>['RecipeConsumable.recipe_id'=>$recipeId],
            'recursive'=>-1,
          ]);
          if (!empty($previousRecipeConsumables)){
            foreach ($previousRecipeConsumables as $previousRecipeConsumableId){
              $this->RecipeConsumable->id=$previousRecipeConsumableId;
              if (!$this->RecipeConsumable->delete($previousRecipeConsumableId)) {
                echo "Problema eliminando los consumibles anteriores";
                pr($this->validateErrors($this->RecipeConsumable));
                throw new Exception();
              }  
            }              
          }
        }
                
        $recipeArray=[  
          'Recipe'=>[            
            'product_id'=>$recipeProductId,
            'name'=>$recipeName,
            'description'=>$recipeDescription,
            'mill_conversion_product_id'=>$recipeMillConversionProductId,
          ],
        ];          
        if ($recipeId == 0){
          $this->Recipe->create();
        }
        else {
          $this->Recipe->id=$recipeId;
          //if (!$this->Recipe->exists($recipeId)) {
          //  throw new Exception(__('Receta inválida'));
          //}				
        }
        if (!$this->Recipe->save($recipeArray)) {
          echo "Problema guardando la receta";
          pr($this->validateErrors($this->Recipe));
          throw new Exception();
        }
        $recipeId=$this->Recipe->id;
        
        
        foreach ($recipeItems as $recipeItem){
          //pr($recipeItem);
          $this->RecipeItem->create();
          $recipeItemData=[
            'RecipeItem'=>[
              'recipe_id'=>$recipeId,
              'product_id'=>$recipeItem['product_id'],
              'quantity'=>$recipeItem['quantity'],
              'unit_id'=>$recipeItem['unit_id'],
            ],
          ];
          if (!$this->RecipeItem->save($recipeItemData)) {
            echo "Problema guardando los ingredientes de la receta";
            pr($this->validateErrors($this->RecipeItem));
            throw new Exception();
          }  
        }
        if (!empty($recipeConsumables)){
          foreach ($recipeConsumables as $recipeConsumable){
            //pr($recipeConsumable);
            $this->RecipeConsumable->create();
            $recipeConsumableData=[
              'RecipeConsumable'=>[
                'recipe_id'=>$recipeId,
                'product_id'=>$recipeConsumable['product_id'],
                'quantity'=>$recipeConsumable['quantity'],
                'unit_id'=>$recipeConsumable['unit_id'],
              ],
            ];
            if (!$this->RecipeConsumable->save($recipeConsumableData)) {
              echo "Problema guardando los consumibles de la receta";
              pr($this->validateErrors($this->RecipeConsumable));
              throw new Exception();
            }  
          }
        }
        
        
        $datasource->commit();
      
        $this->recordUserAction($this->Recipe->id,"crear",null);
        $this->recordUserActivity($this->Session->read('User.username'),"Se registró la receta ".$recipeName." desde la pantalla de producto");
        
        //$this->Session->setFlash('Se guardó la receta.','default',['class' => 'success']);
        
        /*
        $savedRecipe=$this->Recipe=>find('first',[
          'conditions'=>['Recipe.id'=>$recipeId],
          'contain'=>['RecipeItem'],
        ]);
        //return true;
        return $savedRecipe;
        */
        //echo "recipeId is ".$recipeId."";
        return $recipeId;
      } 
      catch(Exception $e){
        $datasource->rollback();
        pr($e);
        $this->Session->setFlash('No se guardó la receta.', 'default',['class' => 'error-message']);
        //return false;
      }
    }
  }
	
  public function showRecipeSelectorAndIngredients() {
		//$this->autoRender = false; // We don't render a view in this example    
		$this->request->onlyAllow('ajax'); // No direct access via browser URL
		$this->layout = "ajax";// just in case to reduce the error message;
		
		$recipeId=(isset($_POST['recipeId'])?trim($_POST['recipeId']):0);
		$productId=trim($_POST['productId']);
    $this->set(compact('recipeId'));
    $referrerForm=trim($_POST['referrerForm']);
    $this->set(compact('referrerForm'));
    
    $this->loadModel('Product');
    $this->loadModel('Unit');
    
    $productRecipeIds=$this->Recipe->getProductRecipeList($productId);
    //pr($productRecipeIds);
    $recipes=$this->Recipe->getRecipeList($productRecipeIds);
    $this->set(compact('recipes'));
    
    $recipe=$this->Recipe->find('first',[
      'conditions'=>['Recipe.id'=>$recipeId],
      'contain'=>[
        'RecipeItem',
        'RecipeConsumable',
      ],
      'order'=>'Recipe.name ASC',
    ]);
    $this->set(compact('recipe'));  
    
    $rawMaterials=$this->Product->getProductsByProductNature(PRODUCT_NATURE_RAW);
    $this->set(compact('rawMaterials'));  
    
    $consumables=$this->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
    $this->set(compact('consumables'));  
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));  
  }
	
  public function getRecipeIngredients(){
    $this->request->onlyAllow('ajax'); 
		    
    $this->loadModel('PlantProductType');
    $this->loadModel('PlantProductionType');
    $this->loadModel('Product');
    $this->loadModel('ProductType');
    
    $this->loadModel('Unit');
    
		$recipeId=trim($_POST['recipeId']);
    
    $finishedProductQuantity=trim($_POST['finishedProductQuantity']);
    $this->set(compact('finishedProductQuantity'));
    
    if (isset($_POST['requestIngredients'])){
      $requestIngredients=$_POST['requestIngredients']; 
    }
    else {
      $requestIngredients=[
        'Products'=>[],
      ]; 
    }
    $this->set(compact('requestIngredients'));
    $recipeProductId=$this->Recipe->getProductId($recipeId);
    $recipeProductionTypeId=$this->Product->getProductionTypeId($recipeProductId);
    $plantIdsForProductionTypeId=$this->PlantProductionType->getPlantIdsForProductionType($recipeProductionTypeId);
    //pr($plantIdsForProductionTypeId);
    $rawProductTypeIds=$this->ProductType->getProductTypeIdsForCategory(CATEGORY_RAW);
    //pr($rawProductTypeIds);
    $plantProductTypeIds=$this->PlantProductType->getProductTypeIdsForPlant($plantIdsForProductionTypeId);
    //pr($plantProductTypeIds);
    $productConditions=[
      'Product.product_type_id'=> array_intersect($rawProductTypeIds,$plantProductTypeIds),
      'Product.production_type_id'=> $recipeProductionTypeId,
      'Product.bool_active'=> true
    ];
    //pr($productConditions);
    $products = $this->Product->find('list',[
			'fields'=>'Product.id,Product.name',
			'conditions' => $productConditions,
      'order'=>'Product.name'
		]);
    $this->set(compact('products'));
    
    $recipeIngredients=$this->Recipe->getRecipeIngredients($recipeId);
    $this->set(compact('recipeIngredients'));
    
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
  }
  
  public function getRecipeConsumables(){
    $this->request->onlyAllow('ajax'); 
		
    $this->loadModel('PlantProductType');
    $this->loadModel('PlantProductionType');
    $this->loadModel('Product');
    //$this->loadModel('ProductType');
    
    $this->loadModel('Unit');
    
    $this->loadModel('Warehouse');
    $this->loadModel('WarehouseProduct');
    
    $recipeId=trim($_POST['recipeId']);
    
    $finishedProductQuantity=trim($_POST['finishedProductQuantity']);
    $this->set(compact('finishedProductQuantity'));
    
    if (isset($_POST['requestRecipeConsumables'])){
      $requestRecipeConsumables=$_POST['requestRecipeConsumables']; 
    }
    else {
      $requestRecipeConsumables=[
        'Products'=>[],
      ]; 
    }
    $this->set(compact('requestRecipeConsumables'));
    
    $recipeProductId=$this->Recipe->getProductId($recipeId);
    $recipeProductionTypeId=$this->Product->getProductionTypeId($recipeProductId);
    //pr($recipeProductionTypeId);
    $plantIdsForProductionTypeId=$this->PlantProductionType->getPlantIdsForProductionType($recipeProductionTypeId);
    //pr($plantIdsForProductionTypeId);
    //$otherProductTypeIds=$this->ProductType->getProductTypeIdsForCategory(CATEGORY_OTHER);
    //pr($otherProductTypeIds);
    $plantProductTypeIds=$this->PlantProductType->getProductTypeIdsForPlant($plantIdsForProductionTypeId);
    //pr($plantProductTypeIds);
    $plantWarehouseIds=$this->Warehouse->getWarehouseIdsForPlantId($plantIdsForProductionTypeId);
    //pr($plantWarehouseIds);
    $warehouseProductIds=$this->WarehouseProduct->getProductIdsForWarehouse($plantWarehouseIds);
    
    $productConditions=[
      //'Product.product_type_id'=> array_intersect($otherProductTypeIds,$plantProductTypeIds),
      'Product.product_type_id'=> $plantProductTypeIds,
      //'Product.production_type_id'=> $recipeProductionTypeId,
      'Product.bool_active'=> true,
      'Product.product_nature_id'=> PRODUCT_NATURE_BAGS,
      'Product.id'=> $warehouseProductIds,
    ];
    //pr($productConditions);
    $products = $this->Product->find('list',[
			'fields'=>'Product.id,Product.name',
			'conditions' => $productConditions,
      'order'=>'Product.name'
		]);
    $this->set(compact('products'));
    
    $recipeConsumables=$this->Recipe->getRecipeConsumables($recipeId);
    $this->set(compact('recipeConsumables'));
    
    $this->loadModel('Unit');
    $units=$this->Unit->getUnitList();
    $this->set(compact('units'));
  }

  public function resumen() {
    $this->loadModel('Product');
    $this->loadModel('ProductionType');
    
    $this->loadModel('UserPlant');
    
		//$this->loadModel('Warehouse');
    //$this->loadModel('UserWarehouse');
    //$this->loadModel('WarehouseProduct');
    
    $this->Recipe->recursive = -1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));


    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    //$warehouseId=0;
    $productionTypeId=0;
		
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlants(array_keys($plants));
    $this->set(compact('productionTypes'));
    
    $productionTypeId=0;
    if (count($productionTypes) == 1){
      $productionTypeId=array_keys($productionTypes)[0];
    }
    elseif ($this->request->is('post')) {
      $productionTypeId=$this->request->data['Report']['production_type_id'];
    }
    $this->set(compact('productionTypeId'));
  /*  
		if ($this->request->is('post')) {
			//$warehouseId=$this->request->data['Report']['warehouse_id'];
      //$productionTypeId=$this->request->data['Report']['production_type_id'];
		}
    $this->set(compact('warehouseId'));
    $this->set(compact('productionTypeId'));
    
    $warehouses=$this->UserWarehouse->getWarehouseListForUser($loggedUserId);
    //pr($warehouses);
    $this->set(compact('warehouses'));
    if (count($warehouses) == 1){
      $warehouseId=array_keys($warehouses)[0];
    }
  	
		  
    $_SESSION['warehouseId']=$warehouseId;
    $this->set(compact('warehouseId'));
     $productionTypes=$this->ProductionType->getProductionTypeList();
    $this->set(compact('productionTypes'));
	*/	
    $recipeConditions=[];
    if ($productionTypeId){
      $productIdsForProductionType=$this->Product->find('list',[
        'fields'=>['Product.id'],
        'conditions'=>[
          'Product.production_type_id'=>$productionTypeId,
        ],
      ]);  
      $recipeConditions['Recipe.product_id']=$productIdsForProductionType;
    }
    //if ($warehouseId > 0){
    //  $recipeConditions['Recipe.warehouse_id']=$warehouseId;
    //}
    
		$recipeCount=	$this->Recipe->find('count', [
			'fields'=>['Recipe.id'],
			'conditions' => $recipeConditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $recipeConditions,
			'contain'=>[
        'Product',
        'MillConversionProduct',
        'RecipeItem'=>[
          'Product',
          'Unit',
        ],
        'RecipeConsumable'=>[
          'Product',
          'Unit',
        ],
			],
			'limit'=>($recipeCount!=0?$recipeCount:1),
		] ;

		$recipes = $this->Paginator->paginate('Recipe');
		$this->set(compact('recipes'));
	}

	public function detalle($id = null) {
		if (!$this->Recipe->exists($id)) {
			throw new NotFoundException(__('Invalid recipe'));
		}
    /*
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
    */
		$options = [
      'conditions' => [
        'Recipe.id' => $id,
      ],
      'contain'=>[
        'Product'=>[
          'ProductionType'
        ],
        'MillConversionProduct',
        'RecipeItem'=>[
          'Product',
          'Unit'
        ], 
        'RecipeConsumable'=>[
          'Product',
          'Unit'
        ], 
      ],
    ];
		$this->set('recipe', $this->Recipe->find('first', $options));
	}

	public function crear() {
    $this->loadModel('RecipeItem');
    $this->loadModel('RecipeConsumable');
    
    $this->loadModel('Product');
    $this->loadModel('ProductionType');
    $this->loadModel('Unit');
    
		//$this->loadModel('Plant');
    $this->loadModel('UserPlant');
    
    //$this->loadModel('Warehouse');
    //$this->loadModel('UserWarehouse');
    
    $this->Recipe->recursive = -1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));

    //$plantId=0;
		
    $requestItems=[];
    $requestConsumables=[];
		
		//if ($this->request->is('post')) {
		//	$plantId=$this->request->data['ProductionRun']['plant_id'];
		//}
		//$this->set(compact('plantId'));
    
    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    //pr($plants);
    $this->set(compact('plants'));
    //if (count($plants) == 1){
    //  $plantId=array_keys($plants)[0];
    //}
    //elseif (count($plants) > 1 && $plantId == 0){
    //  if (!empty($_SESSION['plantId'])){
    //    $plantId = $_SESSION['plantId'];
    //  }
    //  else {
    //    $plantId=0;
    //  }
    //}
    //$_SESSION['plantId']=$plantId;
    //$this->set(compact('plantId'));
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlants(array_keys($plants));
    $this->set(compact('productionTypes'));
    
    $productionTypeId=0;
    if (count($productionTypes) == 1){
      $productionTypeId=array_keys($productionTypes)[0];
    }
    elseif ($this->request->is('post')) {
      $productionTypeId=$this->request->data['Recipe']['production_type_id'];
    }
    $this->set(compact('productionTypeId'));
    
    $products = $this->Recipe->Product->getProductsByProductionType($productionTypeId,PRODUCT_NATURE_PRODUCED);
		$this->set(compact('products'));
    
    //$productProductionTypes=$this->Product->getProductProductionTypeList(array_keys($products));
    //$this->set(compact('productProductionTypes'));
        
    $millConversionProducts=$rawMaterials = $this->Recipe->Product->getProductsByProductionType($productionTypeId,PRODUCT_NATURE_RAW);
		$this->set(compact('rawMaterials','millConversionProducts'));
    //pr($rawMaterials);
    
    //$rawMaterialProductionTypes=$this->Product->getProductProductionTypeList(array_keys($rawMaterials));
    //$this->set(compact('rawMaterialProductionTypes'));
    
    $rawMaterialUnits=$this->Product->getProductUnitList(array_keys($rawMaterials));
    $this->set(compact('rawMaterialUnits'));
    //pr($rawMaterialUnits);
    
    $consumables = $this->Recipe->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
		$this->set(compact('consumables'));
    
    $consumableUnits=$this->Product->getProductUnitList(array_keys($consumables));
    $this->set(compact('consumableUnits'));
    
    if ($this->request->is('post') && empty($this->request->data['selectProductionType'])) {
      //echo 'post received';
      foreach ($this->request->data['RecipeItem'] as $requestItem){
        if ($requestItem['product_id'] > 0 && $requestItem['quantity'] >0){
          $requestItems[]=$requestItem;
        }
      }
      foreach ($this->request->data['RecipeConsumable'] as $requestConsumable){
        if ($requestConsumable['product_id'] > 0 && $requestConsumable['quantity'] >0){
          $requestConsumables[]=$requestConsumable;
        }
      }
      
      $previousRecipesWithThisName=[];
			$previousRecipesWithThisName=$this->Recipe->find('all',[
				'conditions'=>[
					'TRIM(LOWER(Recipe.name))'=>trim(strtolower($this->request->data['Recipe']['name'])),
				],
			]);
      
      if (count($previousRecipesWithThisName)>0){
				$this->Session->setFlash('Ya se introdujo una receta con este nombre!  No se guardó la receta.', 'default',['class' => 'error-message']);
			}
      elseif (empty($this->request->data['Recipe']['name'])){
        $this->Session->setFlash('Se debe especificar el nombre de la receta.  No se guardó la receta.', 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['Recipe']['product_id'])){
        $this->Session->setFlash('Se debe especificar el producto que se fabrica con la receta.  No se guardó la receta.', 'default',['class' => 'error-message']);
      }
      elseif (empty($requestItems)){
        $this->Session->setFlash('Se deben especificar uno o más ingredienes para la receta.  No se guardó la receta.', 'default',['class' => 'error-message']);
      }
      else {
        echo 'starting to save';
        $successMessage='Se guardó el producto.  ';
        
				$datasource=$this->Recipe->getDataSource();
				$datasource->begin();
				try {
          $this->Recipe->create();
					if (!$this->Recipe->save($this->request->data['Recipe'])) {
						echo "problema al guardar la receta";
						pr($this->validateErrors($this->Recipe));
						throw new Exception();
					} 
					$recipeId=$this->Recipe->id;
          //echo 'saved recipe';
          
          foreach ($this->request->data['RecipeItem'] as $recipeItem){
            if ($recipeItem['product_id'] > 0 && $recipeItem['quantity'] >0){  
              $recipeItemArray=[
                'RecipeItem'=>[
                  'recipe_id'=>$recipeId,
                  'product_id'=>$recipeItem['product_id'],
                  'quantity'=>$recipeItem['quantity'],
                  'unit_id'=>$recipeItem['unit_id'],
                ]
              ];  
              $this->RecipeItem->create();
              if (!$this->RecipeItem->save($recipeItemArray)) {
                echo "Problema guardando los ingredientes";
                pr($this->validateErrors($this->RecipeItem));
                throw new Exception();
              }   
            }
          }
          //echo 'saved ingredients';
          foreach ($this->request->data['RecipeConsumable'] as $recipeConsumable){
            if ($recipeConsumable['product_id'] > 0 && $recipeConsumable['quantity'] >0){  
              $recipeConsumableArray=[
                'RecipeConsumable'=>[
                  'recipe_id'=>$recipeId,
                  'product_id'=>$recipeConsumable['product_id'],
                  'quantity'=>$recipeConsumable['quantity'],
                  'unit_id'=>$recipeConsumable['unit_id'],
                ]
              ];  
              $this->RecipeConsumable->create();
              if (!$this->RecipeConsumable->save($recipeConsumableArray)) {
                echo "Problema guardando los consumibles";
                pr($this->validateErrors($this->RecipeConsumable));
                throw new Exception();
              }   
            }
          }  

          
          $datasource->commit();
					
					$this->recordUserAction($this->Recipe->id,null,null);
					$this->recordUserActivity($this->Session->read('User.username'),"Se registró la receta ".$this->request->data['Recipe']['name']);
          
          $this->Session->setFlash('Se guardó la receta.','default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen',$recipeId]);
				} 		
				catch(Exception $e){
					$datasource->rollback();
					pr($e);					
					$this->Session->setFlash(__('The recipe could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
				}
      }
		}
		
    $this->set(compact('requestItems'));
    $this->set(compact('requestConsumables'));
    
    $units = $this->Unit->getUnitList();
		$this->set(compact('units'));
	}

	public function editar($id = null) {
	  if (!$this->Recipe->exists($id)) {
			throw new NotFoundException(__('Invalid recipe'));
		}
    
    $this->loadModel('RecipeItem');
    $this->loadModel('RecipeConsumable');
    
    $this->loadModel('Product');
    $this->loadModel('ProductionType');
    $this->loadModel('Unit');
    
		$this->loadModel('UserPlant');
    
    $this->Recipe->recursive = -1;
    
    $loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));

    $plants=$this->UserPlant->getPlantListForUser($loggedUserId);
    $this->set(compact('plants'));
    
    $productionTypes=$this->ProductionType->getProductionTypesForPlants(array_keys($plants));
    $this->set(compact('productionTypes'));
    
    $productionTypeId=0;
    if (count($productionTypes) == 1){
      $productionTypeId=array_keys($productionTypes)[0];
    }
    elseif ($this->request->is(['post','put'])) {
      $productionTypeId=$this->request->data['Recipe']['production_type_id'];
    }
    
    //echo 'productiontypeid is '.$productionTypeId.'<br/>';
    
    $requestItems=[];
    $requestConsumables=[];
		if ($this->request->is(['post', 'put'])){
      foreach ($this->request->data['RecipeItem'] as $requestItem){
        if ($requestItem['product_id'] > 0 && $requestItem['quantity'] >0){
          $requestItems[]=$requestItem;
        }
      }
      foreach ($this->request->data['RecipeConsumable'] as $requestConsumable){
        if ($requestConsumable['product_id'] > 0 && $requestConsumable['quantity'] >0){
          $requestConsumables[]=$requestConsumable;
        }
      }
      
      if (empty($this->request->data['selectProductionType'])) {
        $previousRecipesWithThisName=[];
        $previousRecipesWithThisName=$this->Recipe->find('all',[
          'conditions'=>[
            'TRIM(LOWER(Recipe.name))'=>trim(strtolower($this->request->data['Recipe']['name'])),
            'Recipe.id !='=>$id,
          ],
        ]);
        
        if (count($previousRecipesWithThisName)>0){
          $this->Session->setFlash('Ya se introdujo una receta con este nombre!  No se guardó la receta.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Recipe']['name'])){
          $this->Session->setFlash('Se debe especificar el nombre de la receta.  No se guardó la receta.', 'default',['class' => 'error-message']);
        }
        elseif (empty($this->request->data['Recipe']['product_id'])){
          $this->Session->setFlash('Se debe especificar el producto que se fabrica con la receta.  No se guardó la receta.', 'default',['class' => 'error-message']);
        }
        elseif (empty($requestItems)){
          $this->Session->setFlash('Se deben especificar uno o más ingredienes para la receta.  No se guardó la receta.', 'default',['class' => 'error-message']);
        }
        else {
          $successMessage='Se guardó el producto.  ';
          
          $datasource=$this->Recipe->getDataSource();
          $datasource->begin();
          try {
            $previousRecipeItems=$this->RecipeItem->find('list',[
              'fields'=>'RecipeItem.id',
              'conditions'=>[
                'RecipeItem.recipe_id'=>$id,
              ],
            ]);
            if (!empty($previousRecipeItems)){
              foreach ($previousRecipeItems as $recipeItemId){
                $this->RecipeItem->id=$recipeItemId;
                if (!$this->RecipeItem->delete($recipeItemId)) {
                  echo "problema al eliminar los ingredientes anteriores";
                  pr($this->validateErrors($this->RecipeItem));
                  throw new Exception();
                } 
              }
            }
            
            $previousRecipeConsumables=$this->RecipeConsumable->find('list',[
              'fields'=>'RecipeConsumable.id',
              'conditions'=>[
                'RecipeConsumable.recipe_id'=>$id,
              ],
            ]);
            if (!empty($previousRecipeConsumables)){
              foreach ($previousRecipeConsumables as $recipeConsumableId){
                $this->RecipeConsumable->id=$recipeConsumableId;
                if (!$this->RecipeConsumable->delete($recipeConsumableId)) {
                  echo "problema al eliminar los consumibles anteriores";
                  pr($this->validateErrors($this->RecipeConsumable));
                  throw new Exception();
                } 
              }
            }
            
            $this->Recipe->id=$id;
            if (!$this->Recipe->save($this->request->data['Recipe'])) {
              echo "problema al guardar la receta";
              pr($this->validateErrors($this->Recipe));
              throw new Exception();
            } 
            $recipeId=$this->Recipe->id;
            
            foreach ($this->request->data['RecipeItem'] as $recipeItem){
              if ($recipeItem['product_id'] > 0 && $recipeItem['quantity'] >0){  
                $recipeItemArray=[
                  'RecipeItem'=>[
                    'recipe_id'=>$recipeId,
                    'product_id'=>$recipeItem['product_id'],
                    'quantity'=>$recipeItem['quantity'],
                    'unit_id'=>$recipeItem['unit_id'],
                  ]
                ];  
                $this->RecipeItem->create();
                if (!$this->RecipeItem->save($recipeItemArray)) {
                  echo "Problema guardando los ingredientes";
                  pr($this->validateErrors($this->RecipeItem));
                  throw new Exception();
                }   
              }
            }

            foreach ($this->request->data['RecipeConsumable'] as $recipeConsumable){
              if ($recipeConsumable['product_id'] > 0 && $recipeConsumable['quantity'] >0){  
                $recipeConsumableArray=[
                  'RecipeConsumable'=>[
                    'recipe_id'=>$recipeId,
                    'product_id'=>$recipeConsumable['product_id'],
                    'quantity'=>$recipeConsumable['quantity'],
                    'unit_id'=>$recipeConsumable['unit_id'],
                  ]
                ];  
                $this->RecipeConsumable->create();
                if (!$this->RecipeConsumable->save($recipeConsumableArray)) {
                  echo "Problema guardando los consumibles";
                  pr($this->validateErrors($this->RecipeConsumable));
                  throw new Exception();
                }   
              }
            }      
            $datasource->commit();
            
            $this->recordUserAction($this->Recipe->id,'editar',null);
            $this->recordUserActivity($this->Session->read('User.username'),"Se editó la receta ".$this->request->data['Recipe']['name']);
            
            $this->Session->setFlash('Se editó la receta.','default',['class' => 'success']);
            return $this->redirect(['action' => 'resumen',$recipeId]);
          } 		
          catch(Exception $e){
            $datasource->rollback();
            pr($e);					
            $this->Session->setFlash(__('The recipe could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
          }
        }
      }
    } 
    else {
			$recipe = $this->Recipe->find('first',[
        'conditions' => ['Recipe.id' => $id],
        'contain'=>[
          'RecipeItem',
          'RecipeConsumable',
        ],
      ]);
			$this->request->data = $recipe;
      
      foreach ($recipe['RecipeItem'] as $requestItem){
        if ($requestItem['product_id'] > 0 && $requestItem['quantity'] >0){
          $requestItems[]=$requestItem;
        }
      }
      foreach ($recipe['RecipeConsumable'] as $requestConsumable){
        if ($requestConsumable['product_id'] > 0 && $requestConsumable['quantity'] >0){
          $requestConsumables[]=$requestConsumable;
        }
      }
      
      $product=$this->Product->find('first',[
        'conditions'=>['Product.id'=>$recipe['Recipe']['product_id']],
        'recursive'=>-1,
      ]);
      $productionTypeId=$product['Product']['production_type_id'];
		}
    //echo 'productiontypeid is '.$productionTypeId.'<br/>';
    $this->set(compact('productionTypeId'));
	  $this->set(compact('requestItems'));
    $this->set(compact('requestConsumables'));
    
    $products = $this->Recipe->Product->getProductsByProductionType($productionTypeId,PRODUCT_NATURE_PRODUCED);
		$this->set(compact('products'));
    
    $millConversionProducts=$rawMaterials = $this->Recipe->Product->getProductsByProductionType($productionTypeId,PRODUCT_NATURE_RAW);
		$this->set(compact('rawMaterials','millConversionProducts'));
    
    $rawMaterialUnits=$this->Product->getProductUnitList(array_keys($rawMaterials));
    $this->set(compact('rawMaterialUnits'));
    
    $consumables = $this->Recipe->Product->getProductsByProductNature(PRODUCT_NATURE_BAGS);
		$this->set(compact('consumables'));
    
    $consumableUnits=$this->Product->getProductUnitList(array_keys($consumables));
    $this->set(compact('consumableUnits'));
    
    $units = $this->Unit->getUnitList();
		$this->set(compact('units'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Recipe->id = $id;
		if (!$this->Recipe->exists()) {
			throw new NotFoundException(__('Invalid recipe'));
		}
		
    $this->loadModel('RecipeItem');
    
    $this->request->allowMethod('post', 'delete');
    
    $datasource=$this->Recipe->getDataSource();
		$datasource->begin();
    
    try {
      $recipeItems=$this->RecipeItem->find('list',[
        'fields'=>'RecipeItem.id',
        'conditions'=>[
          'RecipeItem.recipe_id'=>$id,
        ],
      ]);
      if (!empty($recipeItems)){
        foreach ($recipeItems as $recipeItemId){
          $this->RecipeItem->id=$recipeItemId;
          if (!$this->RecipeItem->delete($recipeItemId)) {
            echo "problema al eliminar los ingredientes";
            pr($this->validateErrors($this->RecipeItem));
            throw new Exception();
          } 
        }
      }
          
      $this->Recipe->id=$id;
      if (!$this->Recipe->delete($id)) {
        echo "problema al eliminar la receta";
        pr($this->validateErrors($this->Recipe));
        throw new Exception();
      } 
          
      $datasource->commit();
					
      $this->recordUserAction($this->Recipe->id,'delete',null);
      $this->recordUserActivity($this->Session->read('User.username'),"Se eliminó la receta ");
          
      $this->Session->setFlash('Se eliminó la receta.','default',['class' => 'success']);
      return $this->redirect(['action' => 'resumen']);
		} 		
    catch(Exception $e){
      $datasource->rollback();
      pr($e);					
      $this->Session->setFlash('No se podía eliminar la receta.', 'default',['class' => 'error-message']);
    }
	}
}