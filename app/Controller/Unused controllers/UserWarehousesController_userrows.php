<?php
App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'PHPExcel')));
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class UserWarehousesController extends AppController {

	public $components = array('Paginator');
	public $helpers = array('PhpExcel');

  public function asociarUsuariosBodegas($selectedWarehouseId=0){
		$this->loadModel('User');
    $this->loadModel('Warehouse');
		
		
		$this->User->recursive=-1;
    $this->UserWarehouse->recursive=-1;
		$this->Warehouse->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedWarehouseId=0;
		$selectedUserId=0;
   
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$selectedUserId=$this->request->data['UserWarehouse']['user_id'];
			$selectedWarehouseId=$this->request->data['UserWarehouse']['warehouse_id'];
			
			if (!empty($this->request->data['refresh'])){
        //$this->redirect(array('action' => 'asociarWarehouseesUsuarios',$selectedWarehouseId, 'page' => 1));
      }
      else {
				$currentDateTime=new DateTime();
				$datasource=$this->UserWarehouse->getDataSource();
				$datasource->begin();
				try {
					foreach ($this->request->data['User'] as $userId=>$userValue){
						//pr($userValue);
						if ($userValue['bool_changed']){
							foreach ($userValue['Warehouse'] as $warehouseId=>$warehouseValue){
								$userWarehouseArray=[];
								$userWarehouseArray['UserWarehouse']['user_id']=$userId;
                $userWarehouseArray['UserWarehouse']['warehouse_id']=$warehouseId;
								$userWarehouseArray['UserWarehouse']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
								$userWarehouseArray['UserWarehouse']['bool_assigned']=$userValue['bool_assigned'];
								//pr($userWarehouseArray);
								$this->UserWarehouse->create();
								if (!$this->UserWarehouse->save($userWarehouseArray)){
									echo "Problema creando la asociación entre usuario y bodega";
									pr($this->validateErrors($this->UserWarehouse));
									throw new Exception();
								}								
							}
						}					
					}
					$datasource->commit();
					
					$this->recordUserAction(null,'asociarUsuariosBodegas','userWarehouses');
					$this->recordUserActivity($this->Session->read('User.username'),"Se asignaron usuarios a bodegas");
					$this->Session->setFlash(__('Se asociaron los usuarios a las bodegas.'),'default',['class' => 'success']);
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían asociar usuarios y bodegas.'), 'default',['class' => 'error-message']);
					$this->recordUserActivity($this->Session->read('User.username')," intentó asociar usuarios y bodegas sin éxito");
				}
			}
		}
		
		$this->set(compact('selectedUserId'));
		$this->set(compact('selectedWarehouseId'));
		
    $warehouseConditions=[
			'Warehouse.bool_active'=>true,     
		];
		if (!empty($selectedWarehouseId)){
			$warehouseConditions['Warehouse.id']=$selectedWarehouseId;
		}
    
    $selectedWarehouses=$this->Warehouse->find('list',[
			'fields'=>[
				'Warehouse.id',
				'Warehouse.name',
			],
			'conditions'=>$warehouseConditions,
			'contain'=>[
				'UserWarehouse'=>[
					'fields'=>[
						'UserWarehouse.id',
						'UserWarehouse.user_id',
						'UserWarehouse.bool_assigned',
						'UserWarehouse.assignment_datetime',
					],
					'order'=>'UserWarehouse.assignment_datetime DESC,UserWarehouse.id DESC',
				],
			],
			'order'=>'Warehouse.name',
		]);
		$this->set(compact('selectedWarehouses'));
    
    
    
    $userConditions=['User.bool_active'=>true];
		if (!empty($selectedUserId)){
			$userConditions['User.id']=$selectedUserId;
		}
    $selectedUsers=$this->User->find('all',[
			'fields'=>['User.id','User.username'],
			'conditions'=>$userConditions,
      'contain'=>[
				'UserWarehouse'=>[
					'fields'=>[
						'UserWarehouse.id',
						'UserWarehouse.user_id',
						'UserWarehouse.bool_assigned',
						'UserWarehouse.assignment_datetime',
					],
					'order'=>'UserWarehouse.assignment_datetime DESC,UserWarehouse.id DESC',
				],
			],
			'order'=>'User.username',			
		]);
		$this->set(compact('selectedUsers'));
		//pr($selectedUsers);
		
		for ($c=0;$c<count($selectedUsers);$c++){
      //pr($selectedUsers[$c]);
      $selectedUsers[$c]['Warehouse']=[];
			$warehouseArray=[];
			if (!empty($selectedUsers[$c]['UserWarehouse'])){
				foreach ($selectedWarehouses as $warehouseId=>$warehouseValue){
					$warehouseArray[$warehouseId]=0;
					foreach ($selectedUsers[$c]['UserWarehouse'] as $userWarehouse){
						if ($userWarehouse['warehouse_id']==$warehouseId){
							$warehouseArray[$warehouseId]=$userWarehouse['bool_assigned'];
							break;
						}
					}
				}
			}
			$selectedUsers[$c]['Warehouse']=$warehouseArray;
		}
		$this->set(compact('selectedUsers'));
		//pr($warehouses);
		
		$users=$this->User->find('list',[
			'fields'=>[
				'User.id',
				'User.username',
			],
			'order'=>'User.username',			
		]);
		$this->set(compact('users'));
		
		$warehouses=$this->Warehouse->find('list',[
			'fields'=>[
				'Warehouse.id',
				'Warehouse.name',
			],
			'conditions'=>[
				'Warehouse.bool_active'=>true,
			],
			'order'=>'Warehouse.name',
		]);
		$this->set(compact('warehouses'));
	}
	
	public function guardarUsuariosBodegsa() {
		$exportData=$_SESSION['resumenAsociacionesUsuariosBodegas'];
		$this->set(compact('exportData'));
	}





}
