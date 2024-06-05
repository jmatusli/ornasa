<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class PlantProductTypesController extends AppController {

  public $components = ['Paginator'];
	public $helpers = ['PhpExcel'];

  public function asociarPlantasTiposDeProducto($selectedPlantId=0){
		$this->loadModel('ProductType');
    $this->loadModel('Plant');
		$this->loadModel('Role');
		
		$this->ProductType->recursive=-1;
    $this->PlantProductType->recursive=-1;
		$this->Plant->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedProductTypeId=0;
    $selectedPlantId=0;
		
		if ($this->request->is('post')) {
			//pr($this->request->data);
      $selectedProductTypeId=$this->request->data['PlantProductType']['product_type_id'];
			$selectedPlantId=$this->request->data['PlantProductType']['plant_id'];
			
      $currentDateTime=new DateTime();
      $datasource=$this->PlantProductType->getDataSource();
      $datasource->begin();
      try {
        foreach ($this->request->data['ProductType'] as $productTypeId=>$productTypeValue){
          //pr($productTypeValue);
          if ($productTypeValue['bool_changed']){
            foreach ($productTypeValue['Plant'] as $plantId=>$plantValue){
              $plantProductionTypeArray=[
                'PlantProductType'=>[
                  'product_type_id'=>$productTypeId,
                  'plant_id'=>$plantId,
                  'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                  'bool_assigned'=>$plantValue['bool_assigned'],
                ],
              ];    
              //pr($plantProductionTypeArray);
              $this->PlantProductType->create();
              if (!$this->PlantProductType->save($plantProductionTypeArray)){
                echo "Problema creando la asociación entre planta y tipo de producto";
                pr($this->validateErrors($this->PlantProductType));
                throw new Exception();
              }								
            }
          }					
        }
        $datasource->commit();
        
        $this->recordUserAction(null,'asociarPlantasTiposDeProducto','plantProductTypes');
        $this->recordUserActivity($this->Session->read('User.username'),"Se asignaron tipos de producto a plantas");
        $this->Session->setFlash(__('Se asociaron los tipos de producto a las plantas.'),'default',['class' => 'success']);
      } 
      catch(Exception $e){
        $datasource->rollback();
        pr($e);
        $this->Session->setFlash(__('No se podían asociar tipos de producto y plantas.'), 'default',['class' => 'error-message']);
        $this->recordUserActivity($this->Session->read('User.username')," intentó asociar tipos de producto y plantas sin éxito");
      }
    
		}
		$this->set(compact('selectedProductTypeId'));
		$this->set(compact('selectedPlantId'));
		
    $plantConditions=[
			'Plant.bool_active'=>true,     
		];
		if (!empty($selectedPlantId)){
			$plantConditions['Plant.id']=$selectedPlantId;
		}
    
    $selectedPlants=$this->Plant->find('list',[
			'fields'=>[
				'Plant.id',
				'Plant.name',
			],
			'conditions'=>$plantConditions,
			'contain'=>[
				'PlantProductType'=>[
					'fields'=>[
						'PlantProductType.id',
						'PlantProductType.product_type_id',
						'PlantProductType.bool_assigned',
						'PlantProductType.assignment_datetime',
					],
					'order'=>'PlantProductType.assignment_datetime DESC,PlantProductType.id DESC',
				],
			],
			'order'=>'Plant.name',
		]);
		$this->set(compact('selectedPlants'));
    
    $productTypeConditions=[];
		if (!empty($selectedProductTypeId)){
			$productTypeConditions['ProductType.id']=$selectedProductTypeId;
		}
    $selectedProductTypes=$this->ProductType->find('all',[
			'fields'=>['ProductType.id','ProductType.name'],
			'conditions'=>$productTypeConditions,
      'contain'=>[
				'PlantProductType'=>[
					'fields'=>[
						'PlantProductType.id',
						'PlantProductType.plant_id',
						'PlantProductType.bool_assigned',
						'PlantProductType.assignment_datetime',
					],
					'order'=>'PlantProductType.assignment_datetime DESC,PlantProductType.id DESC',
				],
			],
			'order'=>'ProductType.name',			
		]);
		$this->set(compact('selectedProductTypes'));
		//pr($selectedProductTypes);
    
    for ($pt=0;$pt<count($selectedProductTypes);$pt++){
      //pr($selectedProductTypes[$c]);
      $selectedProductTypes[$pt]['Plant']=[];
      $plantArray=[];
      if (!empty($selectedProductTypes[$pt]['PlantProductType'])){
        foreach ($selectedPlants as $plantId=>$plantValue){
          $plantArray[$plantId]=0;
          foreach ($selectedProductTypes[$pt]['PlantProductType'] as $plantProductType){
            //pr($plantProductType);
            if ($plantProductType['plant_id']==$plantId){
              $plantArray[$plantId]=$plantProductType['bool_assigned'];
              break;
            }
          }
        }
      }
      $selectedProductTypes[$pt]['Plant']=$plantArray;
    }
    $this->set(compact('selectedProductTypes'));
   
		$productTypes=$this->ProductType->find('list',[
			'fields'=>[
				'ProductType.id',
				'ProductType.name',
			],
			'order'=>'ProductType.name',			
		]);
		$this->set(compact('productTypes'));
		
		$plants=$this->Plant->getPlantList();
		$this->set(compact('plants'));
	}
	
	public function guardarAsociacionesPlantasTiposDeProducto() {
		$exportData=$_SESSION['resumenAsociacionesPlantasTiposDeProducto'];
		$this->set(compact('exportData'));
	}

}