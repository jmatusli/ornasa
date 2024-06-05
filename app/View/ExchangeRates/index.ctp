<div class="exchangeRates index">
	<h2><?php echo __('Exchange Rates'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<thead>
	<tr>
		<th><?php echo $this->Paginator->sort('application_date'); ?></th>
		<th class='centered'><?php echo $this->Paginator->sort('conversion_currency_id'); ?></th>
		<th class='centered'><?php echo $this->Paginator->sort('rate'); ?></th>
		<th class='centered'><?php echo $this->Paginator->sort('base_currency_id'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($exchangeRates as $exchangeRate): 
		//pr($exchangeRate);
		?>
	<tr>
		<td>
			<?php 
				$applicationdate=new DateTime($exchangeRate['ExchangeRate']['application_date']); 
				echo $applicationdate->format('d-m-Y');
			?>
		</td>		
		<td class='centered'>
			<?php echo $this->Html->link($exchangeRate['ConversionCurrency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $exchangeRate['ConversionCurrency']['id'])); ?>
		</td>
		<td class='centered'><?php echo h($exchangeRate['ExchangeRate']['rate']); ?>&nbsp;</td>
		<td class='centered'>
			<?php echo $this->Html->link($exchangeRate['BaseCurrency']['abbreviation'], array('controller' => 'currencies', 'action' => 'view', $exchangeRate['BaseCurrency']['id'])); ?>
		</td>
		<td class='actions'>
		<?php
			echo $this->Html->link(__('View'), array('action' => 'view', $exchangeRate['ExchangeRate']['id'])); 
			if ($bool_edit_permission){
				echo $this->Html->link(__('Edit'), array('action' => 'edit', $exchangeRate['ExchangeRate']['id'])); 
			}
			if ($bool_delete_permission){
				//echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $exchangeRate['ExchangeRate']['id']), array(), __('Are you sure you want to delete # %s?', $exchangeRate['ExchangeRate']['id']));
			}
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</tbody>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Exchange Rate'), array('action' => 'add')); ?></li>
		<br/>
	
	</ul>
</div>
