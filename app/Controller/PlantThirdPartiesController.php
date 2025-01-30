<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class PlantThirdPartiesController extends AppController {

  public $components = ['Paginator'];
	public $helpers = ['PhpExcel'];

  public function asociarPlantasClientes($selectedPlantId=0){
		$this->loadModel('ThirdParty');
    $this->loadModel('Plant');
		$this->loadModel('Role');
		
		$this->ThirdParty->recursive=-1;
    $this->PlantThirdParty->recursive=-1;
		$this->Plant->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedThirdPartyId=0;
    $selectedPlantId=0;
		
		if ($this->request->is('post')) {
			//pr($this->request->data);
      $selectedThirdPartyId=$this->request->data['PlantThirdParty']['third_party_id'];
			$selectedPlantId=$this->request->data['PlantThirdParty']['plant_id'];
			
      if (empty($this->request->data['refresh'])){
        $currentDateTime=new DateTime();
        
        $datasource=$this->PlantThirdParty->getDataSource();
        $datasource->begin();
        try {
          foreach ($this->request->data['ThirdParty'] as $thirdPartyId=>$thirdPartyData){
            //pr($thirdPartyData);
            if ($thirdPartyData['bool_changed']){
              foreach ($thirdPartyData['Plant'] as $plantId=>$plantData){
                $plantThirdPartyArray=[
                  'PlantThirdParty'=>[
                    'third_party_id'=>$thirdPartyId,
                    'plant_id'=>$plantId,
                    'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                    'bool_assigned'=>$plantData['bool_assigned'],
                  ],
                ];    
                //pr($plantThirdPartyArray);
                $this->PlantThirdParty->create();
                if (!$this->PlantThirdParty->save($plantThirdPartyArray)){
                  echo "Problema creando la asociación entre planta y cliente";
                  pr($this->validateErrors($this->PlantThirdParty));
                  throw new Exception();
                }								
              }
            }					
          }
          $datasource->commit();
          
          $this->recordUserAction(null,'asociarPlantasClientes','plantThirdParties');
          $this->recordUserActivity($this->Session->read('User.username'),"Se asignaron clientes a plantas");
          $this->Session->setFlash(__('Se asociaron los clientes a las plantas.'),'default',['class' => 'success']);
        } 
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('No se podían asociar clientes y plantas.'), 'default',['class' => 'error-message']);
          $this->recordUserActivity($this->Session->read('User.username')," intentó asociar clientes y plantas sin éxito");
        }
        
      }
		}
		$this->set(compact('selectedThirdPartyId'));
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
      // list cannot contain
      /*
			'contain'=>[
				'PlantThirdParty'=>[
					'fields'=>[
						'PlantThirdParty.id',
						'PlantThirdParty.third_party_id',
						'PlantThirdParty.bool_assigned',
						'PlantThirdParty.assignment_datetime',
					],
					'order'=>'PlantThirdParty.assignment_datetime DESC,PlantThirdParty.id DESC',
				],
			],
      */
			'order'=>'Plant.name',
		]);
		$this->set(compact('selectedPlants'));
    //pr($selectedPlants);
    
    $thirdPartyConditions=[
      'ThirdParty.bool_provider'=>'0',
      'ThirdParty.bool_generic'=>'0',
    ];
		if (!empty($selectedThirdPartyId)){
			$thirdPartyConditions['ThirdParty.id']=$selectedThirdPartyId;
		}
    
    $selectedThirdParties=$this->ThirdParty->find('all',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>$thirdPartyConditions,
      'contain'=>[
				'PlantThirdParty'=>[
					'fields'=>[
						'PlantThirdParty.id',
						'PlantThirdParty.plant_id',
						'PlantThirdParty.bool_assigned',
						'PlantThirdParty.assignment_datetime',
					],
          'order'=>'PlantThirdParty.assignment_datetime DESC,PlantThirdParty.id DESC',
				],
			],
			'order'=>'ThirdParty.company_name',			
		]);
		//pr($selectedThirdParties);
    
    for ($tp=0;$tp<count($selectedThirdParties);$tp++){
      //pr($selectedThirdParties[$tp]);
      $selectedThirdParties[$tp]['Plant']=[];
      $plantArray=[];
      if (!empty($selectedThirdParties[$tp]['PlantThirdParty'])){
        //pr($selectedThirdParties[$tp]['PlantThirdParty']);
        foreach ($selectedPlants as $plantId=>$plantData){
          $plantArray[$plantId]=0;
          foreach ($selectedThirdParties[$tp]['PlantThirdParty'] as $plantThirdParty){
            //pr($plantThirdParty);
            if ($plantThirdParty['plant_id'] == $plantId){
              $plantArray[$plantId]=$plantThirdParty['bool_assigned'];
              break;
            }
          }
        }
      }
      $selectedThirdParties[$tp]['Plant']=$plantArray;
    }
    //pr($selectedThirdParties);
    $this->set(compact('selectedThirdParties'));
   
		$thirdParties=$this->ThirdParty->getClientList();
		$this->set(compact('thirdParties'));
		
		$plants=$this->Plant->getPlantList();
		$this->set(compact('plants'));
	}
	
	public function guardarAsociacionesPlantasClientes() {
		$exportData=$_SESSION['resumenAsociacionesPlantasClientes'];
		$this->set(compact('exportData'));
	}

  public function asociarPlantasProveedores($selectedPlantId=0){
		$this->loadModel('ThirdParty');
    $this->loadModel('Plant');
		$this->loadModel('Role');
		
		$this->ThirdParty->recursive=-1;
    $this->PlantThirdParty->recursive=-1;
		$this->Plant->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedThirdPartyId=0;
    $selectedPlantId=0;
		
		if ($this->request->is('post')) {
			//pr($this->request->data);
      $selectedThirdPartyId=$this->request->data['PlantThirdParty']['third_party_id'];
			$selectedPlantId=$this->request->data['PlantThirdParty']['plant_id'];
			
      if (empty($this->request->data['refresh'])){
        $currentDateTime=new DateTime();
        
        $datasource=$this->PlantThirdParty->getDataSource();
        $datasource->begin();
        try {
          foreach ($this->request->data['ThirdParty'] as $thirdPartyId=>$thirdPartyData){
            //pr($thirdPartyData);
            if ($thirdPartyData['bool_changed']){
              foreach ($thirdPartyData['Plant'] as $plantId=>$plantData){
                $plantThirdPartyArray=[
                  'PlantThirdParty'=>[
                    'third_party_id'=>$thirdPartyId,
                    'plant_id'=>$plantId,
                    'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                    'bool_assigned'=>$plantData['bool_assigned'],
                  ],
                ];    
                //pr($plantThirdPartyArray);
                $this->PlantThirdParty->create();
                if (!$this->PlantThirdParty->save($plantThirdPartyArray)){
                  echo "Problema creando la asociación entre planta y proveedor";
                  pr($this->validateErrors($this->PlantThirdParty));
                  throw new Exception();
                }								
              }
            }					
          }
          $datasource->commit();
          
          $this->recordUserAction(null,'asociarPlantasProveedores','plantThirdParties');
          $this->recordUserActivity($this->Session->read('User.username'),"Se asignaron proveedores a plantas");
          $this->Session->setFlash(__('Se asociaron los proveedores a las plantas.'),'default',['class' => 'success']);
        } 
        catch(Exception $e){
          $datasource->rollback();
          pr($e);
          $this->Session->setFlash(__('No se podían asociar proveedores y plantas.'), 'default',['class' => 'error-message']);
          $this->recordUserActivity($this->Session->read('User.username')," intentó asociar proveedores y plantas sin éxito");
        }
        
      }
		}
		$this->set(compact('selectedThirdPartyId'));
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
      'order'=>'Plant.name',
		]);
		$this->set(compact('selectedPlants'));
    //pr($selectedPlants);
    
    $thirdPartyConditions=[
      'ThirdParty.bool_provider'=>true,
      'ThirdParty.bool_generic'=>'0',
    ];
		if (!empty($selectedThirdPartyId)){
			$thirdPartyConditions['ThirdParty.id']=$selectedThirdPartyId;
		}
    
    $selectedThirdParties=$this->ThirdParty->find('all',[
			'fields'=>['ThirdParty.id','ThirdParty.company_name'],
			'conditions'=>$thirdPartyConditions,
      'contain'=>[
				'PlantThirdParty'=>[
					'fields'=>[
						'PlantThirdParty.id',
						'PlantThirdParty.plant_id',
						'PlantThirdParty.bool_assigned',
						'PlantThirdParty.assignment_datetime',
					],
          'order'=>'PlantThirdParty.assignment_datetime DESC,PlantThirdParty.id DESC',
				],
			],
			'order'=>'ThirdParty.company_name',			
		]);
		//pr($selectedThirdParties);
    
    for ($tp=0;$tp<count($selectedThirdParties);$tp++){
      //pr($selectedThirdParties[$tp]);
      $selectedThirdParties[$tp]['Plant']=[];
      $plantArray=[];
      if (!empty($selectedThirdParties[$tp]['PlantThirdParty'])){
        //pr($selectedThirdParties[$tp]['PlantThirdParty']);
        foreach ($selectedPlants as $plantId=>$plantData){
          $plantArray[$plantId]=0;
          foreach ($selectedThirdParties[$tp]['PlantThirdParty'] as $plantThirdParty){
            //pr($plantThirdParty);
            if ($plantThirdParty['plant_id'] == $plantId){
              $plantArray[$plantId]=$plantThirdParty['bool_assigned'];
              break;
            }
          }
        }
      }
      $selectedThirdParties[$tp]['Plant']=$plantArray;
    }
    //pr($selectedThirdParties);
    $this->set(compact('selectedThirdParties'));
   
		$thirdParties=$this->ThirdParty->getActiveProviderList();
		$this->set(compact('thirdParties'));
		
		$plants=$this->Plant->getPlantList();
		$this->set(compact('plants'));
	}
	
	public function guardarAsociacionesPlantasProveedores() {
		$exportData=$_SESSION['resumenAsociacionesPlantasProveedores'];
		$this->set(compact('exportData'));
	}
}