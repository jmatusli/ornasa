<div class="deliveryRemarks form">
<?php
	echo $this->Form->create('DeliveryRemark'); 
	echo '<fieldset>';
__('Edit Delivery Remark')		echo '<legend>'.26.'</legend>';
		echo $this->Form->input('id');
		echo $this->Form->input('delivery_id');
		echo $this->Form->input('registering_user_id');
		echo $this->Form->input('remark_datetime');
		echo $this->Form->input('remark_text');
	echo '</fieldset>';
	echo $this->Form->Submit(__('Submit'));
	echo $this->Form->end();
?>
</div>
<div class="actions">
<?php
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		echo '<li>'.$this->Form->postLink(__('Delete'), ['action' => 'delete', $this->Form->value('DeliveryRemark.id')], [], __('Are you sure you want to delete # %s?', $this->Form->value('DeliveryRemark.id'))).'</li>';
		echo '<li>'.$this->Html->link(__('List Delivery Remarks'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('List Deliveries'), ['controller' => 'deliveries', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Delivery'), ['controller' => 'deliveries', 'action' => 'crear']).'</li>';
		echo '<li>'.$this->Html->link(__('List Users'), ['controller' => 'users', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Registering User'), ['controller' => 'users', 'action' => 'crear']).'</li>';
	echo '</ul>';
?>
</div>
