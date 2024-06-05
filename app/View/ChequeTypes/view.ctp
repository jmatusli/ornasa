<div class='chequeTypes view'>
<h2><?php echo __('Cheque Type'); ?></h2>
	<dl>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($chequeType['ChequeType']['name']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Accounting Code'); ?></dt>
		<dd>
			<?php echo $this->Html->link($chequeType['AccountingCode']['code'], array('controller' => 'accounting_codes', 'action' => 'view', $chequeType['AccountingCode']['id'])); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Cheque Type'), array('action' => 'edit', $chequeType['ChequeType']['id'])); ?> </li>
		<!--li><?php //echo $this->Form->postLink(__('Delete Cheque Type'), array('action' => 'delete', $chequeType['ChequeType']['id']), array(), __('Are you sure you want to delete # %s?', $chequeType['ChequeType']['id'])); ?> </li-->
		<li><?php echo $this->Html->link(__('List Cheque Types'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cheque Type'), array('action' => 'add')); ?> </li>
		<br/>
		<li><?php echo $this->Html->link(__('List Accounting Codes'), array('controller' => 'accounting_codes', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Accounting Code'), array('controller' => 'accounting_codes', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Cheques'), array('controller' => 'cheques', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Cheque'), array('controller' => 'cheques', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
<?php 
	if (!empty($chequeType['Cheque'])) {
		echo "<h3>".__('Related Cheques')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Cheque Date')."</th>";
					echo "<th>".__('Cheque Code')."</th>";
					echo "<th>".__('Receiver Name')."</th>";
					echo "<th>".__('Amount')."</th>";
					echo "<th>".__('Concept')."</th>";
					echo "<th>".__('Bank Accounting Code')."</th>";
					echo "<th>".__('Accounting Register')."</th>";
					echo "<th>".__('Purchase')."</th>";
					echo "<th class='actions'>".__('Actions')."</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";

			foreach ($chequeType['Cheque'] as $cheque) {
				echo "<tr>";
					echo "<td>".$cheque['cheque_date']."</td>";
					echo "<td>".$cheque['cheque_code']."</td>";
					echo "<td>".$cheque['receiver_name']."</td>";
					echo "<td>".$cheque['Currency']['abbreviation']." <span class='amountright'>".number_format($cheque['amount'],2,".",",")."</span></td>";
					echo "<td>".$cheque['concept']."</td>";
					echo "<td>".$this->Html-Link($cheque['AccountingCode']['fullname'],array('controller'=>'accounting_codes','action','view',$cheque['bank_accounting_code_id']))."</td>";
					echo "<td>".$this->Html-Link($cheque['AccountingRegister']['accounting_register_code'],array('controller'=>'accounting_registers','action','view',$cheque['accounting_register_id']))."</td>";
					echo "<td>".$this->Html-Link($cheque['Purchase']['order_code'],array('controller'=>'orders','action','view',$cheque['purchase_id']))."</td>";
					echo "<td class='actions'>";
						echo $this->Html->link(__('View'), array('controller' => 'cheques', 'action' => 'view', $cheque['id'])); 
						echo $this->Html->link(__('Edit'), array('controller' => 'cheques', 'action' => 'edit', $cheque['id'])); 
						echo $this->Form->postLink(__('Delete'), array('controller' => 'cheques', 'action' => 'delete', $cheque['id']), array(), __('Are you sure you want to delete # %s?', $cheque['id'])); 
					echo "</td>";
				echo "</tr>";
			}
			echo "</tbody>";
		echo "</table>";
	}
?>

	<!--div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Cheque'), array('controller' => 'cheques', 'action' => 'add')); ?> </li>
		</ul>
	</div-->
</div>
