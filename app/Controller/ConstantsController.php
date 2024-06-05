<?php
App::uses('AppController', 'Controller');
/**
 * Constants Controller
 *
 * @property Constant $Constant
 * @property PaginatorComponent $Paginator
 */
class ConstantsController extends AppController {

	public $components = array('Paginator');

	public function index() {
		$this->Constant->recursive = -1;
		
		$constantCount=	$this->Constant->find('count', array(
			'fields'=>array('Constant.id'),
			'conditions' => array(
			),
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
			),
			'contain'=>array(				
			),
			'limit'=>($constantCount!=0?$constantCount:1),
		);

		$constants = $this->Paginator->paginate('Constant');
		$this->set(compact('constants'));
	}

	public function view($id = null) {
		if (!$this->Constant->exists($id)) {
			throw new NotFoundException(__('Invalid constant'));
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
		$options = array('conditions' => array('Constant.id' => $id));
		$this->set('constant', $this->Constant->find('first', $options));
	}

	public function add() {
		if ($this->request->is('post')) {
			$this->Constant->create();
			if ($this->Constant->save($this->request->data)) {
				$this->Session->setFlash(__('The constant has been saved.'),'default',['class' => 'success']);
				return $this->redirect(array('action' => 'index'));
			} 
      else {
				$this->Session->setFlash(__('The constant could not be saved. Please, try again.'),'default',['class' => 'error-message']);
			}
		}
	}

	public function edit($id = null) {
		if (!$this->Constant->exists($id)) {
			throw new NotFoundException(__('Invalid constant'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->Constant->save($this->request->data)) {
				$this->Session->setFlash(__('The constant has been saved.'),'default',['class' => 'success']);
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The constant could not be saved. Please, try again.'),'default',['class' => 'error-message']);
			}
		} else {
			$options = array('conditions' => array('Constant.' . $this->Constant->primaryKey => $id));
			$this->request->data = $this->Constant->find('first', $options);
		}
	}

/**
 * delete method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		$this->Constant->id = $id;
		if (!$this->Constant->exists()) {
			throw new NotFoundException(__('Invalid constant'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->Constant->delete()) {
			$this->Session->setFlash(__('The constant has been deleted.'),'default',['class' => 'success']);
		} else {
			$this->Session->setFlash(__('The constant could not be deleted. Please, try again.'),'default',['class' => 'error-message']);
		}
		return $this->redirect(array('action' => 'index'));
	}
}
