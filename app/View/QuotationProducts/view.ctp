<div class="quotationProducts view">
<?php 
	echo "<h2>".__('Quotation Product')."</h2>";
	echo "<dl>";
		echo "<dt>".__('Quotation')."</dt>";
		echo "<dd>".$this->Html->link($quotationProduct['Quotation']['id'], array('controller' => 'quotations', 'action' => 'view', $quotationProduct['Quotation']['id']))."</dd>";
		echo "<dt>".__('Product')."</dt>";
		echo "<dd>".$this->Html->link($quotationProduct['Product']['name'], array('controller' => 'products', 'action' => 'view', $quotationProduct['Product']['id']))."</dd>";
		echo "<dt>".__('Product Unit Price')."</dt>";
		echo "<dd>".h($quotationProduct['QuotationProduct']['product_unit_price'])."</dd>";
		echo "<dt>".__('Product Quantity')."</dt>";
		echo "<dd>".h($quotationProduct['QuotationProduct']['product_quantity'])."</dd>";
		echo "<dt>".__('Product Total Price')."</dt>";
		echo "<dd>".h($quotationProduct['QuotationProduct']['product_total_price'])."</dd>";
		echo "<dt>".__('Currency')."</dt>";
		echo "<dd>".$this->Html->link($quotationProduct['Currency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $quotationProduct['Currency']['id']))."</dd>";
	echo "</dl>";
?> 
</div>
<div class="actions">
<?php
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		echo "<li>".$this->Html->link(__('Edit Quotation Product'), array('action' => 'edit', $quotationProduct['QuotationProduct']['id']))."</li>";
		echo "<li>".$this->Form->postLink(__('Delete Quotation Product'), array('action' => 'delete', $quotationProduct['QuotationProduct']['id']), array(), __('Are you sure you want to delete # %s?', $quotationProduct['QuotationProduct']['id']))."</li>";
		echo "<li>".$this->Html->link(__('List Quotation Products'), array('action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Quotation Product'), array('action' => 'add'))."</li>";
		echo "<br/>";
		echo "<li>".$this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Products'), array('controller' => 'products', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Product'), array('controller' => 'products', 'action' => 'add'))."</li>";
		echo "<li>".$this->Html->link(__('List Currencies'), array('controller' => 'currencies', 'action' => 'index'))."</li>";
		echo "<li>".$this->Html->link(__('New Currency'), array('controller' => 'currencies', 'action' => 'add'))."</li>";
	echo "</ul>";
?>
</div>
