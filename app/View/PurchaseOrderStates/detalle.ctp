<div class="purchaseOrderStates view">
<?php 
	echo '<h2>'.__('Purchase Order State').'</h2>';
	echo '<dl>';
		echo '<dt>'.__('Code').'</dt>';
		echo '<dd>'.h($purchaseOrderState['PurchaseOrderState']['code']).'</dd>';
		echo '<dt>'.__('Short Description').'</dt>';
		echo '<dd>'.h($purchaseOrderState['PurchaseOrderState']['short_description']).'</dd>';
		echo '<dt>'.__('Long Description').'</dt>';
		echo '<dd>'.h($purchaseOrderState['PurchaseOrderState']['long_description']).'</dd>';
		echo '<dt>'.__('List Order').'</dt>';
		echo '<dd>'.h($purchaseOrderState['PurchaseOrderState']['list_order']).'</dd>';
		echo '<dt>'.__('Hex Color').'</dt>';
		echo '<dd>'.h($purchaseOrderState['PurchaseOrderState']['hex_color']).'</dd>';
	echo '</dl>';
?> 
</div>
<div class="actions">
<?php 
	echo '<h3>'.__('Actions').'</h3>';
	echo '<ul>';
		if ($bool_edit_permission){
			echo '<li>'.$this->Html->link(__('Edit Purchase Order State'), ['action' => 'editar', $purchaseOrderState['PurchaseOrderState']['id']]).'</li>';
	echo '<br/>';
		}
		if ($bool_edit_permission){
			echo '<li>'.$this->Form->postLink(__('Delete Purchase Order State'), ['action' => 'delete', $purchaseOrderState['PurchaseOrderState']['id']], [], __('Está seguro que quiere eliminar # %s?', $purchaseOrderState['PurchaseOrderState']['id'])).'</li>';
	echo '<br/>';
		}
		echo '<li>'.$this->Html->link(__('List Purchase Order States'), ['action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Purchase Order State'), ['action' => 'crear']).'</li>';
		echo '<br/>';
		echo '<li>'.$this->Html->link(__('List Purchase Orders'), ['controller' => 'purchase_orders', 'action' => 'resumen']).'</li>';
		echo '<li>'.$this->Html->link(__('New Purchase Order'), ['controller' => 'purchase_orders', 'action' => 'crear']).'</li>';
	echo '</ul>';
?> 
</div>
<div class="related">
<?php 
	if (!empty($purchaseOrderState['PurchaseOrder'])){
		echo '<h3>'.__('Related Purchase Orders').'</h3>';
		echo '<table cellpadding="0" cellspacing="0">';
			echo '<tr>';
				echo '<th>'.__('Purchase Order Date').'</th>';
				echo '<th>'.__('Purchase Order Code').'</th>';
				echo '<th>'.__('Provider Id').'</th>';
				echo '<th>'.__('User Id').'</th>';
				echo '<th>'.__('Bool Annulled').'</th>';
				echo '<th>'.__('Bool Iva').'</th>';
				echo '<th>'.__('Currency Id').'</th>';
				echo '<th>'.__('Cost Subtotal').'</th>';
				echo '<th>'.__('Cost Iva').'</th>';
				echo '<th>'.__('Cost Total').'</th>';
				echo '<th>'.__('Bool Credit').'</th>';
				echo '<th>'.__('Due Date').'</th>';
				echo '<th>'.__('Bool Paid').'</th>';
				echo '<th>'.__('Bool Authorized').'</th>';
				echo '<th>'.__('Purchase Order State Id').'</th>';
			echo '</tr>';
		foreach ($purchaseOrderState['PurchaseOrder'] as $purchaseOrder){ 
			echo '<tr>';
				echo '<td>'.$purchaseOrder['purchase_order_date'].'</td>';
				echo '<td>'.$purchaseOrder['purchase_order_code'].'</td>';
				echo '<td>'.$purchaseOrder['provider_id'].'</td>';
				echo '<td>'.$purchaseOrder['user_id'].'</td>';
				echo '<td>'.$purchaseOrder['bool_annulled'].'</td>';
				echo '<td>'.$purchaseOrder['bool_iva'].'</td>';
				echo '<td>'.$purchaseOrder['currency_id'].'</td>';
				echo '<td>'.$purchaseOrder['cost_subtotal'].'</td>';
				echo '<td>'.$purchaseOrder['cost_iva'].'</td>';
				echo '<td>'.$purchaseOrder['cost_total'].'</td>';
				echo '<td>'.$purchaseOrder['bool_credit'].'</td>';
				echo '<td>'.$purchaseOrder['due_date'].'</td>';
				echo '<td>'.$purchaseOrder['bool_paid'].'</td>';
				echo '<td>'.$purchaseOrder['bool_authorized'].'</td>';
				echo '<td>'.$purchaseOrder['purchase_order_state_id'].'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
?>
</div>
