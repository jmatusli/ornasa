<div class="productCategories view">
<h2><?php echo __('Exchange Rate'); ?></h2>
	<dl>
		<dt><?php echo __('Application Date'); ?></dt>
		<dd>
			<?php 
				$applicationdate=new DateTime($exchangeRate['ExchangeRate']['application_date']); 
				echo $applicationdate->format('d-m-Y');
			?>
		</dd>
		<dt><?php echo __('Conversion Currency'); ?></dt>
		<dd>
			<?php echo h($exchangeRate['ConversionCurrency']['abbreviation']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Rate'); ?></dt>
		<dd>
			<?php echo h($exchangeRate['ExchangeRate']['rate']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Base Currency'); ?></dt>
		<dd>
			<?php echo h($exchangeRate['BaseCurrency']['abbreviation']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_edit_permission){
			echo "<li>".$this->Html->link(__('Edit Exchange Rate'), array('action' => 'edit', $exchangeRate['ExchangeRate']['id']))." </li>";
		}
		echo "<li>".$this->Html->link(__('List Exchange Rates'), array('action' => 'index'))." </li>";
		if ($bool_add_permission){
			echo "<li>".$this->Html->link(__('New Exchange Rate'), array('action' => 'add'))." </li>";
		}
		
	echo "</ul>";
?>
</div>