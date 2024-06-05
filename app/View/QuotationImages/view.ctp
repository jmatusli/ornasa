<div class="quotationImages view">
<?php 
	echo "<h2>".__('Quotation Image')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Quotation')."</dt>";
		echo "<dd>".$this->Html->link($quotationImage['Quotation']['quotation_code'], array('controller' => 'quotations', 'action' => 'view', $quotationImage['Quotation']['id']))."</dd>";
		echo "<dt>".__('Url Image')."</dt>";
		echo "<dd>".h($quotationImage['QuotationImage']['url_image'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Quotation Image'), array('action' => 'edit', $quotationImage['QuotationImage']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Quotation Image'), array('action' => 'delete', $quotationImage['QuotationImage']['id']), array(), __('Are you sure you want to delete # %s?', $quotationImage['QuotationImage']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Quotation Images'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Quotation Image'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
