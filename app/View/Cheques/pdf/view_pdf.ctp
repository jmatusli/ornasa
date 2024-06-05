<style>
	table {
		width:100%;
	}
	
	div, span {
		font-size:1em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	.centered{
		text-align:center;
	}
	.right{
		text-align:right;
	}
	div.right{
		padding-right:1em;
	}
	
	span {
		margin-left:0.5em;
	}
	.bold{
		font-weight:bold;
	}
	.underline{
		text-decoration:underline;
	}
	.totalrow td{
		font-weight:bold;
		background-color:#BFE4FF;
	}
	
	.bordered tr th, 
	.bordered tr td
	{
		font-size:0.7em;
		border-width:1px;
		border-style:solid;
		border-color:#000000;
		vertical-align:top;
	}
	td span.right{
		font-size:1em;
		display:inline-block;
		width:65%;
		float:right;
		margin:0em;
	}
</style>
<?php
	$output="";
	$output.="<div class='cheques view'>";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	$output.="<div class='centered big bold'>CHEQUE # ".$cheque['Cheque']['cheque_code']."</div>";
	$chequeDate=new DateTime($cheque['Cheque']['cheque_date']);
	
	$output.="<table>";
		$output.="<tr>";
			$output.="<td style='width:70%'>";
			$output.="<div>A nombre de :<span class='underline'>".$cheque['Cheque']['receiver_name']."</span></div>";
			$output.="</td>";
			$output.="<td style='width:30%'>";
			$output.="<div>Fecha:<span class='underline'>".$chequeDate->format('d-m-Y')."</span></div>";
			$output.="</td>";
		$output.="</tr>";
		$output.="<tr>";
			$output.="<td style='width:100%'>";
			$output.="<div>Monto: C$<span class='underline'>".number_format($cheque['Cheque']['amount'],2,".",",")."</span></div>";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
	
	
	//$output.="<dl>";
	//		$output.="<dt>".__('Cheque Date')."</dt>";
	//		$output.="<dd>".$chequeDate->format('d-m-Y')."</dd>";
	//		$output.="<dt>".__('Cheque Code')."</dt>";
	//		$output.="<dd>".h($cheque['Cheque']['cheque_code'])."</dd>";
	//		$output.="<dt>".__('Receiver Name')."</dt>";
	//		$output.="<dd>".h($cheque['Cheque']['receiver_name'])."</dd>";
	//		$output.="<dt>".__('Amount')."</dt>";
	//		$output.="<dd>".$cheque['Currency']['abbreviation']." ".number_format($cheque['Cheque']['amount'],2,".",",")."</dd>";
	//		$output.="<dt>". __('Observation')."</dt>";
	//		$output.="<dd>". h($cheque['Cheque']['concept'])."</dd>";
	//		$output.="<dt>". __('Bank Accounting Code')."</dt>";
	//		$output.="<dd>". $this->Html->link($cheque['BankAccountingCode']['fullname'], array('controller' => 'accounting_codes', 'action' => 'view', $cheque['BankAccountingCode']['id']))."</dd>";
	//		$output.="<dt>". __('Accounting Register')."</dt>";
	//		$output.="<dd>". $this->Html->link($cheque['AccountingRegister']['register_code']."_".$cheque['AccountingRegister']['concept'], array('controller' => 'accounting_registers', 'action' => 'view', $cheque['AccountingRegister']['id']))."</dd>";
	//$output.="</dl>";
	
	if (!empty($accountingRegister['AccountingMovement'])){
		$output.="<h3>Comprobante</h3>";
		$accountingMovementTable= "<table cellpadding = '0' cellspacing = '0' id='comprobante_pago'>";
		$accountingMovementTable.= "<tr>";
			$accountingMovementTable.= "<th>".__('Accounting Code')."</th>";
			$accountingMovementTable.= "<th>".__('Description')."</th>";
			$accountingMovementTable.= "<th>".__('Concept')."</th>";
			$accountingMovementTable.= "<th class='centered'>".__('Debe')."</th>";
			$accountingMovementTable.= "<th class='centered'>".__('Haber')."</th>";
		$accountingMovementTable.= "</tr>";
		
		$totalDebit=0;
		$totalCredit=0;
		
		foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){
			//pr($accountingMovement);
			$accountingMovementTable.= "<tr>";
				$accountingMovementTable.= "<td>".$this->Html->Link($accountingMovement['AccountingCode']['code'],array('controller'=>'accounting_codes','action'=>'view',$accountingMovement['AccountingCode']['id']))."</td>";
				$accountingMovementTable.= "<td>".$accountingMovement['AccountingCode']['description']."</td>";
				$accountingMovementTable.= "<td>".$accountingMovement['concept']."</td>";
				
				if ($accountingMovement['bool_debit']){
					$accountingMovementTable.= "<td class='centered'>C$<span class='right'>".number_format($accountingMovement['amount'],2,".",",")."</span></td>";
					$accountingMovementTable.= "<td class='centered'>-</td>";
					$totalDebit+=$accountingMovement['amount'];
				}
				else {
					$accountingMovementTable.= "<td class='centered'>-</td>";
					$accountingMovementTable.= "<td class='centered'>C$<span class='right'>".number_format($accountingMovement['amount'],2,".",",")."</span></td>";
					$totalCredit+=$accountingMovement['amount'];
				}
			$accountingMovementTable.= "</tr>";
		} 
			$accountingMovementTable.= "<tr class='totalrow'>";
				$accountingMovementTable.= "<td>Total</td>";
				$accountingMovementTable.= "<td></td>";
				$accountingMovementTable.= "<td></td>";
				$accountingMovementTable.= "<td class='centered'>C$<span class='right'>".number_format($totalDebit,2,".",",")."</span></td>";
				$accountingMovementTable.= "<td class='centered'>C$<span class='right'>".number_format($totalCredit,2,".",",")."</span></td>";
			$accountingMovementTable.= "</tr>";
		$accountingMovementTable.= "</table>";
		$output.=$accountingMovementTable;
	}

	$output.="</div>"; 

	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>

	