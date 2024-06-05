<div class="productCategories view">
<h2><?php echo __('Product Category'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($productCategory['ProductCategory']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Name'); ?></dt>
		<dd>
			<?php echo h($productCategory['ProductCategory']['name']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Product Category'), array('action' => 'edit', $productCategory['ProductCategory']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Product Category'), array('action' => 'delete', $productCategory['ProductCategory']['id']), array(), __('Are you sure you want to delete # %s?', $productCategory['ProductCategory']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Product Categories'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product Category'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Product Types'), array('controller' => 'product_types', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add')); ?> </li>
	</ul>
</div>
<div class="related">
	<h3><?php echo __('Related Product Types'); ?></h3>
	<?php if (!empty($productCategory['ProductType'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Id'); ?></th>
		<th><?php echo __('Name'); ?></th>
		<th><?php echo __('Description'); ?></th>
		<th><?php echo __('Product Category Id'); ?></th>
		<th><?php echo __('Created'); ?></th>
		<th><?php echo __('Modified'); ?></th>
		<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($productCategory['ProductType'] as $productType): ?>
		<tr>
			<td><?php echo $productType['id']; ?></td>
			<td><?php echo $productType['name']; ?></td>
			<td><?php echo $productType['description']; ?></td>
			<td><?php echo $productType['product_category_id']; ?></td>
			<td><?php echo $productType['created']; ?></td>
			<td><?php echo $productType['modified']; ?></td>
			<td class="actions">
				<?php echo $this->Html->link(__('View'), array('controller' => 'product_types', 'action' => 'view', $productType['id'])); ?>
				<?php echo $this->Html->link(__('Edit'), array('controller' => 'product_types', 'action' => 'edit', $productType['id'])); ?>
				<?php echo $this->Form->postLink(__('Delete'), array('controller' => 'product_types', 'action' => 'delete', $productType['id']), array(), __('Are you sure you want to delete # %s?', $productType['id'])); ?>
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

	<div class="actions">
		<ul>
			<li><?php echo $this->Html->link(__('New Product Type'), array('controller' => 'product_types', 'action' => 'add')); ?> </li>
		</ul>
	</div>
</div>
