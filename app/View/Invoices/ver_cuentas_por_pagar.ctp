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
<div class="invoices cuentas_por_pagar fullwidth">
<?php 
  echo "<h2>Reporte de Cuentas por Pagar ".($clientId>0?' para Cliente '.$client['ThirdParty']['company_name']:'')."</h2>";
	
  $reportTable="";
  echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-md-6'>";			
        if ($roleId==ROLE_ADMIN){
          echo $this->Form->create('Report');
          echo "<fieldset>";
            //echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')));
            //echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')));
            echo $this->Form->input('Report.client_id',['default'=>$clientId,'empty'=>[0=>"--Seleccione cliente--"]]);
          echo "</fieldset>";
          //echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
          //echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
          echo $this->Form->end(__('Refresh'));
        }
        if (!empty($pendingInvoices)){
          echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarCuentasPorPagar'), array( 'class' => 'btn btn-primary')); 
        }
			echo "</div>";
  echo "</div>";
  if ($clientId>0){
    echo "<div class='col-md-12'>";			
    if (!empty($pendingInvoices)){
      $table_id=substr("cuentas_por_pagar_".$client['ThirdParty']['company_name'],0,31);
      $reportTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
        $reportTable.="<thead>";
          $reportTable.="<tr>";
            if ($roleId==ROLE_ADMIN){
              $reportTable.="<th class='actions'></th>";
            }
            $reportTable.="<th>Fecha de Emisión</th>";
            //$reportTable.="<th>Cliente</th>";
            $reportTable.="<th>Factura/Orden</th>";
            $reportTable.="<th class='centered'>Precio Total</th>";
            $reportTable.="<th class='centered'>Abonado</th>";
            $reportTable.="<th class='centered'>Saldo Pendiente</th>";
            //$reportTable.="<th class='centered'>Diferencia Cambiaria</th>";
            //$reportTable.="<th class='centered'>A pagar</th>";
            $reportTable.="<th>Fecha de Vencimiento</th>";
            $reportTable.="<th>Días de Crédito</th>";
            //$reportTable.="<th class='centered'>1-30</th>";
            //$reportTable.="<th class='centered'>31-60</th>";
            //$reportTable.="<th class='centered'>>60</th>";
          $reportTable.="</tr>";
        $reportTable.="</thead>";
        $reportTable.="<tbody>";
        $cuentasPorPagarBody="";
        $totalCSInvoice=0;
        $totalCSPaid=0;
        $totalCSPending=0;
        $totalCSUnder30=0;
        $totalCSUnder60=0;
        $totalCSOver60=0;
        foreach ($pendingInvoices as $invoice){
          $invoiceDate=new DateTime($invoice['Invoice']['invoice_date']);
          $dueDate=new DateTime($invoice['Invoice']['due_date']);
          $currentDate= new DateTime(date('Y-m-d'));
          $daysLate=$currentDate->diff($invoiceDate);
          //pr($daysLate);
          $currencyClass="CScurrency";
          if ($invoice['Currency']['id']==CURRENCY_USD){
            $currencyClass="USDcurrency";
          }
          $cuentasPorPagarBody.="<tr>";
            if ($roleId==ROLE_ADMIN){
              $cuentasPorPagarBody.="<td class='actions'>".$this->Html->link('Cancelar Factura', array('controller'=>'cash_receipts','action' => 'add', CASH_RECEIPT_TYPE_CREDIT))."</td>";
            }
            $cuentasPorPagarBody.="<td>".$invoiceDate->format('d-m-Y')."</td>";
            //$cuentasPorPagarBody.="<td>".$this->Html->link($invoice['Client']['company_name'], array('controller' => 'third_parties', 'action' => 'verCliente', CASH_RECEIPT_TYPE_CREDIT))."</td>";
            $cuentasPorPagarBody.="<td>".$this->Html->link($invoice['Invoice']['invoice_code'], array('controller' => 'orders', 'action' => 'verVenta', $invoice['Order']['id']))."</td>";
            $cuentasPorPagarBody.="<td class='centered ".$currencyClass."'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['total_price']."</span></td>";
            $cuentasPorPagarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['paidCS']."</span></td>";
            $cuentasPorPagarBody.="<td class='CScurrency'><span class='currency'></span><span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
            if ($invoice['Currency']['id']==CURRENCY_CS){
                $totalCSInvoice+=$invoice['Invoice']['total_price'];
            }
            elseif ($invoice['Currency']['id']==CURRENCY_USD) {
              $totalCSInvoice+=$invoice['Invoice']['total_price']*$exchangeRateCurrent;
            }
            $totalCSPaid+=$invoice['Invoice']['paidCS'];
            $totalCSPending+=$invoice['Invoice']['pendingCS'];
            //$cuentasPorPagarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'></span></td>";
            //$cuentasPorPagarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'></span></td>";
            $cuentasPorPagarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
            /*
            if ($daysLate->format('%d')<31){
              $cuentasPorPagarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
              $cuentasPorPagarBody.="<td class='centered number'>-</td>";
              $cuentasPorPagarBody.="<td class='centered number'>-</td>";
              $totalCSUnder30+=$invoice['Invoice']['pendingCS'];
            }
            else if ($daysLate->format('%d')<61){
              $cuentasPorPagarBody.="<td class='centered number'>-</td>";
              $cuentasPorPagarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
              $cuentasPorPagarBody.="<td class='centered number'>-</td>";
              $totalCSUnder60+=$invoice['Invoice']['pendingCS'];
            }
            else{
              $cuentasPorPagarBody.="<td class='centered number'>-</td>";
              $cuentasPorPagarBody.="<td class='centered number'>-</td>";
              $cuentasPorPagarBody.="<td class='centered number'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
              $totalCSOver60+=$invoice['Invoice']['pendingCS'];
            }
            */
            $cuentasPorPagarBody.="<td>".$daysLate->format('%a')."</td>";
          $cuentasPorPagarBody.="</tr>";
        }	
          $totalRow="";
          $totalRow.="<tr class='totalrow'>";
            $totalRow.="<td>Total</td>";	
            if ($roleId==ROLE_ADMIN){
              $totalRow.="<td></td>";	
            }
            $totalRow.="<td></td>";	
            $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSInvoice."</span></td>";
            $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPaid."</span></td>";
            $totalRow.="<td class='centered number CScurrency'><span class='currency'></span><span class='amountright'>".$totalCSPending."</span></td>";
            $totalRow.="<td></td>";	
            $totalRow.="<td></td>";	
            //$totalRow.="<td class='centered number CScurrency'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$totalCSUnder30."</span></td>";
            //$totalRow.="<td class='centered number CScurrency'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$totalCSUnder60."</span></td>";
            //$totalRow.="<td class='centered number CScurrency'>".$invoice['Currency']['abbreviation']."<span class='amountright'>".$totalCSOver60."</span></td>";
          $totalRow.="</tr>";
          $reportTable.=$totalRow.$cuentasPorPagarBody.$totalRow;
        $reportTable.="</tbody>";
      $reportTable.="</table>";
      echo $reportTable;
      
      
      $excelTable="";
      $table_id=substr("facturas_por_cobrar_".$client['ThirdParty']['company_name'],0,31);
      $excelTable.= "<table cellpadding='0' cellspacing='0' id='".$table_id."'>";
        $excelTable.="<thead>";
          $excelTable.="<tr>";
            $excelTable.="<th>Fecha de Emisión</th>";
            $excelTable.="<th>Factura/Orden</th>";
            $excelTable.="<th></th>";
            $excelTable.="<th class='centered'>Precio Total</th>";
            $excelTable.="<th></th>";
            $excelTable.="<th class='centered'>Abonado</th>";
            $excelTable.="<th></th>";
            $excelTable.="<th class='centered'>Saldo Pendiente</th>";
            $excelTable.="<th>Fecha de Vencimiento</th>";
            $excelTable.="<th>Días de Crédito</th>";
          $excelTable.="</tr>";
        $excelTable.="</thead>";
        $excelTable.="<tbody>";
        
        $cuentasPorPagarBody="";
        $totalCSInvoice=0;
        $totalCSPaid=0;
        $totalCSPending=0;
        $totalCSUnder30=0;
        $totalCSUnder60=0;
        $totalCSOver60=0;
        foreach ($pendingInvoices as $invoice){
          $invoiceDate=new DateTime($invoice['Invoice']['invoice_date']);
          $dueDate=new DateTime($invoice['Invoice']['due_date']);
          $currentDate= new DateTime(date('Y-m-d'));
          $daysLate=$currentDate->diff($invoiceDate);
          //pr($daysLate);
          $cuentasPorPagarBody.="<tr>";
            $cuentasPorPagarBody.="<td>".$invoiceDate->format('d-m-Y')."</td>";
            $cuentasPorPagarBody.="<td>".$this->Html->link($invoice['Invoice']['invoice_code'], array('controller' => 'orders', 'action' => 'viewSale', $invoice['Order']['id']))."</td>";
            $cuentasPorPagarBody.="<td>".$invoice['Currency']['abbreviation']."</td>";
            $cuentasPorPagarBody.="<td class='centered'><span class='amountright'>".$invoice['Invoice']['total_price']."</span></td>";
            $cuentasPorPagarBody.="<td>C$</td>";
            $cuentasPorPagarBody.="<td class='centered'><span class='amountright'>".$invoice['Invoice']['paidCS']."</span></td>";
            $cuentasPorPagarBody.="<td>C$</td>";
            $cuentasPorPagarBody.="<td class='centered'><span class='amountright'>".$invoice['Invoice']['pendingCS']."</span></td>";
            if ($invoice['Currency']['id']==CURRENCY_CS){
                $totalCSInvoice+=$invoice['Invoice']['total_price'];
            }
            elseif ($invoice['Currency']['id']==CURRENCY_USD) {
              $totalCSInvoice+=$invoice['Invoice']['total_price']*$exchangeRateCurrent;
            }
            $totalCSPaid+=$invoice['Invoice']['paidCS'];
            $totalCSPending+=$invoice['Invoice']['pendingCS'];
            
            $cuentasPorPagarBody.="<td>".$dueDate->format('d-m-Y')."</td>";
            $cuentasPorPagarBody.="<td>".$daysLate->days."</td>";
          $cuentasPorPagarBody.="</tr>";
        }	
          $totalRow="";
          $totalRow.="<tr class='totalrow'>";
            $totalRow.="<td>Total</td>";	
            $totalRow.="<td></td>";	
            $totalRow.="<td>C$</td>";	
            $totalRow.="<td><span class='amountright'>".$totalCSInvoice."</span></td>";
            $totalRow.="<td>C$</td>";	
            $totalRow.="<td><span class='amountright'>".$totalCSPaid."</span></td>";
            $totalRow.="<td>C$</td>";	
            $totalRow.="<td><span class='amountright'>".$totalCSPending."</span></td>";
            $totalRow.="<td class='centered'></td>";	
            $totalRow.="<td class='centered'></td>";	
          $totalRow.="</tr>";
          $excelTable.=$totalRow.$cuentasPorPagarBody.$totalRow;
        $excelTable.="</tbody>";
      $excelTable.="</table>";
      
      $_SESSION['cuentasPorPagar'] = $excelTable;
    }
    else {
      echo "<h3>No hay cuentas por pagar pendientes</h3>";
    }     
    echo "</div>";
    
    echo "</div>";
    
  }
?>

</div>
