<div class="orders index sales fullwidth">
<script>
	function formatNumbers(){
		$("td.number span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).parent().prepend("-");
			}
			$(this).number(true,0,'.',',');
		});
	}
	
	function formatCSCurrencies(){
		$("td.CScurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("C$");
		});
	}
	
	function formatUSDCurrencies(){
		$("td.USDcurrency").each(function(){
			
			if (parseFloat($(this).find('.amountright').text())<0){
				$(this).find('.amountright').prepend("-");
			}
			$(this).find('.amountright').number(true,2);
			$(this).find('.currency').text("US$");
		});
	}
  
  function formatPercentages(){
		$("td.percentage span").each(function(){
			$(this).number(true,2);
			$(this).parent().append(" %");
		});
	}
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
    formatPercentages();
	});
</script>
<?php 
  $salesTableHeader="<thead>";
		$salesTableHeader.="<tr>";
			$salesTableHeader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$salesTableHeader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$salesTableHeader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
      $salesTableHeader.="<th class='centered'>".__('Precio Subtotal')."</th>";
      $salesTableHeader.="<th class='centered'>".__('Precio IVA')."</th>";
			$salesTableHeader.="<th class='centered'>".__('Subtotal + IVA')."</th>";
			$salesTableHeader.="<th class='centered'>".__('Precio Total')."</th>";      
		$salesTableHeader.="</tr>";
	$salesTableHeader.="</thead>";
	
	$salesTableBody="<tbody>";
  
  $totalPriceSubtotalCS=0;
  $totalPriceIvaCS=0;
	$totalPriceTotalCS=0;
	
  $totalPriceSubtotalUSD=0;
  $totalPriceIvaUSD=0;
	$totalPriceTotalUSD=0;
  
	foreach ($sales as $sale){
    //if ($sale['Order']['id'] == 1863){
    //  pr($sale);
    //}
    $boolError=(abs($sale['Invoice']['total_price'] - $sale['Invoice']['sub_total_price'] - $sale['Invoice']['IVA_price']) > 0.005);
    //echo "boolError for sale number ".$sale['Order']['order_code']." is ".$boolError."<br/>";
    if (($displayOptionId==ORDERS_ERROR && $boolError) || $displayOptionId==ORDERS_ALL){
      $orderdate=new DateTime($sale['Order']['order_date']);
      $invoiceCode=$sale['Order']['order_code'];
      
      if ($sale['Invoice']['currency_id']===CURRENCY_CS){
        $totalPriceSubtotalCS+=$sale['Invoice']['sub_total_price'];
        $totalPriceIvaCS+=$sale['Invoice']['IVA_price'];
        $totalPriceTotalCS+=$sale['Invoice']['total_price'];
      }
      elseif ($sale['Invoice']['currency_id']===CURRENCY_CS){
        $totalPriceSubtotalUSD+=$sale['Invoice']['sub_total_price'];
        $totalPriceIvaUSD+=$sale['Invoice']['IVA_price'];
        $totalPriceTotalUSD+=$sale['Invoice']['total_price']; 
      }
      
      if ($sale['Invoice']['bool_annulled']==1){
        $invoiceCode.=" (Anulado)";
        $salesTableBody.="<tr class='italic'".($boolError?" style='color:#f00;'":"").">";		
      }
      else {
        $salesTableBody.="<tr".($boolError?" style='color:#f00;'":"").">";		
      }
        $salesTableBody.="<td>".$orderdate->format('d-m-Y')."</td>";
        $salesTableBody.="<td>".$this->html->Link($invoiceCode,['action'=>'verVenta',$sale['Order']['id']])."</td>";
        $salesTableBody.="<td>".$sale['ThirdParty']['company_name']."</td>";        
        $salesTableBody.="<td class='centered ".($sale['Invoice']['currency_id']===CURRENCY_CS?"CScurrency":($sale['Invoice']['currency_id']===CURRENCY_USD?"USDcurrency":""))."'><span class='currency'></span><span class='amountright'>".$sale['Invoice']['sub_total_price']."</span></td>";
        $salesTableBody.="<td class='centered ".($sale['Invoice']['currency_id']===CURRENCY_CS?"CScurrency":($sale['Invoice']['currency_id']===CURRENCY_USD?"USDcurrency":""))."'><span class='currency'></span><span class='amountright'>".$sale['Invoice']['IVA_price']."</span></td>";
        $salesTableBody.="<td class='centered ".($sale['Invoice']['currency_id']===CURRENCY_CS?"CScurrency":($sale['Invoice']['currency_id']===CURRENCY_USD?"USDcurrency":""))."'><span class='currency'></span><span class='amountright'>".($sale['Invoice']['sub_total_price']+$sale['Invoice']['IVA_price'])."</span></td>";
        $salesTableBody.="<td class='centered ".($sale['Invoice']['currency_id']===CURRENCY_CS?"CScurrency":($sale['Invoice']['currency_id']===CURRENCY_USD?"USDcurrency":""))."'><span class='currency'></span><span class='amountright'>".$sale['Invoice']['total_price']."</span></td>";
      $salesTableBody.="</tr>";
    }    
  }
  $totalRow="";  
  if ($totalPriceSubtotalCS>0){
    $totalRow.="<tr class='totalrow'>";
      $totalRow.="<td>Total C$</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceSubtotalCS."</span></td>";
      $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceIvaCS."&nbsp;</span></td>";
      $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".($totalPriceSubtotalCS+$totalPriceIvaCS)."&nbsp;</span></td>";
      $totalRow.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".$totalPriceTotalCS."</span></td>";
    $totalRow.="</tr>";
  }
  if ($totalPriceSubtotalUSD>0){
    $totalRow.="<tr class='totalrow'>";
      $totalRow.="<td>Total US$</td>";
      $totalRow.="<td></td>";
      $totalRow.="<td></td>";
      $totalRow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalPriceSubtotalUSD."</span></td>";
      $totalRow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalPriceIvaUSD."&nbsp;</span></td>";
      $totalRow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".($totalPriceSubtotalUSD+$totalPriceIvaUSD)."&nbsp;</span></td>";
      $totalRow.="<td class='centered USDcurrency'><span class='currency'></span><span class='amountright'>".$totalPriceTotalUSD."</span></td>";
    $totalRow.="</tr>";
  }
	
	$salesTableBody=$totalRow.$salesTableBody.$totalRow."</tbody>";

	$salesTable="<table cellpadding='0' cellspacing='0' id='ventas'>".$salesTableHeader.$salesTableBody."</table>";
	/*
	$remissionsTableHeader="<thead>";
		$remissionsTableHeader.="<tr>";
			$remissionsTableHeader.="<th>".$this->Paginator->sort('order_date',__('Exit Date'))."</th>";
			$remissionsTableHeader.="<th>".$this->Paginator->sort('order_code','Orden')."</th>";
			$remissionsTableHeader.="<th>".$this->Paginator->sort('ThirdParty.company_name',__('Client'))."</th>";
			$remissionsTableHeader.="<th class='centered'>".__('Precio Subtotal')."</th>";
      $remissionsTableHeader.="<th class='centered'>".__('Precio IVA')."</th>";
			$remissionsTableHeader.="<th class='centered'>".__('Precio + IVA')."</th>";
			$remissionsTableHeader.="<th class='centered'>".__('Precio Total')."</th>";  
		$remissionsTableHeader.="</tr>";
	$remissionsTableHeader.="</thead>";
	
	$remissionsTableBody="<tbody>";
	
	$totalPriceSubtotalCS=0;
  $totalPriceIvaCS=0;
	$totalPriceTotalCS=0;
	
  $totalPriceSubtotalUSD=0;
  $totalPriceIvaUSD=0;
	$totalPriceTotalUSD=0;
		
	foreach ($remissions as $sale){
    $boolError=($sale['Invoice']['total_price'] - $sale['Invoice']['sub_total_price'] - $sale['Invoice']['IVA_price'] > 0.005);
    //echo "boolError for sale number ".$sale['Order']['order_code']." is ".$boolError."<br/>";
    if (($displayOptionId==ORDERS_ERROR && $boolError) || $displayOptionId==ORDERS_ALL){
      $orderdate=new DateTime($sale['Order']['order_date']);
      $invoiceCode=$sale['Order']['order_code'];
      
      if ($sale['CashReceipt']['currency_id']===CURRENCY_CS){
        $totalPriceSubtotalCS+=$sale['CashReceipt']['sub_total_price'];
        $totalPriceIvaCS+=$sale['CashReceipt']['IVA_price'];
        $totalPriceTotalCS+=$sale['CashReceipt']['total_price'];
      }
      elseif ($sale['CashReceipt']['currency_id']===CURRENCY_CS){
        $totalPriceSubtotalUSD+=$sale['CashReceipt']['sub_total_price'];
        $totalPriceIvaUSD+=$sale['CashReceipt']['IVA_price'];
        $totalPriceTotalUSD+=$sale['CashReceipt']['total_price']; 
      }
      
      if ($sale['CashReceipt']['bool_annulled']==1){
        $invoiceCode.=" (Anulado)";
        $remissionsTableBody.="<tr class='italic'".($boolError?" style='color:#f00;'":"").">";		
      }
      else {
        $remissionsTableBody.="<tr".($boolError?" style='color:#f00;'":"").">";		
      }
      
			$remissionsTableBody.="<td>".$orderdate->format('d-m-Y')."</td>";
        $remissionsTableBody.="<td>".$invoiceCode."</td>";
        $remissionsTableBody.="<td>".$sale['ThirdParty']['company_name']."</td>";
        $remissionsTableBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>v".round($sale['Order']['price_products_unit_quantity'],4)."</span></td>";
        $remissionsTableBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".round($sale['Order']['price_products_total'],4)."</span></td>";
        $remissionsTableBody.="<td class='centered CScurrency'><span class='currency'></span><span class='amountright'>".round($sale['Order']['sub_total_price'],2)."</span></td>";
        
      $remissionsTableBody.="</tr>";
    }
	}
	*/
	
	echo "<h2>Descuadres de redondeo entre totales y suma subtotal + IVA</h2>";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";	
			echo "<div class='col-md-5'>";	
        echo "<div class='col-sm-9'>";	
        echo $this->Form->create('Report'); 
        echo "<fieldset>"; 
          echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
          echo $this->Form->input('Report.display_option_id',array('label'=>__('Visualizar Problemas/Todos'),'default'=>$displayOptionId));
        echo "</fieldset>"; 
        echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>"; 
        echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>"; 
        echo $this->Form->end(__('Refresh')); 
	
        echo $this->Html->link(__('Guardar como Excel'), ['action' => 'guardarResumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones'], ['class' => 'btn btn-primary']); 
      echo "</div>";
			echo "<div class='col-sm-3'>";	
				echo "<div class='actions fullwidth' style=''>";	
        if ($userrole != ROLE_SALES){
					echo "<h3>".__('Actions')."</h3>";
					echo "<ul>";
            if ($bool_sale_add_permission) {
              echo "<li>".$this->Html->link(__('New Sale'), array('action' => 'crearVenta'))."</li>";
              echo "<br/>";
            }
            if ($bool_remission_add_permission) {
              echo "<li>".$this->Html->link(__('New Remission'), array('action' => 'crearRemision'))."</li>";
              echo "<br/>";
            }
            if ($bool_client_index_permission){
              echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))."</li>";
            }
            if ($bool_client_add_permission) {
              echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))."</li>";
            }
          echo "</ul>";
        }
				echo "</div>";
			echo "</div>";
		echo "</div>";
	echo "</div>";			       
?>
</div>
<div class='related'>
<?php
	echo "<h3>Ventas</h3>";
	echo $salesTable;
	
	
	$_SESSION['resumenDescuadresRedondeoSubtotalesIvaTotalesVentasRemisiones'] = $salesTable;
?>
</div>