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
	
	function formatPercentages(){
		$("td.percentage span.amountright").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			else {
				var percentageValue=parseFloat($(this).text());
				$(this).text(100*percentageValue);
			}
			$(this).number(true,2,'.',',');
			$(this).append(" %");
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
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
		formatPercentages();
	});
</script>
<div class="orders index purchases">
<?php 
	echo "<h2>Entradas de Suministros</h2>";
	
	echo "<div class='container-fluid'>";
		echo "<div class='row'>";
			echo "<div class='col-md-6'>";			
				echo $this->Form->create('Report');
				echo "<fieldset>";
					echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>2014,'maxYear'=>date('Y')]);
					echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>2014,'maxYear'=>date('Y')]);
          
          echo $this->WarehouseFilter->displayWarehouseFilter($warehouses, $userRoleId,$warehouseId);
          
          echo $this->Form->input('Report.provider_id',['label'=>'Proveedor','default'=>$providerId,'empty'=>[0=>'-- Todos Proveedores --']]);
				echo "</fieldset>";
				echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
				echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
				echo $this->Form->end(__('Refresh'));
			echo "</div>";
			echo "<div class='col-md-4'>";			
				foreach ($consumibleProductTypes as $productType){

						echo "<h3>Totales para Tipo de Producto ".$productType['ProductType']['name']."</h3>";
						echo "<table cellpadding='0' cellspacing='0'>";
							echo "<thead>";
								echo "<tr>";
									echo "<th>Producto</th>";
									echo "<th># de Empaques</th>";
									echo "<th>Cantidad Total</th>";
									echo "<th>Costo Total</th>";
								echo "</tr>";
							echo "</thead>";
							$totalPackages=0; 
							$totalQuantity=0;
							$totalCost=0;
							echo "<tbody>";							
							foreach ($productType['Product'] as $product){
								if (!empty($product['total_quantity_product'])){
									$totalPackages+=$product['total_packages']; 
									$totalQuantity+=$product['total_quantity_product'];
									$totalCost+=$product['total_cost_product'];
									echo "<tr>";
										echo "<td>".$product['name']."</td>";
										echo "<td class='number'><span class='amountright'>".$product['total_packages']."</span></td>";
										echo "<td class='number'><span class='amountright'>".$product['total_quantity_product']."</span></td>";
										echo "<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$product['total_cost_product']."</span></td>";
									echo "</tr>";
								}
							}
								$totalRow="<tr class='totalrow'>";
									$totalRow.="<td>Total</td>";
									$totalRow.="<td class='number'><span class='amountright'>".$totalPackages."</span></td>";
									$totalRow.="<td class='number'><span class='amountright'>".$totalQuantity."</span></td>";
									$totalRow.="<td class='CScurrency'><span class='currency'>C$ </span><span class='amountright'>".$totalCost."</span></td>";
								$totalRow.="</tr>";
								echo $totalRow;
							echo "</tbody>";
						echo "</table>";
				}
			echo "</div>";
		echo "</div>";
	echo "</div>";
?>
</div>
<div class='actions'>
<?php 
	echo "<h3>".__('Actions')."</h3>";
	echo "<ul>";
		if ($bool_add_permission) { 
			echo "<li>".$this->Html->link(__('Nueva Entrada Suministros'), array('action' => 'crearEntradaSuministros'))."</li>";
			echo "<br/>";
		}
		if ($bool_provider_index_permission) { 		
			echo "<li>".$this->Html->link(__('List Providers'), array('controller' => 'third_parties', 'action' => 'resumenProveedores'))." </li>";
		}
		if ($bool_provider_add_permission) { 
			echo "<li>".$this->Html->link(__('New Provider'), array('controller' => 'third_parties', 'action' => 'crearProveedor'))." </li>";
		} 
	echo "</ul>";
?>
</div>
<div class='related'>";
<?php
  echo '<p class="info"><span class="pendingentry">Entradas que están por pagar</span> salen con un <span class="pendingentry">fondo amarillo</span></p>';

	echo "<table cellpadding='0' cellspacing='0'>";
		echo "<thead>";
			echo "<tr>";
				echo "<th>".$this->Paginator->sort('order_date',__('Purchase Date'))."</th>";
				echo "<th>".$this->Paginator->sort('order_code','# Entrada')."</th>";
        echo "<th># Orden de compra</th>";
				echo "<th>".$this->Paginator->sort('third_party_id',__('Proveedor'))."</th>";
        echo "<th>Productos</th>";
				echo "<th>".$this->Paginator->sort('Cantidad')."</th>";
				echo "<th class='centered'>".$this->Paginator->sort('total_price',__('Total Cost'))."</th>";
				echo "<th class='actions'>".__('Actions')."</th>";
			echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
			$totalprice=0; 
			$totalProductCount=0;
			
			$purchaseRows="";
			foreach ($purchases as $purchase){ 
				//pr($purchase);
				//$totalprice+=$purchase['Order']['total_price']; 
        $totalprice+=$purchase['Order']['entry_cost_total']; 
				$orderdate=new DateTime($purchase['Order']['order_date']);
				
				$productCount=0;
				foreach ($purchase['StockMovement'] as $stockMovement){
          $productCount+=$stockMovement['product_quantity'];
          $totalProductCount+=$stockMovement['product_quantity'];
				}
				
				$purchaseRows.="<tr>";
				
				$purchaseRows.="<td>".$orderdate->format('d-m-Y')."</td>";
				$purchaseRows.='<td'.($purchase['Order']['bool_entry_paid']?'':' class="pendingentry"').'>'.$this->Html->link($purchase['Order']['order_code'], ['action' => 'verEntradaSuministros', $purchase['Order']['id']]).'</td>';
				$purchaseRows.='<td>'.$this->Html->link($purchase['PurchaseOrder']['purchase_order_code'], ['controller'=>'purchaseOrders','action' => 'detalle', $purchase['PurchaseOrder']['id']]).'</td>';
          
        $purchaseRows.="<td>".$this->Html->link($purchase['ThirdParty']['company_name'], ['controller' => 'third_parties', 'action' => 'verProveedor', $purchase['ThirdParty']['id']])."</td>";
        $purchaseRows.="<td>";
        foreach($purchase['StockMovement'] as $stockMovement){
          $purchaseRows.=number_format($stockMovement['product_quantity'],0,'',',')." ".$stockMovement['Product']['name']."<br/>";
        }
        $purchaseRows.="</td>";
        $purchaseRows.="<td class='centered'>".($productCount>0?number_format($productCount,0,".",","):"-")."</td>";
				//$purchaseRows.='<td class="centered'.($purchase['Order']['bool_entry_paid']?'':' pendingentry').'"><span class="currency">C$ </span>'.number_format($purchase['Order']['total_price'],4,".",",").'</td>';
        $purchaseRows.='<td class="centered'.($purchase['Order']['bool_entry_paid']?'':' pendingentry').'"><span class="currency">C$ </span>'.number_format($purchase['Order']['entry_cost_total'],4,".",",").'</td>';
				$purchaseRows.="<td class='actions'>";
					$companyName=str_replace(".","",$purchase['ThirdParty']['company_name']);
					$companyName=str_replace(" ","",$companyName);
					$namepdf="Compra_".$companyName."_".$purchase['Order']['order_code'];
					if ($bool_edit_permission) { 
						$purchaseRows.=$this->Html->link(__('Edit'), ['action' => 'editarEntradaSuministros', $purchase['Order']['id']]); 
					} 
					if ($bool_delete_permission) { 
						// $purchaseRows.=$this->Form->postLink(__('Delete'), array('action' => 'delete', $purchase['Order']['id']), array(), __('Are you sure you want to delete # %s?', $purchase['Order']['id'])); 
					}
					$purchaseRows.=$this->Html->link(__('Guardar como pdf'), ['action' => 'verPdfEntradaSuministros','ext'=>'pdf', $purchase['Order']['id'],$namepdf]);
				$purchaseRows.="</td>";
				
				$purchaseRows.="</tr>";
			} 
			$totalRow="";
			$totalRow.="<tr class='totalrow'>";
				$totalRow.="<td>Total</td>";
				$totalRow.="<td></td>";
        $totalRow.="<td></td>";
				$totalRow.="<td></td>";
        $totalRow.="<td></td>";
				$totalRow.="<td class='centered'>".number_format($totalProductCount,0,".",",")."</td>";
				$totalRow.="<td class='centered'><span class='currency'>C$ </span>".number_format($totalprice,4,".",",")."</td>";
				$totalRow.="<td></td>";
			$totalRow.="</tr>";
			echo $totalRow.$purchaseRows.$totalRow;
		echo "</tbody>";
	echo "</table>";
	
?>	
</div>

