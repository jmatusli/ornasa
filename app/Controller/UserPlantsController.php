<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class UserPlantsController extends AppController {

	public $components = ['Paginator'];
	public $helpers = ['PhpExcel'];

  public function asociarUsuariosPlantas($selectedPlantId=0){
		$this->loadModel('User');
    $this->loadModel('Plant');
		$this->loadModel('Role');
		
		$this->User->recursive=-1;
    $this->UserPlant->recursive=-1;
		$this->Plant->recursive=-1;
		
		$this->request->allowMethod('get','post', 'put');
		
    $selectedRoleId=0;
    $selectedUserId=0;
    $selectedPlantId=0;
		
		if ($this->request->is('post')) {
			//pr($this->request->data);
      $selectedRoleId=$this->request->data['UserPlant']['role_id'];
			$selectedUserId=$this->request->data['UserPlant']['user_id'];
			$selectedPlantId=$this->request->data['UserPlant']['plant_id'];
			
      $currentDateTime=new DateTime();
      $datasource=$this->UserPlant->getDataSource();
      $datasource->begin();
      try {
        foreach ($this->request->data['User'] as $userId=>$userValue){
          //pr($userValue);
          if ($userValue['bool_changed']){
            foreach ($userValue['Plant'] as $plantId=>$plantValue){
              $userPlantArray=[];
              $userPlantArray['UserPlant']['user_id']=$userId;
              $userPlantArray['UserPlant']['plant_id']=$plantId;
              $userPlantArray['UserPlant']['assignment_datetime']=$currentDateTime->format('Y-m-d H:i:s');
              $userPlantArray['UserPlant']['bool_assigned']=$plantValue['bool_assigned'];
              //pr($userPlantArray);
              $this->UserPlant->create();
              if (!$this->UserPlant->save($userPlantArray)){
                echo "Problema creando la asociación entre usuario y planta";
                pr($this->validateErrors($this->UserPlant));
                throw new Exception();
              }								
            }
          }					
        }
        $datasource->commit();
        
        $this->recordUserAction(null,'asociarUsuariosPlantas','userPlants');
        $this->recordUserActivity($this->Session->read('User.username'),"Se asignaron usuarios a plantas");
        $this->Session->setFlash(__('Se asociaron los usuarios a las plantas.'),'default',['class' => 'success']);
      } 
      catch(Exception $e){
        $datasource->rollback();
        pr($e);
        $this->Session->setFlash(__('No se podían asociar usuarios y plantas.'), 'default',['class' => 'error-message']);
        $this->recordUserActivity($this->Session->read('User.username')," intentó asociar usuarios y plantas sin éxito");
      }
    
		}
		$this->set(compact('selectedRoleId'));
		$this->set(compact('selectedUserId'));
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
				'UserPlant'=>[
					'fields'=>[
						'UserPlant.id',
						'UserPlant.user_id',
						'UserPlant.bool_assigned',
						'UserPlant.assignment_datetime',
					],
					'order'=>'UserPlant.assignment_datetime DESC,UserPlant.id DESC',
				],
			],
			'order'=>'Plant.name',
		]);
		$this->set(compact('selectedPlants'));
    
    $userConditions=['User.bool_active'=>true];
		if (!empty($selectedUserId)){
			$userConditions['User.id']=$selectedUserId;
		}
    $selectedUsers=$this->User->find('all',[
			'fields'=>['User.id','User.username'],
			'conditions'=>$userConditions,
      'contain'=>[
				'UserPlant'=>[
					'fields'=>[
						'UserPlant.id',
						'UserPlant.user_id',
						'UserPlant.bool_assigned',
						'UserPlant.assignment_datetime',
					],
					'order'=>'UserPlant.assignment_datetime DESC,UserPlant.id DESC',
				],
			],
			'order'=>'User.username',			
		]);
		$this->set(compact('selectedUsers'));
		//pr($selectedUsers);
    
    $roleConditions=[];
		if (!empty($selectedRoleId)){
			$roleConditions['Role.id']=$selectedRoleId;
		}
    $selectedRoles=$this->Role->find('all',[
			'fields'=>['Role.id','Role.name'],
			'conditions'=>$roleConditions,
      'contain'=>[
        'User'=>[
          'conditions'=>$userConditions,
          'order'=>'User.username',		
          'UserPlant'=>[
            'fields'=>[
              'UserPlant.id',
              'UserPlant.user_id',
              'UserPlant.plant_id',
              'UserPlant.bool_assigned',
              'UserPlant.assignment_datetime',
            ],
            'order'=>'UserPlant.assignment_datetime DESC,UserPlant.id DESC',
          ],
        ],
      ],
      'order'=>'Role.list_order',			
		]);
		$this->set(compact('selectedUsers'));
		//pr($selectedUsers);
    
    
		for ($r=0;$r<count($selectedRoles);$r++){
      for ($u=0;$u<count($selectedRoles[$r]['User']);$u++){
        //pr($selectedUsers[$c]);
        $selectedRoles[$r]['User'][$u]['Plant']=[];
        $plantArray=[];
        if (!empty($selectedRoles[$r]['User'][$u]['UserPlant'])){
          foreach ($selectedPlants as $plantId=>$plantValue){
            $plantArray[$plantId]=0;
            foreach ($selectedRoles[$r]['User'][$u]['UserPlant'] as $userPlant){
              //pr($userPlant);
              if ($userPlant['plant_id']==$plantId){
                $plantArray[$plantId]=$userPlant['bool_assigned'];
                break;
              }
            }
          }
        }
        $selectedRoles[$r]['User'][$u]['Plant']=$plantArray;
      }
    }
		$this->set(compact('selectedUsers'));
    $this->set(compact('selectedRoles'));
		//pr($selectedRoles);
		
    $roles=$this->Role->getRoles();
		$this->set(compact('roles'));
    
		$users=$this->User->find('list',[
			'fields'=>[
				'User.id',
				'User.username',
			],
			'order'=>'User.username',			
		]);
		$this->set(compact('users'));
		
		$plants=$this->Plant->find('list',[
			'fields'=>[
				'Plant.id',
				'Plant.name',
			],
			'conditions'=>[
				'Plant.bool_active'=>true,
			],
			'order'=>'Plant.name',
		]);
		$this->set(compact('plants'));
	}
	
	public function guardarUsuariosPlantas() {
		$exportData=$_SESSION['resumenAsociacionesUsuariosPlantas'];
		$this->set(compact('exportData'));
	}

}
