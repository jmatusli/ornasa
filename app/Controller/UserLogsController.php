<?php
App::build(['Vendor' => [APP . 'Vendor' . DS . 'PHPExcel']]);
App::uses('AppController', 'Controller');
App::import('Vendor', 'PHPExcel/Classes/PHPExcel');

class UserLogsController extends AppController {

  public $components = ['Paginator','RequestHandler'];
	public $helpers = ['PhpExcel'];

  public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->allow('resumen','guardarResumen');		
	}

	public function resumen($userId=0) {
		$loggedUserId=$this->Auth->User('id');
    $this->set(compact('loggedUserId'));
    $userRoleId = $this->Auth->User('role_id');
    $this->set(compact('userRoleId'));
    
    if ($this->request->is('post')) {
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			
      $userId=$this->request->data['Report']['user_id'];
		}
		else if (!empty($_SESSION['startDate']) && !empty($_SESSION['endDate'])){
			$startDate=$_SESSION['startDate'];
			$endDate=$_SESSION['endDate'];
			$endDatePlusOne=date("Y-m-d",strtotime($endDate."+1 days"));
		}
		else {
			$startDate = date("Y-m-01");
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		
		$_SESSION['startDate']=$startDate;
		$_SESSION['endDate']=$endDate;
		
		$this->set(compact('startDate','endDate'));
		$this->set(compact('userId'));
    
    $userLogConditions=[
      'UserLog.created >='=>$startDate,
      'UserLog.created <'=>$endDatePlusOne,
    ];
    
    if ($userId > 0){
      $userLogConditions['UserLog.user_id'] = $userId;
    }
    
    $this->Paginator->settings = [
			'conditions' => $userLogConditions,
			'contain'=>[
        'User',
			],
			'order'=>'UserLog.id DESC',
			'limit'=>250,
		];
    
    $userLogs=$this->Paginator->paginate('UserLog');
    
		$this->set(compact('userLogs'));
    
    $users=$this->User->find('list',[
      'conditions'=>['bool_active'=>true],
			'order'=>'User.username'
		]);
    $this->set(compact('users'));
	}
  public function guardarResumen() {
		$exportData=$_SESSION['resumen'];
		$this->set(compact('exportData'));
	}
  
	public function detalle($id = null) {
		if (!$this->UserLog->exists($id)) {
			throw new NotFoundException(__('Invalid user log'));
		}
		$options = array('conditions' => array('UserLog.' . $this->UserLog->primaryKey => $id));
		$this->set('userLog', $this->UserLog->find('first', $options));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->UserLog->create();
			if ($this->UserLog->save($this->request->data)) {
				$this->Session->setFlash(__('The user log has been saved.'),'default',array('class' => 'success'));
				$this->recordUserAction($this->UserLog->id,"add",null);
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user log could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		}
		$users = $this->UserLog->User->find('list');
		$this->set(compact('users'));
	}

	public function editar($id = null) {
		if (!$this->UserLog->exists($id)) {
			throw new NotFoundException(__('Invalid user log'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->UserLog->save($this->request->data)) {
				$this->Session->setFlash(__('The user log has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user log could not be saved. Please, try again.'), 'default',array('class' => 'error-message'));
			}
		} else {
			$options = array('conditions' => array('UserLog.' . $this->UserLog->primaryKey => $id));
			$this->request->data = $this->UserLog->find('first', $options);
		}
		$users = $this->UserLog->User->find('list');
		$this->set(compact('users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->UserLog->id = $id;
		if (!$this->UserLog->exists()) {
			throw new NotFoundException(__('Invalid user log'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->UserLog->delete()) {
			$this->Session->setFlash(__('The user log has been deleted.'));
		} else {
			$this->Session->setFlash(__('The user log could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
