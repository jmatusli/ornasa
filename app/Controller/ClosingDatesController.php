<?php
App::uses('AppController', 'Controller');
/**
 * ClosingDates Controller
 *
 * @property ClosingDate $ClosingDate
 * @property PaginatorComponent $Paginator
 */
class ClosingDatesController extends AppController {

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
		$this->ClosingDate->recursive = 0;
		$closingDates=$this->ClosingDate->find('all',array(
			'order'=>'closing_date DESC'
		));
		$this->set(compact('closingDates'));
	}


/**
 * add method
 *
 * @return void
 */
	public function add() {
		$firstDateOfCurrentMonth = date("Y-m-01");
		$proposedClosingDate=date( "Y-m-d", strtotime( $firstDateOfCurrentMonth."-1 days" ) );
		if ($this->request->is('post')) {
			$this->ClosingDate->create();
			if ($this->ClosingDate->save($this->request->data)) {
				$this->recordUserAction($this->ClosingDate->id,null,null);
				$this->Session->setFlash(__('The closing date has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The closing date could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		}
		$this->set(compact('proposedClosingDate'));
	}

		
/**
 * view method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		if (!$this->ClosingDate->exists($id)) {
			throw new NotFoundException(__('Invalid closing date'));
		}
		$options = array('conditions' => array('ClosingDate.' . $this->ClosingDate->primaryKey => $id));
		$this->set('closingDate', $this->ClosingDate->find('first', $options));
	}
	
/**
 * edit method
 *
 * @throws NotFoundException
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		if (!$this->ClosingDate->exists($id)) {
			throw new NotFoundException(__('Invalid closing date'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->ClosingDate->save($this->request->data)) {
				$this->recordUserAction();
				$this->Session->setFlash(__('The closing date has been saved.'));
				return $this->redirect(array('action' => 'index'));
			} 
			else {
				$this->Session->setFlash(__('The closing date could not be saved. Please, try again.'));
			}
		} else {
			$options = array('conditions' => array('ClosingDate.' . $this->ClosingDate->primaryKey => $id));
			$this->request->data = $this->ClosingDate->find('first', $options);
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
		$this->ClosingDate->id = $id;
		if (!$this->ClosingDate->exists()) {
			throw new NotFoundException(__('Invalid closing date'));
		}
		$this->request->allowMethod('post', 'delete');
		if ($this->ClosingDate->delete()) {
			$this->Session->setFlash(__('The closing date has been deleted.'));
		} else {
			$this->Session->setFlash(__('The closing date could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
