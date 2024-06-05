<div class="quotationRemarks view">
<?php 
	echo "<h2>".__('Quotation Remark')."</h2>";
	echo "<dl>";
		echo "<dt>".__('User')."</dt>";
		echo "<dd>".$this->Html->link($quotationRemark['User']['username'], array('controller' => 'users', 'action' => 'view', $quotationRemark['User']['id']))."</dd>";
		echo "<dt>".__('Quotation')."</dt>";
		echo "<dd>".$this->Html->link($quotationRemark['Quotation']['quotation_code'], array('controller' => 'quotations', 'action' => 'view', $quotationRemark['Quotation']['id']))."</dd>";
		echo "<dt>".__('Remark Datetime')."</dt>";
		echo "<dd>".h($quotationRemark['QuotationRemark']['remark_datetime'])."</dd>";
		echo "<dt>".__('Remark Text')."</dt>";
		echo "<dd>".h($quotationRemark['QuotationRemark']['remark_text'])."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Quotation Remark'), array('action' => 'edit', $quotationRemark['QuotationRemark']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Quotation Remark'), array('action' => 'delete', $quotationRemark['QuotationRemark']['id']), array(), __('Are you sure you want to delete # %s?', $quotationRemark['QuotationRemark']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Quotation Remarks'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Quotation Remark'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Users'), array('controller' => 'users', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New User'), array('controller' => 'users', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add'))."</li>";
	echo "</ul>";
?> 
</div>
