<div class="accountingRegisters index">
	<h2><?php echo __('Accounting Registers'); ?></h2>
	
<?php	
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarResumenAsientosContablesProblemas'), array( 'class' => 'btn btn-primary')); 
	
	$accountingRegistersTableHeader="";
	$accountingRegistersTableHeader.="<thead>";
		$accountingRegistersTableHeader.="<tr>";
			$accountingRegistersTableHeader.="<th>ID</th>";
			$accountingRegistersTableHeader.="<th>Date</th>";
			$accountingRegistersTableHeader.="<th>Number</th>";
			$accountingRegistersTableHeader.="<th>Concept</th>";
			$accountingRegistersTableHeader.="<th>Register Amount</th>";
			$accountingRegistersTableHeader.="<th>Debit Amount</th>";
			$accountingRegistersTableHeader.="<th>Credit Amount</th>";
		$accountingRegistersTableHeader.="</tr>";
	$accountingRegistersTableHeader.="</thead>";
	
	$accountingRegistersTableHeaderExcel="";
	$accountingRegistersTableHeaderExcel.="<thead>";
		$accountingRegistersTableHeaderExcel.="<tr><th colspan='6' align='center'>".COMPANY_NAME."</th></tr>";
		$accountingRegistersTableHeaderExcel.="<tr><th colspan='6' align='center'>".__('Reporte Asientos Contables con Problemas')." (".date('d-m-Y').")</th></tr>";
		$accountingRegistersTableHeaderExcel.="<tr><th colspan='6' align='center'>Asientos Contables con Problemas</th></tr>";
		$accountingRegistersTableHeaderExcel.="<tr>";
			$accountingRegistersTableHeaderExcel.="<th>ID</th>";
			$accountingRegistersTableHeaderExcel.="<th>Date</th>";
			$accountingRegistersTableHeaderExcel.="<th>Number</th>";
			$accountingRegistersTableHeaderExcel.="<th>Concept</th>";
			$accountingRegistersTableHeaderExcel.="<th>Register Amount</th>";
			$accountingRegistersTableHeaderExcel.="<th>Debit Amount</th>";
			$accountingRegistersTableHeaderExcel.="<th>Credit Amount</th>";
		$accountingRegistersTableHeaderExcel.="</tr>";
	$accountingRegistersTableHeaderExcel.="</thead>";
	
	
	$accountingRegistersTableBodyExcel=$accountingRegistersTableBody="<tbody>";
	
	foreach ($accountingRegisters as $accountingRegister){
		$debitAmount=0;
		$creditAmount=0;
		foreach ($accountingRegister['AccountingMovement'] as $movement){
			if ($movement['bool_debit']){
				$debitAmount+=$movement['amount'];
			}
			else {
				$creditAmount+=$movement['amount'];
			}
		}
		$registerAmount=$accountingRegister['AccountingRegister']['amount'];
		$bool_register_OK=true;
		if ($registerAmount!=$debitAmount){
			$bool_register_OK=false;
		}
		if ($registerAmount!=$creditAmount){
			$bool_register_OK=false;
		}
		if (!$bool_register_OK){
			if (abs($registerAmount-$debitAmount)>0.01||abs($registerAmount-$creditAmount)>0.01){
				$accountingRegistersTableBody.="<tr>";
					$registerDate=new DateTime($accountingRegister['AccountingRegister']['register_date']);
					$accountingRegisterBodyRow="";
					$accountingRegisterBodyRow.="<td>".$this->Html->Link($accountingRegister['AccountingRegister']['id'],array('action'=>'view',$accountingRegister['AccountingRegister']['id']))."</td>";
					$accountingRegisterBodyRow.="<td>".$registerDate->format('d-m-Y')."</td>";
					//$accountingRegisterBodyRow.="<td>".$accountingRegister['AccountingRegister']['register_code'].($accountingRegister['AccountingRegister']['bool_annulled']?" (Anulado)":"")."</td>";
					$accountingRegisterBodyRow.="<td>".$accountingRegister['AccountingRegister']['register_code']."</td>";
					$accountingRegisterBodyRow.="<td>".$accountingRegister['AccountingRegister']['concept']."</td>";
					$accountingRegisterBodyRow.="<td class='CScurrency'><span class='amountright'>".$accountingRegister['AccountingRegister']['amount']."</td>";
					$accountingRegisterBodyRow.="<td class='CScurrency'><span class='amountright'>".$debitAmount."</td>";
					$accountingRegisterBodyRow.="<td class='CScurrency'><span class='amountright'>".$creditAmount."</td>";
					
					$accountingRegistersTableBodyExcel.="<tr>".$accountingRegisterBodyRow."</tr>";
					$accountingRegistersTableBody.=$accountingRegisterBodyRow;
					
				$accountingRegistersTableBody.="</tr>";
			}
		}
	}
	$accountingRegistersTableBodyExcel.="</tbody>";
	$accountingRegistersTableBody.="</tbody>";
	
	$reportOutput="<table cellpadding='0' cellspacing='0'>".$accountingRegistersTableHeader.$accountingRegistersTableBody."</table>";
	echo $reportOutput;
	$reportExcel="<table cellpadding='0' cellspacing='0' id='asientos_contables_problemas'>".$accountingRegistersTableHeaderExcel.$accountingRegistersTableBodyExcel."</table>";
	$_SESSION['resumenAsientosContablesProblemas'] = $reportExcel;
	
?>	
	<ul class='nav pull-right contextmenu'>
		<li class='dropdown user'>
			<button class='btn btn-primary dropdown-toggle' data-toggle='dropdown'>
				Pantallas Relacionadas
				<i class='icon-angle-down'></i>
			</button>
			<ul class='dropdown-menu'>
				<li><?php echo $this->Html->link(__('New Accounting Register'), array('action' => 'add')); ?></li>
				<li class='divider'></li>
				<!--li><?php echo $this->Html->link(__('List Accounting Movements'), array('controller' => 'accounting_movements', 'action' => 'index')); ?> </li-->
				<!--li><?php echo $this->Html->link(__('New Accounting Movement'), array('controller' => 'accounting_movements', 'action' => 'add')); ?> </li-->
			</ul>
		</li>
	</ul>
</div>
