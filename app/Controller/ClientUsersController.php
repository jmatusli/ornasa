<?php
App::uses('AppController', 'Controller');
/**
 * ClientUsers Controller
 *
 * @property ClientUser $ClientUser
 * @property PaginatorComponent $Paginator
 */
class ClientUsersController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->ClientUser->recursive = -1;
		
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
		
		$clientUserCount=	$this->ClientUser->find('count', array(
			'fields'=>array('ClientUser.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($clientUserCount!=0?$clientUserCount:1),
		);

		$clientUsers = $this->Paginator->paginate('ClientUser');
		$this->set(compact('clientUsers'));
	}

	public function view($id = null) {
		if (!$this->ClientUser->exists($id)) {
			throw new NotFoundException(__('Invalid client user'));
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
		$options = array('conditions' => array('ClientUser.' . $this->ClientUser->primaryKey => $id));
		$this->set('clientUser', $this->ClientUser->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->ClientUser->create();
			if ($this->ClientUser->save($this->request->data)) {
				$this->Session->setFlash(__('The client user has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The client user could not be saved. Please, try again.'));
			}
		}
		$clients = $this->ClientUser->Client->find('list');
		$users = $this->ClientUser->User->find('list');
		$this->set(compact('clients', 'users'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->ClientUser->exists($id)) {
			throw new NotFoundException(__('Invalid client user'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ClientUser->save($this->request->data)) {
				$this->Session->setFlash(__('The client user has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The client user could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ClientUser.' . $this->ClientUser->primaryKey => $id));
			$this->request->data = $this->ClientUser->find('first', $options);
		}
		$clients = $this->ClientUser->Client->find('list');
		$users = $this->ClientUser->User->find('list');
		$this->set(compact('clients', 'users'));
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->ClientUser->id = $id;
		if (!$this->ClientUser->exists()) {
			throw new NotFoundException(__('Invalid client user'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ClientUser->delete()) {
			$this->Session->setFlash(__('The client user has been deleted.'));
		} else {
			$this->Session->setFlash(__('The client user could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
