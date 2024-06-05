<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='orders view'>";
  }
  else {
    echo "<div class='orders view fullwidth'>";
  }

	//pr($cashReceipt);
	
	echo "<h2>".__('Remission')." ".$order['Order']['order_code']."</h2>";
	echo "<dl>";
		$currencyAbbreviation="C$";
		if (!empty($cashReceipt)){
			if ($cashReceipt['CashReceipt']['currency_id']==CURRENCY_USD){
				$currencyAbbreviation="US$";
			}
		}
		echo "<dt>".__('Remission Date')."</dt>";
		$orderDate=new DateTime($order['Order']['order_date']);
		echo "<dd>".$orderDate->format('d-m-Y')."</dd>";
		echo "<dt>".__('Código de Remisión')."</dt>";
		echo "<dd>".$order['Order']['order_code']."</dd>";
		echo "<dt>".__('Client')."</dt>";
		$clientName=$order['ThirdParty']['company_name'];
		echo "<dd>".($userrole != ROLE_SALES?$this->Html->link($clientName, ['controller' => 'third_parties', 'action' => 'verCliente', $order['ThirdParty']['id']]):$clientName)."</dd>";
    echo "<dt>".__('Comment')."</dt>";
    if (!empty($order['Order']['comment'])){
      echo "<dd>".html_entity_decode(str_replace(array("\r\n","\r","\n"),"<br/>",$order['Order']['comment']))."</dd>";
    }
    else {
      echo "<dd>-</dd>";
    } 
		if (!empty($cashReceipt)){
			echo "<dt>".__('Precio Total')."</dt>";
			echo "<dd>".$currencyAbbreviation." ".number_format($cashReceipt['CashReceipt']['amount'],2,".",",")."</dd>";
		}
	echo "</dl>";
	if (!empty($cashReceipt['CashboxAccountingCode']['description'])){
		echo "<div class='righttop'>";
			echo "<h4>Factura de Contado</h4>";
			echo "<div>Pagado a caja ".$cashReceipt['CashboxAccountingCode']['description']."</div>";
		echo "</div>";
	}
?>
	<br/>
	<button onclick="printContent('printinfo')">Imprime Orden de Salida</button>
	
	<div class="related">
<?php 
	if (!empty($summedMovements)){
	echo "<h3>".__('Productos en Remisión')."</h3>";
		echo "<table cellpadding = '0' cellspacing = '0'>";
			echo "<thead>";
				echo "<tr>";
					echo "<th>".__('Product')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Unit Price')."</th>";
					echo "<th class='centered'>".__('Quantity')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Total Price')."</th>";
					echo "<th class='centered' style='width:10%'>".__('Empaque')."</th>";
				echo "</tr>";
			echo "</thead>";
			
			$totalquantity=0;
			$totalprice=0;
			echo "<tbody>";
			foreach ($summedMovements as $summedMovement){ 
				echo "<tr>";
				if ($summedMovement['StockMovement']['production_result_code_id']>0){
					echo "<td>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$summedMovement['StockItem']['raw_material_name'].")</td>";
				}
				else {
					echo "<td>".$summedMovement['Product']['name']."</td>";
				}
				echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price'],2,".",",")."</span></td>";
				echo "<td class='centered'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
				echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</span></td>";
				
				$productpackingunit=$summedMovement['Product']['packaging_unit'];
				$productquantity=$summedMovement[0]['total_product_quantity'];
				$packunits=0;
				$remainder=0;
				$extraunits=0;
				$packingtext="";
				if ($productpackingunit>0){
					$packunits=floor($productquantity/$productpackingunit);
					$remainder=$productquantity-$productpackingunit*$packunits;
					$extraunits=$remainder%$productpackingunit;
					if ($packunits>0){
						$packingtext.=$packunits." emp + ".$extraunits." unds";
					}
					else {
						$packingtext.=$extraunits." unds";
					}
				}
				else {
					$packingtext.=$productquantity." unds";
				}
				
				
				
				
				echo "<td class='centered'>".$packingtext."</td>";
				
				$totalquantity+=$summedMovement[0]['total_product_quantity'];
				$totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
				echo "</tr>";
			}
		
				echo "<tr class='totalrow'>";
					echo "<td>Total</td>";
					echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".($totalquantity>0?number_format($totalprice/$totalquantity,2,".",","):"-")."</span></td>";
					echo "<td class='centered'>".number_format($totalquantity,0,".",",")."</td>";
					echo "<td class='centered'><span class='currency'>C$ </span><span class='amountright'>".number_format($totalprice,2,".",",")."</span></td>";
					echo "<td></td>";
				echo "</tr>";
			echo "</tbody>";

		echo "</table>";
	}
?>

</div>


<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='related'>";
    if (!empty($cashReceipt)){
      if (!empty($cashReceipt['AccountingRegisterCashReceipt'])){
        foreach ($cashReceipt['AccountingRegisterCashReceipt'] as $accountingRegisterCashReceipt){
          $accountingRegister=$accountingRegisterCashReceipt['AccountingRegister'];
          echo "<h3>Comprobante ".$accountingRegister['register_code']."</h3>";
          $accountingMovementTable= "<table cellpadding = '0' cellspacing = '0'>";
            $accountingMovementTable.="<thead>"; 
              $accountingMovementTable.= "<tr>";
                $accountingMovementTable.= "<th>".__('Accounting Code')."</th>";
                $accountingMovementTable.= "<th>".__('Description')."</th>";
                $accountingMovementTable.= "<th>".__('Concept')."</th>";
                $accountingMovementTable.= "<th class='centered'>".__('Debe')."</th>";
                $accountingMovementTable.= "<th class='centered'>".__('Haber')."</th>";
                //$accountingMovementTable.= "<th></th>";
              $accountingMovementTable.= "</tr>";
            $accountingMovementTable.="</thead>";
            $totalDebit=0;
            $totalCredit=0;
            $accountingMovementTable.="<tbody>";				
            foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){
              //pr($accountingMovement);
              $accountingMovementTable.= "<tr>";
                $accountingMovementTable.= "<td>".$this->Html->Link($accountingMovement['AccountingCode']['code'],array('controller'=>'accounting_codes','action'=>'view',$accountingMovement['AccountingCode']['id']))."</td>";
                $accountingMovementTable.= "<td>".$accountingMovement['AccountingCode']['description']."</td>";
                $accountingMovementTable.= "<td>".$accountingMovement['concept']."</td>";
                
                if ($accountingMovement['bool_debit']){
                  $accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
                  $accountingMovementTable.= "<td class='centered'>-</td>";
                  $totalDebit+=$accountingMovement['amount'];
                }
                else {
                  $accountingMovementTable.= "<td class='centered'>-</td>";
                  $accountingMovementTable.= "<td class='centered ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$accountingMovement['amount']."</span></td>";
                  $totalCredit+=$accountingMovement['amount'];
                }
                //$accountingMovementTable.= "<td>".($accountingMovement['bool_debit']?__('Debe'):__('Haber'))."</td>";
                //$accountingMovementTable.= "<td class='actions'>";
                  //$accountingMovementTable.= $this->Html->link(__('View'), array('controller' => 'accounting_movements', 'action' => 'view', $accountingMovement['id'])); 
                  //$accountingMovementTable.= $this->Html->link(__('Edit'), array('controller' => 'accounting_movements', 'action' => 'edit', $accountingMovement['id'])); 
                  //$accountingMovementTable.= $this->Form->postLink(__('Delete'), array('controller' => 'accounting_movements', 'action' => 'delete', $accountingMovement['AccountingMovement']['id']), array(), __('Are you sure you want to delete # %s?', $accountingMovement['id'])); 
                //$accountingMovementTable.= "</td>";
              $accountingMovementTable.= "</tr>";
            } 
            
              $accountingMovementTable.= "<tr class='totalrow'>";
                $accountingMovementTable.= "<td>Total</td>";
                $accountingMovementTable.= "<td></td>";
                $accountingMovementTable.= "<td></td>";
                if (!empty($accountingMovement)){
                  $accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalDebit."</span></td>";
                  $accountingMovementTable.= "<td class='centered  ".($accountingMovement['currency_id']==CURRENCY_USD?"USDcurrency":"CScurrency")."'><span>".$totalCredit."</span></td>";
                }
                else {
                  $accountingMovementTable.= "<td class='centered CScurrency'><span>0</span></td>";
                  $accountingMovementTable.= "<td class='centered CScurrency'><span>0</span></td>";
                }
              $accountingMovementTable.= "</tr>";
            $accountingMovementTable.= "</tbody>";
          $accountingMovementTable.= "</table>";
          echo $accountingMovementTable;				
        }
      }
    }
    echo "</div>";
  }  
?>	

<div id='printinfo'>
		<!-- buscar el  que ha sido asignado con el insert -->
		<?php 
			echo "<div id='companytitle'>ORNASA</div>";
			echo "<br/>";
			echo "<br/>";
			
			//visualize the date
			$orderdate=new DateTime($order['Order']['order_date']);
			echo "<div class='orderdate'>";
			echo "<table>";
			echo "<thead>";
			echo "<tr>";
			echo "<th>Día</th>";
			echo "<th>Mes</th>";
			echo "<th>Año</th>";
			echo "</tr>";
			echo "</thead>";
			echo "<tbody>";
			echo "<tr>";
			echo "<td>".date_format($orderdate,'d')."</td>";
			echo "<td>".date_format($orderdate,'m')."</td>";
			echo "<td>".date_format($orderdate,'Y')."</td>";
			echo "</tr>";
			echo "</tbody>";
			echo "</table>";
			echo "</div>";
			
			echo "<div class='ordertext'><span style='width:15%'>Factura Número:</span>".$order['Order']['cashreceipt_code']."</div>"; 
			echo "<br/>";
			echo "<div class='ordertext'><span style='width:15%'>Cliente:</span>".$order['ThirdParty']['company_name']."</div>";
			
			if (!empty($summedMovements)){
				echo "<table class='producttable'>";
				echo "<thead>";
				echo "<tr>";		
				echo "<th class='productname'>Producto</th>";
				echo "<th class='unitprice'>Precio Unidad</th>";
				echo "<th class='quantity'>Cantidad</th>";
				echo "<th class='totalprice'>Precio</th>";
				echo "</tr>";
				echo "</thead>";
				echo "<tbody>";				
		
				$totalquantity=0;
				$totalprice=0;
				foreach ($summedMovements as $summedMovement){ 
					echo "<tr>";
					if ($summedMovement['StockMovement']['production_result_code_id']>0){
						echo "<td class='productname'>".$summedMovement['Product']['name']." ".$summedMovement['ProductionResultCode']['code']." (".$summedMovement['StockItem']['raw_material_name'].")</td>";
					}
					else {
						echo "<td class='productname'>".$summedMovement['Product']['name']."</td>";
					}
					
					echo "<td class='unitprice'><span class='currency'>C$ </span>".number_format($summedMovement['StockMovement']['product_unit_price'],2,".",",")."</td>";
					echo "<td class='quantity'>".number_format($summedMovement[0]['total_product_quantity'],0,".",",")."</td>";
					echo "<td class='totalprice'><span class='currency'>C$ </span>".number_format($summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'],2,".",",")."</td>";
					$totalquantity+=$summedMovement[0]['total_product_quantity'];
					$totalprice+=$summedMovement['StockMovement']['product_unit_price']*$summedMovement[0]['total_product_quantity'];
					echo "</tr>";
				}
				echo "<tr><td class='totaltext bottomrow'>SUB TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($order['Order']['total_price'],2,".",",")."</td></tr>";
				echo "<tr><td class='totaltext bottomrow'>IVA</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($cashreceipt['CashReceipt']['IVA_price'],2,".",",")."</td></tr>";
				echo "<tr><td class='totaltext bottomrow'>TOTAL</td><td class='bottomrow'></td><td class='bottomrow'></td><td>C$ ".number_format($cashreceipt['CashReceipt']['total_price'],2,".",",")."</td></tr>";
				echo "</tbody>";
				
				echo "</table>";
			}			
			echo "&nbsp;<br/>";
			echo "&nbsp;<br/>";
			echo "&nbsp;<br/>";
			echo "&nbsp;<br/>";	
		?>
	</div>
</div>

<?php
  if ($userrole != ROLE_SALES){
    echo "<div class='actions'>";
      echo "<h3>".__('Actions')."</h3>";
      echo "<ul>";
        $orderCode=str_replace(' ','',$order['Order']['order_code']);
        $orderCode=str_replace('/','',$orderCode);
        $filename='Remisión_'.$orderCode;
        echo "<li>".$this->Html->link(__('Guardar como pdf'), array('action' => 'verPdfRemision','ext'=>'pdf',$order['Order']['id'],$filename))."</li>";
        echo "<br/>";
        
        if ($bool_remission_edit_permission) { 
          echo "<li>".$this->Html->link(__('Edit Remission'), array('action' => 'editarRemision', $order['Order']['id']))." </li>";
          echo "<br/>";
        }
        if ($bool_delete_permission){
          echo "<li>".$this->Form->postLink(__('Eliminar Remisión'), array('action' => 'eliminarRemision', $order['Order']['id']), array(), __('Está seguro que quiere eliminar la remisión # %s?', $order['Order']['order_code']))." </li>";
          echo "<br/>";
        }
        echo "<li>".$this->Html->link(__('Todas Ventas y Remisiones'), array('action' => 'resumenVentasRemisiones'))." </li>";
        if ($bool_remission_add_permission) { 
          echo "<li>".$this->Html->link(__('New Remission'), array('action' => 'crearRemision'))." </li>";
        }
        echo "<br/>";
        if ($bool_client_index_permission) { 
          echo "<li>".$this->Html->link(__('List Clients'), array('controller' => 'third_parties', 'action' => 'resumenClientes'))." </li>";
        }
        if ($bool_client_add_permission) { 
          echo "<li>".$this->Html->link(__('New Client'), array('controller' => 'third_parties', 'action' => 'crearCliente'))." </li>";
        }
      echo "</ul>";
    echo "</div>";
  }
?>




<script type="text/javascript">
	<!--
		function printContent(id){
			str=document.getElementById(id).innerHTML
			newwin=window.open('','printwin','left=5,top=5,width=640,height=480')
			newwin.document.write('<HTML>\n<HEAD>\n')
			
			newwin.document.write('<style type="text/css">\n')
			newwin.document.write('#all {font-size:12px;}\n')
			newwin.document.write('#companytitle {font-size:20px;font-weight:bold;}\n')
			newwin.document.write('.ordertext {font-size:14px;font-weight:bold;}\n')
			newwin.document.write('.orderdate {width:20%;float:right;clear:right;}\n')
			newwin.document.write('.producttable {width:100%;}\n')
			newwin.document.write('.productname {width:45%;}\n')
			newwin.document.write('.unitprice {width:15%;}\n')
			newwin.document.write('.quantity {width:20%;}\n')
			newwin.document.write('.totalprice {width:20%;}\n')
			newwin.document.write('td {border:1px solid black;}\n')
			newwin.document.write('td.bottomrow {border:0px;}\n')
			newwin.document.write('td.totaltext {font-weight:bold;}\n')
			newwin.document.write('</style>\n')
			
			newwin.document.write('<script>\n')
			newwin.document.write('function chkstate(){\n')
			newwin.document.write('if(document.readyState=="complete"){\n')
			newwin.document.write('window.close()\n')
			newwin.document.write('}\n')
			newwin.document.write('else{\n')
			newwin.document.write('setTimeout("chkstate()",2000)\n')
			newwin.document.write('}\n')
			newwin.document.write('}\n')
			newwin.document.write('function print_win(){\n')
			newwin.document.write('window.print();\n')
			newwin.document.write('chkstate();\n')
			newwin.document.write('}\n')
			newwin.document.write('<\/script>\n')
			newwin.document.write('</HEAD>\n')
			newwin.document.write('<BODY style="margin:2px;max-height:300px;" onload="print_win()">\n')
			newwin.document.write('<div id="all" style="font-size:11px;">\n')
			newwin.document.write(str)
			newwin.document.write('</div>\n')
			newwin.document.write('</BODY>\n')
			newwin.document.write('</HTML>\n')
			newwin.document.close()
		}
	//-->
</script>
