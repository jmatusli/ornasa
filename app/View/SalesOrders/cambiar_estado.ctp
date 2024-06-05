<script>
	
	$('body').on('change','#SalesOrderQuotationId',function(){	
		var quotationid=$(this).children("option").filter(":selected").val();
		var quotationcode=$(this).children("option").filter(":selected").text();
		if (quotationid>0){
			loadProductsForQuotation(quotationid);
			loadQuotationInfo(quotationid);
			$('#SalesOrderSalesOrderCode').val(quotationcode);
			setQuotationCurrency(quotationid);
		}
	});	
		
	function loadProductsForQuotation(quotationid){
	<?php 
		if ($role_id==ROLE_ADMIN){ 
			echo "var editpermissiondenied=0;";
		}
		else {
			echo "var editpermissiondenied=1;";
		}
	?>
		$('#products').empty();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>quotations/getquotationproducts/',
			data:{"quotationid":quotationid,"editpermissiondenied":editpermissiondenied},
			cache: false,
			type: 'POST',
			success: function (products) {
				$('#products').html(products);
			},
			error: function(e){
				$('#products').html(e.responseText);
				console.log(e);
			}
		});
	}
	function loadQuotationInfo(quotationid){
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>quotations/getquotationinfo/',
			data:{"quotationid":quotationid},
			cache: false,
			type: 'POST',
			success: function (quotationinfo) {
				$('div.righttop.quotation').html(quotationinfo);
				$('div.righttop.quotation').removeClass('hidden');
			},
			error: function(e){
				$('div.righttop').html(e.responseText);
				console.log(e);
			}
		});
	}
	function setQuotationCurrency(quotationid){
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>quotations/getquotationcurrencyid/',
			data:{"quotationid":quotationid},
			cache: false,
			type: 'POST',
			success: function (quotationcurrencyid) {
				$('#SalesOrderCurrencyId').val(quotationcurrencyid);
				$('#SalesOrderCurrencyId').trigger('change');
			},
			error: function(e){
				alert(e.responseText);
				console.log(e);
			}
			
		});
	}
		
	$('body').on('change','.productid',function(){	
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	$('body').on('change','.productquantity',function(){	
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	$('body').on('change','.productunitprice',function(){	
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	function calculateRow(rowid) {    
		var currentrow=$('#products').find("[row='" + rowid + "']");
		
		var quantity=parseFloat(currentrow.find('td.productquantity div input').val());
		var unitprice=parseFloat(currentrow.find('td.productunitprice div input').val());
		
		var totalprice=quantity*unitprice;
		
		currentrow.find('td.producttotalprice div input').val(roundToTwo(totalprice));
	}
	function calculateTotal(){
		//var booliva=$('#QuotationBoolIva').is(':checked');
		var subtotalPrice=0;
		//var ivaPrice=0
		//var totalPrice=0
		$("#productos tbody tr:not(.hidden)").each(function() {
			var currentProduct = $(this).find('td.producttotalprice div input');
			if (!isNaN(currentProduct.val())){
				var currentPrice = parseFloat(currentProduct.val());
				subtotalPrice += currentPrice;
			}
		});
		$('#subtotal span.amountright').text(subtotalPrice);
		$('tr.totalrow.subtotal td.totalprice div input').val(subtotalPrice);
		
		//if (booliva){
		//	ivaPrice=roundToTwo(0.15*subtotalPrice);
		//}
		//$('#iva span.amount').text(ivaPrice);
		//$('tr.totalrow.iva td.totalprice div input').val(ivaPrice);
		//totalPrice=subtotalPrice + ivaPrice;
		//$('#total span.amount').text(totalPrice);
		//$('tr.totalrow.total td.totalprice div input').val(totalPrice);	
		return false;
	}
		
	function formatCurrencies(){
		$("td.amount span.amountright").each(function(){
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,2);
		});
		var currencyid=$('#SalesOrderCurrencyId').children("option").filter(":selected").val();
		if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text('C$ ');
		}
		else if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text('US$ ');			
		}
	}
	
	$(document).ready(function(){	
		formatCurrencies();
		if ($('#SalesOrderQuotationId').val()==0){
			$('div.righttop.quotation').addClass('hidden');
		}
		else {
			var quotationid=$('#SalesOrderQuotationId').children("option").filter(":selected").val();
			//loadProductsForQuotation(quotationid);
			loadQuotationInfo(quotationid);
			//setQuotationCurrency(quotationid);
		}
		calculateTotal();
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
</script>
<div class="salesOrders form">
<?php 
	echo $this->Form->create('SalesOrder'); 
	echo "<fieldset>"; 
		echo "<legend>".__('Cambiar Estado de la Orden de Venta')."</legend>"; 
		echo $this->Form->input('id');
		//echo $this->Form->input('client_id',array('default'=>$relatedQuotation['Quotation']['client_id'],'empty'=>array('0'=>'Seleccione Cliente')));
		echo $this->Form->input('quotation_id',array('class'=>'fixed','empty'=>array('0'=>'Seleccione Cotización')));
		echo $this->Form->input('sales_order_date',array('dateFormat'=>'DMY','class'=>'fixed'));
		echo $this->Form->input('sales_order_code',array('readonly'=>'readonly'));
		echo $this->Form->input('bool_annulled',array('onclick'=>'return false;'));
		echo $this->Form->input('currency_id',array('class'=>'fixed'));
	echo "</fieldset>";
	echo "<div class='righttop'>";
		echo "<dl>";
			echo "<dt>Subtotal</dt>";
			echo "<dd id='subtotal'><span class='currency'></span><span class='amountright'>".$this->request->data['SalesOrder']['price_subtotal']."</span></dd>";
		echo "</dl>";
	echo "</div>";
	echo "<div class='righttop quotation'>";
		$quotationDate=new DateTime($relatedQuotation['Quotation']['quotation_date']);
		echo "<h3>Resumen Cotización ".$relatedQuotation['Quotation']['quotation_code']."</h3>";
		echo "<dl>";
			echo "<dt>Ejecutivo</dt>";
			echo "<dd>".$this->Html->link($relatedQuotation['User']['username'],array('controller'=>'users','action'=>'view',$relatedQuotation['User']['id']))."</dd>";
			echo "<dt>Cliente</dt>";
			echo "<dd>".$this->Html->link($relatedQuotation['Client']['name'],array('controller'=>'clients','action'=>'view',$relatedQuotation['Client']['id']))."</dd>";
			echo "<dt>Fecha Cotización</dt>";
			echo "<dd>".$quotationDate->format('d-m-Y')."</dd>";
		echo "</dl>";
	echo "</div>";
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<!--li><?php //echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('SalesOrder.id')), array(), __('Are you sure you want to delete # %s?', $this->Form->value('SalesOrder.id'))); ?></li-->
		<li><?php echo $this->Html->link(__('List Sales Orders'), array('action' => 'index')); ?></li>
		<br/>
	<?php
		if ($bool_quotation_index_permission){
			echo "<li>".$this->Html->link(__('List Quotations'), array('controller' => 'quotations', 'action' => 'index'))."</li>";
		}
		if ($bool_quotation_index_permission){
			echo "<li>".$this->Html->link(__('New Quotation'), array('controller' => 'quotations', 'action' => 'add'))."</li>";
		}
	?>	
	</ul>
</div>
<div>
<?php
	$productsForSalesorder=$this->request->data['SalesOrderProduct'];
	echo "<div id='products'>";
		echo "<table id='productos'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product')."</th>";
					echo "<th>".__('Description')."</th>";
					echo "<th class='centered'>".__('Product Quantity')."</th>";
					echo "<th class='centered>".__('Unit Price')."</th>";
					echo "<th class='centered>".__('Total Price')."</th>";
					echo "<th>".__('Status')."</th>";
				echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			$i=0;
			foreach ($this->request->data['SalesOrderProduct'] as $product){
				echo "<tr row='".$i."'>";
					echo "<td class='productid'>".$this->Form->input('SalesOrderProduct.'.$i.'.product_id',array('label'=>false,'class'=>'fixed'))."</td>";
					echo "<td class='productdescription'>".$this->Form->input('SalesOrderProduct.'.$i.'.product_description',array('label'=>false,'readonly'=>'readonly'))."</td>";
					echo "<td class='productquantity amount'>".$this->Form->input('SalesOrderProduct.'.$i.'.product_quantity',array('label'=>false,'readonly'=>'readonly','type'=>'numeric'))."</td>";
					echo "<td class='productunitprice amount'><span class='currency'></span>".$this->Form->input('SalesOrderProduct.'.$i.'.product_unit_price',array('type'=>'decimal','label'=>false,'readonly'=>'readonly'))."</td>";
					echo "<td class='producttotalprice amount'><span class='currency'></span>".$this->Form->input('SalesOrderProduct.'.$i.'.product_total_price',array('type'=>'decimal','label'=>false,'default'=>$product['product_total_price'],'readonly'=>'readonly'))."</td>";
					echo "<td class='productstatus'>".$this->Form->input('SalesOrderProduct.'.$i.'.sales_order_product_status_id',array('label'=>false,'default'=>PRODUCT_STATUS_REGISTERED))."</td>";
				echo "</tr>";
				$i++;
			}
				echo "<tr class='totalrow subtotal'>";
					echo "<td>Subtotal</td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td></td>";
					echo "<td class='totalprice amount right'><span class='currency'></span>".$this->Form->input('price_subtotal',array('label'=>false,'type'=>'decimal','readonly'=>'readonly','default'=>'0'))."</td>";
					echo "<td></td>";
					echo "<td></td>";
				echo "</tr>";	
			echo "</tbody>";
		echo "</table>";
	echo "</div>";
	echo $this->Form->end(__('Submit')); 
?>
</div>