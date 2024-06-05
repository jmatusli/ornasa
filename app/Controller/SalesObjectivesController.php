<?php
App::uses('AppController', 'Controller');

class SalesObjectivesController extends AppController {

	public $components = array('Paginator');

	public function resumen() {
		$this->SalesObjective->recursive = -1;
		$vendorUserId=0;
		if ($this->request->is('post')) {
			/*
			$startDateArray=$this->request->data['Report']['startdate'];
			$startDateString=$startDateArray['year'].'-'.$startDateArray['month'].'-'.$startDateArray['day'];
			$startDate=date( "Y-m-d", strtotime($startDateString));
		
			$endDateArray=$this->request->data['Report']['enddate'];
			$endDateString=$endDateArray['year'].'-'.$endDateArray['month'].'-'.$endDateArray['day'];
			$endDate=date("Y-m-d",strtotime($endDateString));
			$endDatePlusOne=date("Y-m-d",strtotime($endDateString."+1 days"));
			*/
			$vendorUserId=$this->request->data['Report']['vendor_user_id'];
		}
		/*
		if (!isset($startDate)){
			$startDate = date("Y-m-01");
		}
		if (!isset($endDate)){
			$endDate=date("Y-m-d",strtotime(date("Y-m-d")));
			$endDatePlusOne= date( "Y-m-d", strtotime( date("Y-m-d")."+1 days" ) );
		}
		*/
		//$this->set(compact('startDate','endDate'));
		$this->set(compact('vendorUserId'));
		
		$conditions=array();
		if ($vendorUserId>0){
			$conditions[]=array('SalesObjective.user_id'=>$vendorUserId);
		}
		
		$salesObjectiveCount=	$this->SalesObjective->find('count', array(
			'fields'=>array('SalesObjective.id'),
			'conditions' => $conditions,
		));
		
		$this->Paginator->settings = array(
			'conditions' => array(	
				$conditions
			),
			'contain'=>array(		
				'User',
			),
			'order'=>'SalesObjective.objective_date DESC',
			'limit'=>($salesObjectiveCount!=0?$salesObjectiveCount:1),
		);

		$salesObjectives = $this->Paginator->paginate('SalesObjective');
		$this->set(compact('salesObjectives'));
		
		$this->loadModel('User');
    $vendorUserIds=$this->User->find('list',[
      'fields'=>['User.id'],
			'conditions'=>[
				'OR'=>[
					['User.role_id'=>ROLE_ADMIN],
          ['User.role_id'=>ROLE_ASSISTANT],
          ['User.role_id'=>ROLE_SALES_EXECUTIVE],
				],
			],
		]);
    //pr($vendorUserIds);
    $vendorUsers=$this->SalesObjective->User->find('list',[
      'conditions'=>[
        'User.bool_active'=>true,
        'User.id'=>$vendorUserIds,
      ],
			'order'=>'User.username ASC',
		]);
		$this->set(compact('vendorUsers'));
	}

	public function detalle($id = null) {
		if (!$this->SalesObjective->exists($id)) {
			throw new NotFoundException(__('Invalid sales objective'));
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
		
		$options = array('conditions' => array('SalesObjective.' . $this->SalesObjective->primaryKey => $id));
		$this->set('salesObjective', $this->SalesObjective->find('first', $options));
	}

	public function crear() {
		if ($this->request->is('post')) {
			$this->SalesObjective->create();
			if ($this->SalesObjective->save($this->request->data)) {
				$this->Session->setFlash(__('The sales objective has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales objective could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		}
    $vendorUserIds=$this->SalesObjective->User->find('list',[
      'fields'=>['User.id'],
			'conditions'=>[
				'OR'=>[
					['User.role_id'=>ROLE_ADMIN],
          ['User.role_id'=>ROLE_ASSISTANT],
          ['User.role_id'=>ROLE_SALES_EXECUTIVE],
				],
			],
		]);
    //pr($vendorUserIds);
    $users=$this->SalesObjective->User->find('list',[
      'conditions'=>[
        'bool_active'=>true,
        'id'=>$vendorUserIds,
      ],
			'order'=>'User.username ASC',
		]);
		$this->set(compact('users'));
	}

	public function editar($id = null) {
		if (!$this->SalesObjective->exists($id)) {
			throw new NotFoundException(__('Invalid sales objective'));
		}
		if ($this->request->is(array('post', 'put'))) {
			if ($this->SalesObjective->save($this->request->data)) {
				$this->Session->setFlash(__('The sales objective has been saved.'),'default',array('class' => 'success'));
				return $this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The sales objective could not be saved. Please, try again.'),'default',array('class' => 'error-message'));
			}
		} 
    else {
			$options = array('conditions' => array('SalesObjective.' . $this->SalesObjective->primaryKey => $id));
			$this->request->data = $this->SalesObjective->find('first', $options);
		}
		$vendorUserIds=$this->SalesObjective->User->find('list',[
      'fields'=>['User.id'],
			'conditions'=>[
				'OR'=>[
					['User.role_id'=>ROLE_ADMIN],
          ['User.role_id'=>ROLE_ASSISTANT],
          ['User.role_id'=>ROLE_SALES_EXECUTIVE],
				],
			],
		]);
    $users=$this->SalesObjective->User->find('list',[
      'conditions'=>[
        'bool_active'=>true,
        'id'=>$vendorUserIds,
      ],
			'order'=>'User.username ASC',
		]);
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
		$this->SalesObjective->id = $id;
		if (!$this->SalesObjective->exists()) {
			throw new NotFoundException(__('Invalid sales objective'));
		}
		$this->request->allowMethod('post', 'delete');
		
		$salesObjective=$this->SalesObjective->find('first',array(
			'conditions'=>array(
				'SalesObjective.id'=>$id,
			),
			'contain'=>array(
				'User'
			),
		));
		
		if ($this->SalesObjective->delete()) {
			$this->loadModel('Deletion');
			$this->Deletion->create();
			$deletionArray=array();
			$deletionArray['Deletion']['user_id']=$this->Auth->User('id');
			$deletionArray['Deletion']['reference_id']=$salesObjective['SalesObjective']['id'];
			$deletionArray['Deletion']['reference']=$salesObjective['User']['username']." ".$salesObjective['SalesObjective']['objective_date'];
			$deletionArray['Deletion']['type']='SalesObjective';
			$this->Deletion->save($deletionArray);
		
			$this->Session->setFlash(__('The sales objective has been deleted.'));
		} 
		else {
			$this->Session->setFlash(__('The sales objective could not be deleted. Please, try again.'));
		}
		return $this->redirect(array('action' => 'index'));
	}
}
