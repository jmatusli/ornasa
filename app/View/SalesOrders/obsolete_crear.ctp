<script>
	$('body').on('change','#SalesOrderSalesOrderDateDay',function(){	
		updateExchangeRate();
	});
	$('body').on('change','#SalesOrderSalesOrderDateMonth',function(){	
		updateExchangeRate();
	});
	$('body').on('change','#SalesOrderSalesOrderDateYear',function(){	
		updateExchangeRate();
	});
	function updateExchangeRate(){
		var selectedday=$('#SalesOrderSalesOrderDateDay').children("option").filter(":selected").val();
		var selectedmonth=$('#SalesOrderSalesOrderDateMonth').children("option").filter(":selected").val();
		var selectedyear=$('#SalesOrderSalesOrderDateYear').children("option").filter(":selected").val();
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>exchange_rates/getexchangerate/',
			data:{"selectedday":selectedday,"selectedmonth":selectedmonth,"selectedyear":selectedyear},
			cache: false,
			type: 'POST',
			success: function (exchangerate) {
				$('#SalesOrderExchangeRate').val(exchangerate);
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
			}
		});
	}
	
	$('body').on('change','#SalesOrderBoolAnnulled',function(){	
		if ($(this).is(':checked')){
			$('#products').empty();
			$('#subtotal span.amountright').text('0');
		}
		else {
			$('#SalesOrderQuotationId').trigger('change');
		}
	});
	
	$('body').on('change','#SalesOrderBoolAuthorized',function(){	
		if ($(this).is(':checked')){
			$('#SalesOrderAuthorizingUserId').val('<?php echo $user_id; ?>');
			$('td.salesorderproductstatusid div select').each(function(){
				var boolNoProduction=$(this).closest('tr').find('td.boolnoproduction').is(':checked');
				if (boolNoProduction){
					$(this).find('option:not(:selected)').attr('disabled', false);
					$(this).val(<?php echo PRODUCT_STATUS_READY_FOR_DELIVERY; ?>);
					$(this).find('option:not(:selected)').attr('disabled', true);
					
				}
				else {
					$(this).find('option:not(:selected)').attr('disabled', false);
					$(this).val(<?php echo PRODUCT_STATUS_AUTHORIZED; ?>);
					$(this).find('option:not(:selected)').attr('disabled', true);
				}
			});		
		}
		else {
			$('#SalesOrderAuthorizingUserId').val('0');
			$('td.salesorderproductstatusid div select').each(function(){
				$(this).find('option:not(:selected)').attr('disabled', false);
				$(this).val(<?php echo PRODUCT_STATUS_REGISTERED; ?>);
				$(this).find('option:not(:selected)').attr('disabled', true);				
			});		
		}
	});
	
	$('body').on('change','#SalesOrderQuotationId',function(){	
		var quotationid=$(this).children("option").filter(":selected").val();
		var quotationcode=$(this).children("option").filter(":selected").text();
		if (quotationid>0){
			$('#SalesOrderSalesOrderCode').val(quotationcode);
			setQuotationCurrency(quotationid);
			setQuotationIva(quotationid);
			loadQuotationInfo(quotationid);
			loadProductsForQuotation(quotationid);
		}
	});	
		
	function setQuotationCurrency(quotationid){
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>quotations/getquotationcurrencyid/',
			data:{"quotationid":quotationid},
			cache: false,
			type: 'POST',
			success: function (quotationcurrencyid) {
				$('#SalesOrderCurrencyId').val(quotationcurrencyid);
				updateCurrencies();
			},
			error: function(e){
				alert(e.responseText);
				console.log(e);
			}
		});
	}
	function setQuotationIva(quotationid){
		$.ajax({
			url: '<?php echo $this->Html->url('/'); ?>quotations/getquotationiva/',
			data:{"quotationid":quotationid},
			cache: false,
			type: 'POST',
			success: function (quotationiva) {
				$('#SalesOrderBoolIva').prop('checked',quotationiva);
				updateCurrencies();
			},
			error: function(e){
				alert(e.responseText);
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
				$('div.topright.quotation').html(quotationinfo);
				$('div.topright.quotation').removeClass('hidden');
			},
			error: function(e){
				console.log(e);
				alert(e.responseText);
			}
		});
	}
	function loadProductsForQuotation(quotationid){
		var editpermissiondenied=0;
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
	
	$('body').on('change','#SalesOrderRemarkWorkingDaysBeforeReminder',function(){
		var working_days_before_reminder=$(this).val();
		if (working_days_before_reminder<1||working_days_before_reminder>10){
			alert("El número de días laborales debe estar entre 1 y 10");
		}
		else {
			var reminderdatemoment = addWeekdays(moment(), working_days_before_reminder);
			var reminderdateyear=moment(reminderdatemoment).format('YYYY');
			var reminderdatemonth=moment(reminderdatemoment).format('MM');
			var reminderdateday=moment(reminderdatemoment).format('DD');
			
			$('#SalesOrderRemarkReminderDateDay').val(reminderdateday);
			$('#SalesOrderRemarkReminderDateMonth').val(reminderdatemonth);
			$('#SalesOrderRemarkReminderDateYear').val(reminderdateyear);
		}		
	});
	
	$('body').on('click','.removeItem',function(){	
		var tableRow=$(this).closest('tr').remove();
		calculateTotal();
	});	
	$('body').on('change','.productid',function(){	
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	$('body').on('change','.productquantity',function(){	
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=Math.round($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	$('body').on('change','.productunitprice',function(){	
		if (!$(this).find('div input').val()||isNaN($(this).find('div input').val())){
			$(this).find('div input').val(0);
		}
		else {
			var roundedValue=roundToTwo($(this).find('div input').val());
			$(this).find('div input').val(roundedValue);
		}
		calculateRow($(this).closest('tr').attr('row'));
		calculateTotal();
	});	
	
	$('body').on('change','.boolnoproduction div input',function(){	
		var currentrow=$(this).closest('tr');
		if ($(this).is(':checked')){
			$('td.salesorderproductstatusid option:not(:selected)').attr('disabled', false);
			if ($('#SalesOrderBoolAuthorized').is(':checked')){	
				currentrow.find('td.salesorderproductstatusid div select').val(<?php echo PRODUCT_STATUS_READY_FOR_DELIVERY; ?>);
			}
			else {
				currentrow.find('td.salesorderproductstatusid div select').val(<?php echo PRODUCT_STATUS_REGISTERED; ?>);
			}
			$('td.salesorderproductstatusid option:not(:selected)').attr('disabled', true);
		}
		else {
			$('td.salesorderproductstatusid option:not(:selected)').attr('disabled', false);
			if ($('#SalesOrderBoolAuthorized').is(':checked')){
				currentrow.find('td.salesorderproductstatusid div select').val(<?php echo PRODUCT_STATUS_AUTHORIZED; ?>);
			}
			else {
				currentrow.find('td.salesorderproductstatusid div select').val(<?php echo PRODUCT_STATUS_REGISTERED; ?>);
			}
			$('td.salesorderproductstatusid option:not(:selected)').attr('disabled', true);
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
	
	$('body').on('change','#SalesOrderBoolIva',function(){
		//calculateTotal();
	});
	
	function calculateTotal(){
		var totalProductQuantity=0;
		var subtotalPrice=0;
		var ivaPrice=0
		var totalPrice=0
		$("#products tbody tr:not(.hidden)").each(function() {
			var currentProductQuantity = $(this).find('td.productquantity div input');
			if (!isNaN(currentProductQuantity.val())){
				var currentQuantity = parseFloat(currentProductQuantity.val());
				totalProductQuantity += currentQuantity;
			}
			
			var currentProduct = $(this).find('td.producttotalprice div input');
			if (!isNaN(currentProduct.val())){
				var currentPrice = parseFloat(currentProduct.val());
				subtotalPrice += currentPrice;
			}
			if ($(this).find('td.booliva div input').is(':checked')){
				$(this).find('td.iva div input').val(roundToTwo(0.15*currentPrice));
				ivaPrice+=roundToTwo(0.15*currentPrice);
			}
			else {
				$(this).find('td.iva div input').val(0);
			}
		});
		
		$('tr.totalrow.subtotal td.productquantity span').text(totalProductQuantity.toFixed(0));
		
		$('#subtotal span.amountright').text(roundToTwo(subtotalPrice));
		$('tr.totalrow.subtotal td.totalprice div input').val(roundToTwo(subtotalPrice.toFixed(2)));
		
		$('#iva span.amountright').text(roundToTwo(ivaPrice));
		$('tr.totalrow.iva td.totalprice div input').val(roundToTwo(ivaPrice.toFixed(2)));
		totalPrice=subtotalPrice + ivaPrice;
		$('#total span.amountright').text(roundToTwo(totalPrice));
		$('tr.totalrow.total td.totalprice div input').val(roundToTwo(totalPrice.toFixed(2)));	
		
		return false;
	}
	
	function updateCurrencies(){
		var currencyid=$('#SalesOrderCurrencyId').val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
	}
	
	$('body').on('change','#SalesOrderCurrencyId',function(){	
		var currencyid=$(this).val();
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('span.currency').text("US$");
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('span.currency').text("C$");
		}
		// now update all prices
		var exchangerate=parseFloat($('#SalesOrderExchangeRate').val());
		if (currencyid==<?php echo CURRENCY_USD; ?>){
			$('td.productunitprice').each(function(){
				var originalprice= $(this).find('div input').val();
				var newprice=roundToTwo(originalprice/exchangerate);
				$(this).find('div input').val(newprice);
				//$(this).find('div input').trigger('change');
				//$(this).trigger('change');
				calculateRow($(this).closest('tr').attr('row'));
			});
		}
		else if (currencyid==<?php echo CURRENCY_CS; ?>){
			$('td.productunitprice').each(function(){
				var originalprice= $(this).find('div input').val();
				var newprice=roundToTwo(originalprice*exchangerate);
				$(this).find('div input').val(newprice);
				//$(this).find('div input').trigger('change');
				//$(this).trigger('change');
				calculateRow($(this).closest('tr').attr('row'));
			});
		}
		calculateTotal();
	});	
	
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
			$('div.topright.quotation').addClass('hidden');
		}
		else {
			$('#SalesOrderQuotationId').trigger('change');	
		}
		$('#SalesOrderRemarkWorkingDaysBeforeReminder').trigger('change');
		$('select.fixed option:not(:selected)').attr('disabled', true);
	});
	
	
</script>
<div class="salesOrders form fullwidth">
<?php
	echo $this->Form->create('SalesOrder'); 
	echo "<fieldset>";
		echo "<legend>".__('Add Sales Order')."</legend>";
		echo "<div class='container-fluid'>";
			echo "<div class='rows'>";	
				echo "<div class='col-md-4'>";
					echo $this->Form->input('quotation_id',array('default'=>$quotation_id,'empty'=>array('0'=>'Seleccione Cotización')));
					echo $this->Form->input('sales_order_date',array('dateFormat'=>'DMY'));
					echo $this->Form->input('exchange_rate',array('default'=>$exchangeRateSalesOrder,'readonly'=>'readonly'));
					echo $this->Form->input('sales_order_code',array('readonly'=>'readonly'));
					
					//echo $this->Form->input('bool_annulled',array('default'=>false));
					echo $this->Form->input('bool_iva',array('label'=>'IVA','default'=>true,'type'=>'checkbox','onclick'=>'return false'));
					echo $this->Form->input('currency_id',array('default'=>CURRENCY_CS));
					if ($bool_autorizar_permission){
						echo $this->Form->input('bool_authorized',array('label'=>'Autorizada?','checked'=>false,'type'=>'checkbox'));
					}
					else {
						echo $this->Form->input('bool_authorized',array('label'=>'Autorizada?','checked'=>false,'onclick'=>'return false;','onkeydown'=>'return false;'));
					}
					echo $this->Form->input('authorizing_user_id',array('label'=>'Persona quien autoriza','default'=>'0','type'=>'hidden'));
				echo "</div>";
				echo "<div class='col-md-5'>";
					echo $this->Form->input('SalesOrderRemark.user_id',array('label'=>'Vendedor','value'=>$loggedUserId,'type'=>'hidden'));
					echo $this->Form->input('SalesOrderRemark.remark_text',array('rows'=>2,'default'=>'Orden de Venta creada'));
					echo $this->Form->input('SalesOrderRemark.working_days_before_reminder',array('default'=>5));
					echo $this->Form->input('SalesOrderRemark.reminder_date',array('type'=>'date','dateFormat'=>'DMY'));
					echo $this->Form->input('SalesOrderRemark.action_type_id',array('default'=>ACTION_TYPE_OTHER));
				echo "</div>";
				echo "<div class='col-md-3'>";
					echo "<div class='topright'>";
						echo "<dl>";
							echo "<dt>Subtotal</dt>";
							echo "<dd id='subtotal'><span class='currency'></span><span class='amountright'>0</span></dd>";
							echo "<dt>IVA</dt>";
							echo "<dd id='iva'><span class='currency'></span><span class='amountright'>0</span></dd>";
							echo "<dt>Total</dt>";
							echo "<dd id='total'><span class='currency'></span><span class='amountright'>0</span></dd>";
						echo "</dl>";
					echo "</div>";
					echo "<div class='topright quotation'>";
					echo "</div>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
		echo "<div id='products'>";
		echo "</div>";
		echo $this->Form->input('observation');	
	echo "</fieldset>";
	echo $this->Form->end(__('Submit')); 
?>
</div>
<?php 
/*
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
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
		<!--li><?php echo $this->Html->link(__('List Sales Order Products'), array('controller' => 'sales_order_products', 'action' => 'index')); ?> </li-->
		<!--li><?php echo $this->Html->link(__('New Sales Order Product'), array('controller' => 'sales_order_products', 'action' => 'add')); ?> </li-->
	</ul>
</div>
*/
?>