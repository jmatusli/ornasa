<?php
App::uses('AppController', 'Controller');
/**
 * UserActions Controller
 *
 * @property UserAction $UserAction
 * @property PaginatorComponent $Paginator
 */
class UserActionsController extends AppController {

/**
 * Components
 *
 * @var array
 */
	public $components = array('Paginator');

/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->UserAction->recursive = -1;
		
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
		
		$userActionCount=	$this->UserAction->find('count', array(
			'fields'=>array('UserAction.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($userActionCount!=0?$userActionCount:1),
		);

		$userActions = $this->Paginator->paginate('UserAction');
		$this->set(compact('userActions'));
	}

/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->UserAction->exists($id)) {
			throw new NotFoundException(__('Invalid user action'));
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
		$options = array('conditions' => array('UserAction.' . $this->UserAction->primaryKey => $id));
		$this->set('userAction', $this->UserAction->find('first', $options));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->UserAction->create();
			if ($this->UserAction->save($this->request->data)) {
				$this->Session->setFlash(__('The user action has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user action could not be saved. Please, try again.'));
			}
		}
		$users = $this->UserAction->User->find('list');
		$this->set(compact('users'));
	}

/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->UserAction->exists($id)) {
			throw new NotFoundException(__('Invalid user action'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->UserAction->save($this->request->data)) {
				$this->Session->setFlash(__('The user action has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user action could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('UserAction.' . $this->UserAction->primaryKey => $id));
			$this->request->data = $this->UserAction->find('first', $options);
		}
		$users = $this->UserAction->User->find('list');
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
		$this->UserAction->id = $id;
		if (!$this->UserAction->exists()) {
			throw new NotFoundException(__('Invalid user action'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->UserAction->delete()) {
			$this->Session->setFlash(__('The user action has been deleted.'));
		} else {
			$this->Session->setFlash(__('The user action could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
