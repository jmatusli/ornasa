<div class="paymentModes view">
<?php 
	echo "<h2>".__('Payment Mode')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Name')."</dt>";
		echo "<dd>".h($paymentMode['PaymentMode']['name'])."</dd>";
		echo "<dt>".__('Description')."</dt>";
		if (!empty($paymentMode['PaymentMode']['description'])){
			echo "<dd>".h($paymentMode['PaymentMode']['description'])."</dd>";
		}
		else {
			echo "<dd>-</dd>";
		}
	echo "</dl>";
?> 
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Payment Mode'), array('action' => 'edit', $paymentMode['PaymentMode']['id']))."</li>";
			echo "<br/>";
		}
		if ($bool_delete_permission){
			echo "<li>".$this->Form->postLink(__('Delete Payment Mode'), array('action' => 'delete', $paymentMode['PaymentMode']['id']), array(), __('Está seguro que quiere eliminar el modo de pago %s?', $paymentMode['PaymentMode']['name']))."</li>";
			echo "<br/>";
		}
		echo "<li>".$this->Html->link(__('List Payment Modes'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Payment Mode'), array('action' => 'add'))."</li>";
		echo "<br/>";
	echo "</ul>";
?> 
</div>