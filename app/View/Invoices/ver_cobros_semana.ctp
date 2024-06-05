<div class="invoices index fullwidth">
<?php 
	echo "<h2>".__('Facturas por Cobrar esta semana ')."</h2>";
	$reportTable="";
	$reportTable.= "<table cellpadding='0' cellspacing='0'>";
		$reportTable.="<thead>";
			$reportTable.="<tr>";
				$reportTable.="<th>Fecha de Vencimiento</th>";
				$reportTable.="<th>Cliente</th>";
				$reportTable.="<th>Factura/Orden</th>";
				$reportTable.="<th class='centered'>Precio Total</th>";
				$reportTable.="<th class='centered'>Abonado</th>";
				$reportTable.="<th class='centered'>Saldo Pendiente</th>";
				$reportTable.="<th>Fecha de Emisión</th>";
				//$reportTable.="<th>Días Vencidos</th>";
			$reportTable.="</tr>";
		$reportTable.="</thead>";
		
		$reportTable.="<tbody>";
		$facturarPorCobrarBody="";
		$totalCSInvoice=0;
		$totalCSPaid=0;
		$totalCSPending=0;
		$currentClientId=0;
		$totalForClient=0;
		foreach ($pendingInvoicesThisWeek as $pendingInvoice){
			$dueDate=new DateTime($pendingInvoice['Invoice']['due_date']);
			$invoiceDate=new DateTime($pendingInvoice['Invoice']['invoice_date']);
			
			$currencyClass="CScurrency";
			if ($pendingInvoice['Currency']['id']==CURRENCY_USD){
				$currencyClass="USDcurrency";
			}
			
			if ($pendingInvoice['Client']['id']==$currentClientId){
				$facturarPorCobrarBody.="<tr>";
			}
			else {
				if ($currentClientId>0){
					$facturarPorCobrarBody.="<tr class='bold'>";
						$facturarPorCobrarBody.="<td></td>"; 
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td>TOTAL</td>";
						$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalForClient."</span></td>";
						$facturarPorCobrarBody.="<td></td>";
					$facturarPorCobrarBody.="</tr>";
				}
				$totalForClient=0;
				$currentClientId=$pendingInvoice['Client']['id'];
				$facturarPorCobrarBody.="<tr class='topborder'>";
			}
			
				$facturarPorCobrarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
				$facturarPorCobrarBody.="<td>".$this->Html->link($pendingInvoice['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $pendingInvoice['Client']['id']))."</td>";
				$facturarPorCobrarBody.="<td>".$this->Html->link($pendingInvoice['Invoice']['invoice_code'], array('controller' => 'orders', 'action' => 'verVenta', $pendingInvoice['Order']['id']))."</td>";
				$facturarPorCobrarBody.="<td class='centered ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$pendingInvoice['Invoice']['total_price']."</span></td>";
				$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$pendingInvoice['Invoice']['paidCS']."</span></td>";
				$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$pendingInvoice['Invoice']['pendingCS']."</span></td>";
				if ($pendingInvoice['Currency']['id']==CURRENCY_CS){
						$totalCSInvoice+=$pendingInvoice['Invoice']['total_price'];
				}
				elseif ($pendingInvoice['Currency']['id']==CURRENCY_USD) {
					$totalCSInvoice+=$pendingInvoice['Invoice']['total_price']*$exchangeRateCurrent;
				}
				$totalCSPaid+=$pendingInvoice['Invoice']['paidCS'];
				$totalCSPending+=$pendingInvoice['Invoice']['pendingCS'];
				$totalForClient+=$pendingInvoice['Invoice']['pendingCS'];
				$facturarPorCobrarBody.="<td>".$invoiceDate->format('d-m-Y')."</td>";
				//$facturarPorCobrarBody.="<td>".$daysLate->format('%a')."</td>";
			$facturarPorCobrarBody.="</tr>";
		}	
			$facturarPorCobrarBody.="<tr class='bold'>";
				$facturarPorCobrarBody.="<td></td>"; 
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td>TOTAL</td>";
				$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalForClient."</span></td>";
				$facturarPorCobrarBody.="<td></td>";
			$facturarPorCobrarBody.="</tr>";
			
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
				
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSInvoice."</span></td>";
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPaid."</span></td>";
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
				//$totalRow.="<td></td>";	
			$totalRow.="</tr>";
			$reportTable.=$totalRow.$facturarPorCobrarBody.$totalRow;
		$reportTable.="</tbody>";
	$reportTable.="</table>";
	echo $reportTable;
	
	echo "<h2>".__('Facturas vencidas anteriormente ')."</h2>";
	$reportTable="";
	$reportTable.= "<table cellpadding='0' cellspacing='0'>";
		$reportTable.="<thead>";
			$reportTable.="<tr>";
				$reportTable.="<th>Fecha de Vencimiento</th>";
				$reportTable.="<th>Cliente</th>";
				$reportTable.="<th>Factura/Orden</th>";
				$reportTable.="<th class='centered'>Precio Total</th>";
				$reportTable.="<th class='centered'>Abonado</th>";
				$reportTable.="<th class='centered'>Saldo Pendiente</th>";
				$reportTable.="<th>Fecha de Emisión</th>";
				$reportTable.="<th>Días Vencidos</th>";
			$reportTable.="</tr>";
		$reportTable.="</thead>";
		
		$reportTable.="<tbody>";
		$facturarPorCobrarBody="";
		$totalCSInvoice=0;
		$totalCSPaid=0;
		$totalCSPending=0;
		
		$currentClientId=0;
		$totalForClient=0;
		
		foreach ($pendingInvoicesEarlier as $pendingInvoice){
			$dueDate=new DateTime($pendingInvoice['Invoice']['due_date']);
			$invoiceDate=new DateTime($pendingInvoice['Invoice']['invoice_date']);
			$currentDate= new DateTime(date('Y-m-d'));
			
			$daysLate=$currentDate->diff($dueDate);
			$currencyClass="CScurrency";
			if ($pendingInvoice['Currency']['id']==CURRENCY_USD){
				$currencyClass="USDcurrency";
			}
			
			if ($pendingInvoice['Client']['id']==$currentClientId){
				$facturarPorCobrarBody.="<tr>";
			}
			else {
				if ($currentClientId>0){
					$facturarPorCobrarBody.="<tr class='bold'>";
						$facturarPorCobrarBody.="<td></td>"; 
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td>TOTAL</td>";
						$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalForClient."</span></td>";
						$facturarPorCobrarBody.="<td></td>";
						$facturarPorCobrarBody.="<td></td>";
					$facturarPorCobrarBody.="</tr>";
				}
				$totalForClient=0;
				$currentClientId=$pendingInvoice['Client']['id'];
				$facturarPorCobrarBody.="<tr class='topborder'>";
			}
				$facturarPorCobrarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
				$facturarPorCobrarBody.="<td>".$this->Html->link($pendingInvoice['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', $pendingInvoice['Client']['id']))."</td>";
				$facturarPorCobrarBody.="<td>".$this->Html->link($pendingInvoice['Invoice']['invoice_code'], array('controller' => 'orders', 'action' => 'verVenta', $pendingInvoice['Order']['id']))."</td>";
				$facturarPorCobrarBody.="<td class='centered ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$pendingInvoice['Invoice']['total_price']."</span></td>";
				$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$pendingInvoice['Invoice']['paidCS']."</span></td>";
				$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$pendingInvoice['Invoice']['pendingCS']."</span></td>";
				if ($pendingInvoice['Currency']['id']==CURRENCY_CS){
						$totalCSInvoice+=$pendingInvoice['Invoice']['total_price'];
				}
				elseif ($pendingInvoice['Currency']['id']==CURRENCY_USD) {
					$totalCSInvoice+=$pendingInvoice['Invoice']['total_price']*$exchangeRateCurrent;
				}
				$totalCSPaid+=$pendingInvoice['Invoice']['paidCS'];
				$totalCSPending+=$pendingInvoice['Invoice']['pendingCS'];
				$totalForClient+=$pendingInvoice['Invoice']['pendingCS'];
				$facturarPorCobrarBody.="<td>".$invoiceDate->format('d-m-Y')."</td>";
				$facturarPorCobrarBody.="<td>".$daysLate->format('%a')."</td>";
			$facturarPorCobrarBody.="</tr>";
		}	
			$facturarPorCobrarBody.="<tr class='bold'>";
				$facturarPorCobrarBody.="<td></td>"; 
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td>TOTAL</td>";
				$facturarPorCobrarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$totalForClient."</span></td>";
				$facturarPorCobrarBody.="<td></td>";
				$facturarPorCobrarBody.="<td></td>";
			$facturarPorCobrarBody.="</tr>";
		
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";	
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSInvoice."</span></td>";
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPaid."</span></td>";
				$totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
				$totalRow.="<td></td>";	
			$totalRow.="</tr>";
			$reportTable.=$totalRow.$facturarPorCobrarBody.$totalRow;
		$reportTable.="</tbody>";
	$reportTable.="</table>";
	echo $reportTable;
	
?>
</div>
<!--div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New Invoice'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List Orders'), array('controller' => 'orders', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Order'), array('controller' => 'orders', 'action' => 'add')); ?> </li>
	</ul>
</div-->
<script>
	
	function formatCurrencies(){
		$("td.number span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				var boolnegative=true;
				//$(this).parent().prepend("-");
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).prepend("-");
			}
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("C$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("C$");
			}
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency span.amountright").each(function(){
			var boolnegative=false;
			if (parseFloat($(this).text())<0){
				//$(this).parent().prepend("-");
				var boolnegative=true;
			}
			$(this).number(true,2);
			if (boolnegative){
				$(this).parent().find('span.currency').text("US$");
				$(this).prepend("-");
			}
			else {
				$(this).parent().find('span.currency').text("US$");
			}
		});
	};
	
	$(document).ready(function(){
		formatCurrencies();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>