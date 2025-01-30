<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class ThirdPartyUsersController extends AppController {

	public $components = ['Paginator'];
	public $helpers =['PhpExcel'];  
  
  public function asociarClientesUsuarios($selectedUserId=0){
    $this->loadModel('Plant');
		$this->loadModel('ThirdParty');
		$this->loadModel('User');
		
		$this->request->allowMethod('get','post', 'put');
		$users=$this->User->getActiveUserList();
    $clientOwners=$this->ThirdParty->getClientOwnerList();
		$plants=$this->Plant->getPlantList();
    $this->set(compact('clientOwners','plants'));
    
    define('CLIENT_OPTION_ALL',0);
    define('CLIENT_OPTION_ACTIVE',1);
    $clientOptions=[
      CLIENT_OPTION_ALL=>'Todos clientes',
      CLIENT_OPTION_ACTIVE=>'Clientes activos',
    ];
    $this->set(compact('clientOptions'));
		//$selectedClientId=0;
    $plantId=0;
    $clientOptionId=CLIENT_OPTION_ACTIVE;
    
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$selectedUserId=$this->request->data['ThirdPartyUser']['user_id'];
			$plantId=$this->request->data['ThirdPartyUser']['plant_id'];
      $clientOptionId=$this->request->data['ThirdPartyUser']['client_option_id'];
			if (!empty($this->request->data['refresh'])){
        //$this->redirect(array('action' => 'asociarClientesUsuarios',$selectedClientId, 'page' => 1));
      }
      else {
				$currentDateTime=new DateTime();
				$datasource=$this->ThirdPartyUser->getDataSource();
				$datasource->begin();
				try {
					foreach ($this->request->data['Client'] as $clientId=>$clientData){
						if ($clientData['bool_changed']){
              // pr($clientData);
              $thirdPartyUserArray=[
                'third_party_id'=>$clientId,
                'user_id'=>$selectedUserId,
                'assignment_datetime'=>$currentDateTime->format('Y-m-d H:i:s'),
                'bool_assigned'=>$clientData['User'][$selectedUserId]['bool_association'],
              ];
              // pr($thirdPartyUserArray);
              $this->ThirdPartyUser->create();
              if (!$this->ThirdPartyUser->save($thirdPartyUserArray)){
                echo "Problema creando la asociación entre cliente y vendedor";
                pr($this->validateErrors($this->ThirdPartyUser));
                throw new Exception();
              } 
              if ($clientData['User'][$selectedUserId]['bool_owner']){
                $clientArray=[
                  'id'=>$clientId,
                  'owner_user_id'=>$selectedUserId,
                ];
                // pr($clientArray);
                $this->ThirdParty->id=$clientId;
                if (!$this->ThirdParty->save($clientArray)){
                  echo "Problema actualizando el responsable principal del cliente";
                  pr($this->validateErrors($this->ThirdParty));
                  throw new Exception();
                }  
              }              
            }					
					}
					$datasource->commit();
					
          $logMessage="Se establecieron las asociaciones entre los clientes y el usuario ".$users[$selectedUserId].".";
					$this->recordUserAction(null,'asociarClientesUsuarios','thirdPartyUsers');
					$this->recordUserActivity($this->Session->read('User.username'),$logMessage);
					$this->Session->setFlash($logMessage,'default',['class' => 'success']);
					return $this->redirect(['action' => 'asociarClientesUsuarios']);
					//return $this->redirect($this->referer());
				} 
				catch(Exception $e){
					$datasource->rollback();
					pr($e);
					$this->Session->setFlash(__('No se podían guardar las asociaciones.'), 'default',['class' => 'error-message']);
					$this->recordUserActivity($this->Session->read('User.username')," intentó asignar clientes sin éxito");
				}
			}
		}
		$this->set(compact('selectedUserId'));
    $this->set(compact('plantId'));
    $this->set(compact('clientOptionId'));
		
    $users=$this->User->getActiveVendorAdminUserListForPlant($plantId);
		$this->set(compact('users'));   
		//pr($selectedUsers);
		$clientConditions=[
      'ThirdParty.bool_provider'=>'0',
		];
    if ($clientOptionId == CLIENT_OPTION_ACTIVE){
      $clientConditions['ThirdParty.bool_active']=true;
    }
    if ($plantId >0){
      $this->loadModel('PlantThirdParty');
      $clientsForPlant = $this->PlantThirdParty->getClientsForPlant($plantId);
      $clientConditions['ThirdParty.id']=array_keys($clientsForPlant);
    }
		$selectedClients=$this->ThirdParty->find('all',[
			'fields'=>[
				'ThirdParty.id',
				'ThirdParty.company_name',
			],
			'conditions'=>$clientConditions,
			'contain'=>[
				'ThirdPartyUser'=>[
					'fields'=>[
						'ThirdPartyUser.id',
						'ThirdPartyUser.user_id',
						'ThirdPartyUser.bool_assigned',
						'ThirdPartyUser.assignment_datetime',
					],
          'order'=>'ThirdPartyUser.id DESC',
				],
			],
			'order'=>'ThirdParty.company_name',
		]);
    $clientUserAssociations=[];
		for ($c=0;$c<count($selectedClients);$c++){
      //if ($selectedClients[$c]['ThirdParty']['id'] == 68){
        //pr($selectedClients[$c]);
      //}
			$userArray=[];
			foreach ($users as $currentUserId=>$userValue){
        $userArray[$currentUserId]=0;
        foreach ($selectedClients[$c]['ThirdPartyUser'] as $clientUser){
          if ($clientUser['user_id'] == $currentUserId){
            //if ($currentUserId == 10){
              //pr($clientUser);
            //}
            $userArray[$currentUserId]=$clientUser['bool_assigned'];
            break;
          }
        }
        //if ($currentUserId == 10 && $selectedClients[$c]['ThirdParty']['id'] == 68){
        //  pr($userArray);
        //}
      }
      if ($selectedClients[$c]['ThirdParty']['id'] == 68){
        //pr($userArray);
      }
			$clientUserAssociations[$selectedClients[$c]['ThirdParty']['id']]['Users']=$userArray;
		}
		$this->set(compact('clientUserAssociations'));
		//pr($clientUserAssociations);
		
    switch ($clientOptionId){
      case  CLIENT_OPTION_ACTIVE:
        $clients=$this->ThirdParty->getActiveClientList();
        break;
      default:
        $clients=$this->ThirdParty->getClientList();
    }
    $this->set(compact('clients'));
		// pr($clients);
	}
	
	public function guardarAsociacionesClientesUsuarios() {
		$exportData=$_SESSION['resumenAsociacionesClientesUsuarios'];
		$this->set(compact('exportData'));
	}
}
