<?php
App::uses('AppController', 'Controller');

class UsersController extends AppController {

	public $components = array('Paginator');

	public function beforeFilter() {
		parent::beforeFilter();
		
		$this->Auth->allow('login','logout');		

		// Allow users to register and logout.
		//$this->Auth->allow('add','logout');		
	}
	
	public function login() {
		if ($this->request->is('post')) {
			if ($this->Auth->login()) {
				/*
				print "<pre>";
				echo "<p>Auth->user</p>";
				print_r($this->Auth->user);
				print "</pre>";
				*/
				$this->recordUserActivity($this->data['User']['username'],"Login successful");
				$this->Session->write('User.username',$this->data['User']['username']);
				$this->Session->write('User.userid',$this->Auth->User('id'));
				
				//$userid = $this->Auth->User('id');
				//echo "user id ".$userid."!<br/>";
				$role = $this->Auth->User('role_id');
				//echo "role id ".$role."!<br/>";
				return $this->redirect(parent::userhome($role));
			}
			$this->recordUserActivity($this->data['User']['username'],"Invalid username or password");
			$this->Session->setFlash(__('Invalid username or password, try again'));
		}
	}

	public function logout() {
		$this->recordUserActivity($this->Session->read('User.username'),"Logout");
		return $this->redirect($this->Auth->logout());
	}	

	public function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->Paginator->paginate());
	}

	public function view($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		
		if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
		}		
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
			if ($this->Session->check('currencyId')){
				$currencyId=$_SESSION['currencyId'];
			}
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		
		
		$user= $this->User->find('first', array(
			'conditions' => array(
				'User.id' => $id,
			),
			'contain'=>array(
				'Role',
				'UserLog'=>array(
					'conditions'=>array(
						'event LIKE '=>'%Log%',
						'created >='=>$startDate,
						'created <'=>$endDatePlusOne,
					),
					'order'=>'created DESC',
				),
			),
		));
		$this->set(compact('user'));
	}
	
	public function add() {
		if ($this->request->is('post')) {
			$this->User->create();
			if ($this->User->save($this->request->data)) {
				$this->recordUserAction($this->User->id,null,null);
				$this->Session->setFlash(__('The user has been saved.'),'default',array('class' => 'success'));
				$this->recordUserActivity($this->Session->read('User.username'),"Added new user ".$this->request->data['User']['username']);
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
				$this->recordUserActivity($this->Session->read('User.username'),"Tried to add user unsuccessfully");
			}
		}
		$roles = $this->User->Role->find('list');
		$this->set(compact('roles'));
		
	}

	public function edit($id = null) {
		if (!$this->User->exists($id)) {
			throw new NotFoundException(__('Invalid user'));
		}
		
		$this->User->recursive=-1;
		
		if ($this->request->is(array('post', 'put'))) {
			$this->User->id=$id;
			$this->request->data['User']['password']=$this->request->data['User']['pwd'];
			if ($this->User->save($this->request->data['User'])) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The user has been saved.'),'default',array('class' => 'success'));
				$this->recordUserActivity($this->Session->read('User.username'),"Edited user ".$this->request->data['User']['username']);
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->recordUserActivity($this->Session->read('User.username'),"Tried to edit user ".$this->request->data['User']['username']." without success");
				$this->Session->setFlash(__('The user could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} 
		else {
			$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
			$this->request->data = $this->User->find('first', $options);
		}
		$options = array('conditions' => array('User.' . $this->User->primaryKey => $id));
		$this->request->data = $this->User->find('first', $options);
		//echo "now printing the request data that come from the database<br/>";
		//pr($this->request->data);
		$roles = $this->User->Role->find('list');
		$this->set(compact('roles'));
		
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->User->id = $id;
		if (!$this->User->exists()) {
			throw new NotFoundException(__('Invalid user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->User->delete()) {
			$this->Session->setFlash(__('The user has been deleted.'));
		} else {
			$this->Session->setFlash(__('The user could not be deleted. Please, try again.'));
		}
		$this->recordUserActivity($this->Session->read('User.username'),"Deleted user with id ".$id);
		return $this->redirect(array('action' => 'index'));
	}

	public function rolePermissions(){
		$this->loadModel('Role');
		
		$roles=$this->Role->find('all',array(
		));
		//pr($roles);
		$this->set(compact('roles'));
		
		$consideredControllerAliases=array(
			'Orders',
			'ProductionRuns',
			'StockItems',
			//'Invoices',
			
			'ClosingDates',
			
			'CashReceipts',
			'Cheques',
			'Transfers',
			'ExchangeRates',
			'AccountingCodes',
			'AccountingRegisterTypes',
			'AccountingRegisters',
			
			//'PaymentModes',
			
			'ProductTypes',
			'Products',
			'ThirdParties',
			//'ProductCategories',
			
			//'Clients',
			//'Contacts',
			
			'Machines',	
			'Operators',
			'Shifts',
			'Warehouses',
			
			'Users',
			'Employees',
			'EmployeeHolidays',
			'HolidayTypes',
		);
		
		$selectedControllers=$this->Acl->Aco->find('all',array(
			'conditions'=>array(
				'Aco.parent_id'=>'1',
				'Aco.alias'=>$consideredControllerAliases,
			),
		));
		//pr($selectedControllers);
		
		$excludedActions=array(
			'controllers',
			'recordUserActivity',
			'userhome',
			'get_date',
			'recordUserAction',
			'uploadFiles',
			'hasPermission',
			'recreateStockItemLogs',
			'saveAccountingRegisterData',
			'normalizeChars',
			
			'create_pdf', // for orders
			'downloadPdf', // for orders
			'sortByTotalForClient', // for orders
			'setduedate', // for orders
			
			'getrawmaterialid', // for production runs
			
			'compareArraysByDate', // for products
			'getproductcategoryid', // for products
			'getproductpackagingunit', // for products
			'getmachines', // for products
			
			'cuadrarEstadosDeLote',// for stock items
			'recreateStockItemLogsForSquaring',// for stock items
			'recreateAllStockItemLogs',// for stock items
			'recreateAllBottleCosts',// for stock items
			'recreateStockItemPriceForSquaring',// for stock items
			'cuadrarPreciosBotellas',// for stock items
			'recreateProductionMovementPriceForSquaring',// for stock items
			'sortByFinishedProduct',// for stock items
			'sortByRawMaterial',// for stock items
			
			'getcreditdays',// for third parties
			
			'getaccountsaldo',// for accounting codes
			'getaccountingcodenatura',// for accounting codes
			'indexOriginal',
			'viewOriginal',// for accounting codes
			'loadCodes',// for accounting codes
			'getaccountingcodeforparent',// for accounting codes
			'getaccountingcodename',// for accounting codes
			
			'getaccountingregistercode', // for accounting registers
			'cuadrarAccountingRegisters', // for accounting registers
			'guardarResumenAsientosContablesProblemas', // for accounting registers
			'getaccountingcodedescription', // for accounting registers
			'calculateResultState', // for accounting registers
			
			'getchequenumber', // for cheques
			
			'getexchangerate', // for exchange rates
			
			'login', // for users
			'logout', // for users
			'init_DB_permissions',
			'rolePermissions',
		);
		
		for ($c=0;$c<count($selectedControllers);$c++){
			$selectedActions=array();
			$selectedActions=$this->Acl->Aco->find('all',array(
				'conditions'=>array(
					'Aco.parent_id'=>$selectedControllers[$c]['Aco']['id'],
					'Aco.alias !='=>$excludedActions,
				),
			));
			if (!empty($selectedActions)){
				for ($a=0;$a<count($selectedActions);$a++){
					$rolePermissions=array();
					for ($r=0;$r<count($roles);$r++){
						$aco_name=$selectedControllers[$c]['Aco']['alias']."/".$selectedActions[$a]['Aco']['alias'];
						//pr($aco_name);
						$hasPermission=$this->Acl->check(array('Role'=>array('id'=>$roles[$r]['Role']['id'])),$aco_name);
						//if ($selectedActions[$a]['Aco']['id']==15){
						//	echo "permission for ".$aco_name." is ".$hasPermission."<br/>";
						//}
						if ($hasPermission){
							$rolePermissions[$r]=$hasPermission;
						}
						else {
							$rolePermissions[$r]=0;
						}						
					}
					//if ($selectedActions[$a]['Aco']['id']==15){
					//	pr($rolePermissions);
					//}
					$selectedActions[$a]['rolePermissions']=$rolePermissions;
				}
			}
			//pr($selectedActions);
			
			$selectedControllers[$c]['actions']=$selectedActions;
		}
		$this->set(compact('selectedControllers'));
		//pr($selectedControllers);
		if ($this->request->is('post')) {
			//pr($this->request->data);
			$role = $this->User->Role;
			for ($r=0;$r<count($this->request->data['Role']);$r++){
				$thisRole=$roles[$r];
				//pr($role);
				$role_id=$thisRole['Role']['id'];
				
				$role->id=$role_id;
				
				for ($c=0;$c<count($this->request->data['Role'][$r]['Controller']);$c++){
					$controller=$selectedControllers[$c];
					//pr($controller);
					$controller_alias=$controller['Aco']['alias'];
					if ($controller['Aco']['id']==803){
						//pr($this->request->data['Role'][$r]['Controller'][$c]);
						//pr($controller);
					}
					for ($a=0;$a<count($this->request->data['Role'][$r]['Controller'][$c]['Action']);$a++){
						//pr($this->request->data['Role'][$r]['Controller'][$c]['Action'][$a]);
						
						$action=$selectedControllers[$c]['actions'][$a];
						//pr($action);
						$action_alias=$action['Aco']['alias'];
						//pr($action_alias);
						
						if ($this->request->data['Role'][$r]['Controller'][$c]['Action'][$a]){
							$this->Acl->allow($role, 'controllers/'.$controller_alias."/".$action_alias);
						}
						else {
							$this->Acl->deny($role, 'controllers/'.$controller_alias."/".$action_alias);
						}
						$this->Session->setFlash(__('Los permisos se guardaron.'),'default',array('class' => 'success'));
						//$role->id = 5;
						//$this->Acl->allow($role, 'controllers');
						//$this->Acl->deny($role, 'controllers/ProductionResultCodes');
						//$this->Acl->deny($role, 'controllers/StockMovementTypes');
						//$this->Acl->deny($role, 'controllers/Role');			
					}					
				}				
			}
			/*
			$this->Client->create();
			if ($this->Client->save($this->request->data)) {
				$this->Session->setFlash(__('The client has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The client could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
			*/
		}
		
		for ($c=0;$c<count($selectedControllers);$c++){
			$selectedActions=array();
			$selectedActions=$this->Acl->Aco->find('all',array(
				'conditions'=>array(
					'Aco.parent_id'=>$selectedControllers[$c]['Aco']['id'],
					'Aco.alias !='=>$excludedActions,
				),
			));
			if (!empty($selectedActions)){
				for ($a=0;$a<count($selectedActions);$a++){
					$rolePermissions=array();
					for ($r=0;$r<count($roles);$r++){
						$aco_name=$selectedControllers[$c]['Aco']['alias']."/".$selectedActions[$a]['Aco']['alias'];
						//pr($aco_name);
						$hasPermission=$this->Acl->check(array('Role'=>array('id'=>$roles[$r]['Role']['id'])),$aco_name);
						//if ($selectedActions[$a]['Aco']['id']==15){
						//	echo "permission for ".$aco_name." is ".$hasPermission."<br/>";
						//}
						if ($hasPermission){
							$rolePermissions[$r]=$hasPermission;
						}
						else {
							$rolePermissions[$r]=0;
						}						
					}
					//if ($selectedActions[$a]['Aco']['id']==15){
					//	pr($rolePermissions);
					//}
					$selectedActions[$a]['rolePermissions']=$rolePermissions;
				}
			}
			//pr($selectedActions);
			
			$selectedControllers[$c]['actions']=$selectedActions;
		}
		$this->set(compact('selectedControllers'));
	}
	
	public function init_DB_permissions() {
	
		$role = $this->User->Role;
	/*
		// Allow admins to access everything
		$role->id = 4;
		$this->Acl->allow($role, 'controllers');
		$this->Acl->deny($role, 'controllers/ProductionResultCodes');
		$this->Acl->deny($role, 'controllers/StockMovementTypes');
		$this->Acl->deny($role, 'controllers/Role');
		
		// Allow assistants to access everything but leave out editing rights in the views and controllers
		$role->id = 5;
		$this->Acl->allow($role, 'controllers');
		$this->Acl->deny($role, 'controllers/ProductionResultCodes');
		$this->Acl->deny($role, 'controllers/StockMovementTypes');
		$this->Acl->deny($role, 'controllers/Role');
		
		// Allow assistants to access everything but leave out editing rights in the views and controllers
		$role->id = 6;
		$this->Acl->allow($role, 'controllers');
		$this->Acl->deny($role, 'controllers/ProductionResultCodes');
		$this->Acl->deny($role, 'controllers/StockMovementTypes');
		$this->Acl->deny($role, 'controllers/Role');
		*/
		// we add an exit to avoid an ugly "missing views" error message
		echo "all done";
		exit;
	
	}

}
