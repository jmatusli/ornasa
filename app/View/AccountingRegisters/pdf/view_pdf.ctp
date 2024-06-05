<style>
	div, span {
		font-size:0.9em;
	}
	.small {
		font-size:0.9em;
	}
	.big{
		font-size:1.5em;
	}
	
	div.centered,
	td.centered,
	th.centered
	{
		text-align:center;
	}
	
	div.right{
		text-align:right;
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
	.bordered tr td,
	.bordered tr.totalrow td,
	{
		border-width:1px;
		border-style:solid;
		border-color:#000000;
	}
	
	.bordered tr td{
		border-width:0 1px;
	}
</style>
<?php
	$output="";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	//$output.="<div class="accountingRegisters viewPDF">";
	$output.="<div class='centered big'>".strtoupper(COMPANY_NAME)."</div>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<table style='width:90%;'>";
	$output.="<tr>";
	$output.="<td class='bold' style='width:70%;'>COMPROBANTE DE CONTABILIDAD</td>";
	$registerDate=new DateTime($accountingRegister['AccountingRegister']['register_date']);
	$output.="<td class='bold' style='width:30%;'>LEON ".$registerDate->format('d-m-Y')."</td>";
	$output.="</tr>";
	$output.="</table>";
	$output.="<div>".$accountingRegister['AccountingRegisterType']['description']."</div>";
	
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	if (!empty($accountingRegister['AccountingMovement'])){
		$output.="<table cellpadding = '0' cellspacing = '0' class='bordered'  style='width:'100%;'>";
		$output.= "<thead>";
		$output.= "<tr>";
			$output.= "<th style='width:20%;'>".__('CUENTA')."</th>";
			$output.= "<th class='centered' style='width:45%;'>".__('DESCRIPCION')."</th>";
			$output.= "<th class='centered' style='width:17.5%;'>".__('DEBE')."</th>";
			$output.= "<th class='centered' style='width:17.5%;'>".__('HABER')."</th>";
		$output.= "</tr>";
		$output.= "</thead>";
		
		$output.= "<tbody>";
		$totalDebit=0;
		$totalCredit=0;
		
		foreach ($accountingRegister['AccountingMovement'] as $accountingMovement){
			//pr($accountingMovement);
			$output.= "<tr>";
				$output.= "<td>".$this->Html->Link($accountingMovement['AccountingCode']['code'],array('controller'=>'accounting_codes','action'=>'view',$accountingMovement['AccountingCode']['id']))."</td>";
				$output.= "<td>".$accountingMovement['AccountingCode']['description']."</td>";
				
				if ($accountingMovement['bool_debit']){
					$output.= "<td class='centered'>".$accountingMovement['amount']."</td>";
					$output.= "<td class='centered'>-</td>";
					$totalDebit+=$accountingMovement['amount'];
				}
				else {
					$output.= "<td class='centered'>-</td>";
					$output.= "<td class='centered'>".$accountingMovement['amount']."</td>";
					$totalCredit+=$accountingMovement['amount'];
				}
			$output.= "</tr>";
		} 
		$output.= "<tr class='totalrow'>";
		$output.= "<td>Total</td>";
		$output.= "<td>".(($totalDebit==$totalCredit)?"SUMAS IGUALES":"SUMAS DIFERENTES")."</td>";
		$output.= "<td class='centered'><span>".($accountingMovement['currency_id']==CURRENCY_USD?"USD":"CS")." ".$totalDebit."</span></td>";
		$output.= "<td class='centered'><span>".($accountingMovement['currency_id']==CURRENCY_USD?"USD":"CS")." ".$totalCredit."</span></td>";
		$output.= "</tr>";
		if (!empty($accountingRegister['AccountingRegister']['concept'])){
			$output.= "<tr class='totalrow'>";
		
			$output.= "<td colspan='4' style='width:100%'>CONTABILIZACION DE ".$accountingRegister['AccountingRegister']['concept']."</td>";
			$output.= "</tr>";
		}
		if (!empty($accountingRegister['AccountingRegister']['observation'])){
			$output.= "<tr class='totalrow'>";
		
			$output.= "<td colspan='4' style='width:100%'>CONTABILIZACION DE ".$accountingRegister['AccountingRegister']['observation']."</td>";
			$output.= "</tr>";
		}
		$output.= "</tbody>";
		$output.= "</table>";
	}
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	$output.="<div><span class='bold '>&nbsp;</span></div>";
	
	$output.="<table style='width:90%'>";
		$output.="<tr>";
			$output.="<td style='width:45%'>";
				$output.="Elaborado por: ____________";
			$output.="</td>";
			$output.="<td style='width:10%'></td>";
			$output.="<td style='width:45%'>";
				$output.="Revisado por: _____________";
			$output.="</td>";
		$output.="</tr>";
	$output.="</table>";
	
	echo mb_convert_encoding($output, 'HTML-ENTITIES', 'UTF-8');
?>
