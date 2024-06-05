<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller','PHPExcel');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class UserPageRightsController extends AppController {

	public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel']; 
  
  public function beforeFilter(){
		parent::beforeFilter();
		$this->Auth->allow('');
	}

  public function resumen() {
		$this->UserPageRight->recursive = -1;
		
    $roleId=0;
    
		if ($this->request->is('post')) {
			//$startDateArray=$this->request->data['Report']['startdate'];
			//$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			//$startDate=date( "Y-m-d", strtotime($startDateString));
		
			//$endDateArray=$this->request->data['Report']['enddate'];
			//$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			//$endDate=date("Y-m-d",strtotime($endDateString));
			//$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
      $roleId=$this->request->data['Report']['role_id'];
		}
		//$this->set(compact('startDate','endDate'));
    $this->set(compact('roleId'));
		
    
    $conditions=[];
    if ($roleId>0){
      $usersForRole=array_keys($this->UserPageRight->User->getActiveUsersForRole($roleId));
      
      $conditions=[
        'OR'=>[
          ['UserPageRight.role_id'=>$roleId],
          ['UserPageRight.user_id'=>$userIdsForRole],
        ],
      ];
      
    }
    //pr($conditions);
		$userPageRightCount=	$this->UserPageRight->find('count', [
			'fields'=>['UserPageRight.id'],
			'conditions' => $conditions,
		]);
		
		$this->Paginator->settings = [
			'conditions' => $conditions,
			'contain'=>[
        'PageRight',
        'Role',
        'User',
			],
      'order'=>'UserPageRight.role_id ASC, UserPageRight.user_id ASC, UserPageRight.page_right_id ASC, UserPageRight.assignment_datetime DESC',
			'limit'=>($userPageRightCount!=0?$userPageRightCount:1),
		] ;

		$userPageRights = $this->Paginator->paginate('UserPageRight');
		$this->set(compact('userPageRights'));
	}

  public function guardarResumenAsignacionesDerechosIndividuales($fileName) {
    if (empty($fileName)){
      $fileName=date('d_m_Y')."_Resumen Asignaciones de Derechos Individuales.xlsx";
    }
		$exportData=$_SESSION['resumenAsignacionesDerechosIndividuales'];
		$this->set(compact('exportData','fileName'));
	}

	public function detalle($id = null) {
		if (!$this->UserPageRight->exists($id)) {
			throw new NotFoundException(__('Invalid user page right'));
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
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		$this->set(compact('startDate','endDate'));
		$options = [
      'conditions' => [
        'UserPageRight.id' => $id,
      ],
    ];
		$this->set('userPageRight', $this->UserPageRight->find('first', $options));
	}

	public function crear() {
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if ($this->request->is('post')) {
      if (empty($this->request->data['UserPageRight']['role_id']) && empty($this->request->data['UserPageRight']['user_id'])){
          $this->Session->setFlash(__('Se debe seleccior un papel o un  usuario al cual se asigna el derecho!  No se asignó el derecho.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['UserPageRight']['controller'])){
          $this->Session->setFlash(__('Se debe indicar el controlador al cual se aplica el derecho!  No se asignó el derecho.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['UserPageRight']['action'])){
          $this->Session->setFlash(__('Se debe indicar la acción al cual se aplica el derecho!  No se asignó el derecho.'), 'default',['class' => 'error-message']);
      }
      else {
        $this->UserPageRight->create();
        $this->request->data['UserPageRight']['assignment_datetime']=date("Y-m-d H:i:s");;
        $this->request->data['UserPageRight']['logging_user_id']=$loggedUserId;
        if ($this->UserPageRight->save($this->request->data)) {
          $this->Session->setFlash(__('Se asignó el derecho.'), 'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen'] );
        } 
        else {
          $this->Session->setFlash(__('No se podía asignar el derecho.'), 'default',['class' => 'error-message'] );
        } 
      }
		}
		$pageRights = $this->UserPageRight->PageRight->find('list');
		$roles = $this->UserPageRight->Role->find('list');
		$users = $this->UserPageRight->User->getActiveUserList();
		$this->set(compact('pageRights', 'roles', 'users'));
	}

	public function editar($id = null) {
    $loggedUserId=$this->Auth->User('id');
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('loggedUserId','userRoleId'));
    
		if (!$this->UserPageRight->exists($id)) {
			throw new NotFoundException(__('Invalid user page right'));
		}
		if ($this->request->is(['post', 'put'])) {
      if (empty($this->request->data['UserPageRight']['role_id']) && empty($this->request->data['UserPageRight']['user_id'])){
          $this->Session->setFlash(__('Se debe seleccior un papel o un  usuario al cual se asigna el derecho!  No se asignó el derecho.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['UserPageRight']['controller'])){
          $this->Session->setFlash(__('Se debe indicar el controlador al cual se aplica el derecho!  No se asignó el derecho.'), 'default',['class' => 'error-message']);
      }
      elseif (empty($this->request->data['UserPageRight']['action'])){
          $this->Session->setFlash(__('Se debe indicar la acción al cual se aplica el derecho!  No se asignó el derecho.'), 'default',['class' => 'error-message']);
      }
      else {
        //NOTE THAT NEW REGISTERS ARE BEING CREATED TO KEEP A HISTORIAL
        $this->UserPageRight->create();
        $this->request->data['UserPageRight']['assignment_datetime']=date("Y-m-d H:i:s");;
        $this->request->data['UserPageRight']['logging_user_id']=$loggedUserId;
        if ($this->UserPageRight->save($this->request->data)) {
          $this->Session->setFlash(__('Se asignó el derecho.'), 'default',['class' => 'success']);
          return $this->redirect(['action' => 'resumen'] );
        } 
        else {
          $this->Session->setFlash(__('No se podía asignar el derecho.'), 'default',['class' => 'error-message'] );
        } 
      }  
			//if ($this->UserPageRight->save($this->request->data)) {
			//	$this->Session->setFlash(__('The user page right has been saved.'), 'default',['class' => 'success']);
			//	return $this->redirect(array('action' => 'resumen'));
			//} else {
			//	$this->Session->setFlash(__('The user page right could not be saved. Please, try again.'), 'default',['class' => 'error-message']);
			//}
    }
    else {
			$options = ['conditions' => ['UserPageRight.id' => $id]];
			$this->request->data = $this->UserPageRight->find('first', $options);
		}
		
    $pageRights = $this->UserPageRight->PageRight->find('list');
		$roles = $this->UserPageRight->Role->find('list');
		$users = $this->UserPageRight->User->find('list');
		$this->set(compact('pageRights', 'roles', 'users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->UserPageRight->id = $id;
		if (!$this->UserPageRight->exists()) {
			throw new NotFoundException(__('Invalid user page right'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->UserPageRight->delete()) {
			$this->Session->setFlash(__('The user page right has been deleted.'), 'default',['class' => 'success']);
		} 
    else {
			$this->Session->setFlash(__('The user page right could not be deleted. Please, try again.'), 'default',['class' => 'error-message']);
		}
		return $this->redirect(['action' => 'resumen']);
	}
}
