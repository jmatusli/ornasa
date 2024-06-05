<script>
	function formatNumbers(){
		$("td.number span").each(function(){
			$(this).number(true,0);
		});
	}
	
	function formatCurrencies(){
		$("td.currency span").each(function(){
			$(this).number(true,2);
			$(this).parent().prepend("C$ ");
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
		formatCurrencies();
		formatPercentages();
	});
	
	$(document).on('change','#ReportReportType',function(event){
		$('#ReportVerReporteCierreForm').submit();
	});

</script>

<div class="stockItems view report">
<?php 
	echo "<h2>".__('Reporte de Cierre')."</h2>";
	echo $this->Form->create('Report'); 
	echo "<fieldset>";
		echo $this->Form->input('Report.startdate',['type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
		echo $this->Form->input('Report.enddate',['type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate,'minYear'=>($userrole != ROLE_SALES?2014:date('Y')-1),'maxYear'=>date('Y')]);
		$reportTypes=[];
		if ($userrole != ROLE_SALES){
      $reportTypes[]="Reporte de Cierre en Dinero";
    }
		$reportTypes[]="Reporte de Cierre en Cantidad de Envases";
    if ($userrole != ROLE_SALES){  
      echo $this->Form->input('Report.report_type',array('options'=>'date','label'=>__('Tipo de Reporte'),'default'=>$bool_bottles,'options'=>$reportTypes));
    }
	echo "</fieldset>";
  if ($userrole != ROLE_SALES){
    echo "<button id='previousmonth' class='monthswitcher'>".__('Previous Month')."</button>";
    echo "<button id='nextmonth' class='monthswitcher'>".__('Next Month')."</button>";
  }
	echo $this->Form->end(__('Refresh')); 
	$salesPerPeriod=array();
	for ($i=0;$i<count($monthArray);$i++){
		$salesPerPeriod[$i]=0;
	}
	$salestable="";
	$bottlesAtable="";
	$bottlesBCtable="";
	if (!$bool_bottles && $userrole != ROLE_SALES){
		$salestable="<table id='venta cierre'>";
			$salestable.="<thead>";
				$salestable.="<tr>";
					$salestable.="<th class='hidden'>Client ID</th>";
					$salestable.="<th>".__('Descripción del cliente')."</th>";
					foreach ($monthArray as $period){
						$salestable.="<th class='centered'>".$period['period']."</th>";
					}
					$salestable.="<th class='centered'>".__('TOTAL')."</th>";
					$salestable.="<th class='centered'>".__('%')."</th>";
				$salestable.="</tr>";
			$salestable.="</thead>";
		
			$salestable.="<tbody>";
			$value_sold=0;
			$clientCounter=0;
			foreach ($salesArray as $client){
				$saleByClient=0;
				$salestable.="<tr>"; 
					$salestable.="<td class='hidden'>".$client['clientid']."</td>";
					$salestable.="<td>".($userrole != ROLE_SALES?$this->Html->link($client['clientname'], ['controller' => 'orders', 'action' => 'verVentasPorCliente', $client['clientid']]):$client['clientname'])."</td>";
					$i=0;
					//pr($salesArray[$clientCounter]);
					foreach ($client['sales'] as $clientSales){
						//pr($clientSales);
						$monthlysale=($clientSales['total']=="")?0:$clientSales['total'];
						
						$salestable.="<td class='centered currency'><span>".$monthlysale."</span></td>";
						$saleByClient+=$monthlysale;
						$salesPerPeriod[$i]+=$monthlysale;
						$i++;
					}
					$salestable.="<td class='centered currency'><span>".$saleByClient."</span></td>";
					$pct=0;
					if ($totalSale>0){
						$pct=100*$saleByClient/$totalSale;
					}
					else {
						$pct=100;
					}
					$salestable.="<td class='centered percentage'><span>".$pct."</span></td>";
				$salestable.="</tr>"; 
			}
					
				$salestable.="<tr class='totalrow'>";
					$salestable.="<td class='hidden'></td>";
					$salestable.="<td>Total</td>";
					for ($i=0;$i<count($salesPerPeriod);$i++){
						$salestable.="<td class='centered currency'><span>".$salesPerPeriod[$i]."</span></td>";
					}
					$salestable.="<td class='centered currency'><span>".$totalSale."</span></td>";
					$salestable.="<td class='centered percentage'><span>100</span></td>";
				$salestable.="</tr>";
			$salestable.="</tbody>";
		$salestable.="</table>";
	}
	else {
		$bottlesAtable="<table id='botellas A cierre '>";
      $bottlesAtable.="<thead>";
        $bottlesAtable.="<tr>";
          $bottlesAtable.="<th class='hidden'>Client ID</th>";
          $bottlesAtable.="<th>".__('Descripción del cliente')."</th>";
          foreach ($monthArray as $period){
            $bottlesAtable.="<th class='centered'>".$period['period']."</th>";
          }
          $bottlesAtable.="<th class='centered'>".__('TOTAL')."</th>";
          $bottlesAtable.="<th class='centered'>".__('%')."</th>";
        $bottlesAtable.="</tr>";
      $bottlesAtable.="</thead>";
		
		$bottlesAtable.="<tbody>";
		
		$value_sold=0;
		$clientCounter=0;
		foreach ($bottlesAArray as $client){
			//pr($client);
		
			$bottleByClient=0;
			$bottlesAtable.="<tr>"; 
			$bottlesAtable.="<td class='hidden'>".$client['clientid']."</td>";
			$bottlesAtable.="<td>".($userrole != ROLE_SALES?$this->Html->link($client['clientname'], ['controller' => 'orders', 'action' => 'verVentasPorCliente', $client['clientid']]):$client['clientname'])."</td>";
			$i=0;
			foreach ($client['bottles'] as $clientBottles){
				//pr($clientBottles);
				$monthlybottles=($clientBottles['totalBottles']=="")?0:$clientBottles['totalBottles'];
				
				$bottlesAtable.="<td class='centered number'><span>".$monthlybottles."</span></td>";
				$bottleByClient+=$monthlybottles;
				$salesPerPeriod[$i]+=$monthlybottles;
				$i++;
			}
			$bottlesAtable.="<td class='centered number'><span>".$bottleByClient."</span></td>";
			$pct=0;
			if ($totalABottles>0){
				$pct=100*$bottleByClient/$totalABottles;
			}
			else {
				$pct=100;
			}
			$bottlesAtable.="<td class='centered percentage'><span>".$pct."</span></td>";
			$bottlesAtable.="</tr>"; 
		}
		
		$bottlesAtable.="<tr class='totalrow'>";
		$bottlesAtable.="<td class='hidden'></td>";
		$bottlesAtable.="<td>Total</td>";
		for ($i=0;$i<count($salesPerPeriod);$i++){
			$bottlesAtable.="<td class='centered number'><span>".$salesPerPeriod[$i]."</span></td>";
		}
		$bottlesAtable.="<td class='centered number'><span>".$totalABottles."</span></td>";
		$bottlesAtable.="<td class='centered percentage'><span>100</span></td>";
		$bottlesAtable.="</tr>";
		$bottlesAtable.="</tbody>";
		$bottlesAtable.="</table>";
	
    if ($userrole != ROLE_SALES){
      for ($i=0;$i<count($monthArray);$i++){
        $salesPerPeriod[$i]=0;
      }		
      $bottlesBCtable="<table id='botellas BC cierre '>";
        $bottlesBCtable.="<thead>";
          $bottlesBCtable.="<tr>";
            $bottlesBCtable.="<th class='hidden'>Client ID</th>";
            $bottlesBCtable.="<th>".__('Descripción del cliente')."</th>";
            foreach ($monthArray as $period){
              $bottlesBCtable.="<th class='centered'>".$period['period']."</th>";
            }
            $bottlesBCtable.="<th class='centered'>".__('TOTAL')."</th>";
            $bottlesBCtable.="<th class='centered'>".__('%')."</th>";
          $bottlesBCtable.="</tr>";
        $bottlesBCtable.="</thead>";
      
      $bottlesBCtable.="<tbody>";
      
      $value_sold=0;
      $clientCounter=0;
      foreach ($bottlesBCArray as $client){
        //pr($client);
      
        $bottleByClient=0;
        $bottlesBCtable.="<tr>"; 
        $bottlesBCtable.="<td class='hidden'>".$client['clientid']."</td>";
        $bottlesBCtable.="<td>".$this->Html->link($client['clientname'], array('controller' => 'orders', 'action' => 'verVentasPorCliente', $client['clientid']))."</td>";
        $i=0;
        foreach ($client['bottles'] as $clientBottles){
          //pr($clientBottles);
          $monthlybottles=($clientBottles['totalBottles']=="")?0:$clientBottles['totalBottles'];
          
          $bottlesBCtable.="<td class='centered number'><span>".$monthlybottles."</span></td>";
          $bottleByClient+=$monthlybottles;
          $salesPerPeriod[$i]+=$monthlybottles;
          $i++;
        }
        $bottlesBCtable.="<td class='centered number'><span>".$bottleByClient."</span></td>";
        $pct=0;
        if ($totalBCBottles>0){
          $pct=100*$bottleByClient/$totalBCBottles;
        }
        else {
          $pct=100;
        }
        $bottlesBCtable.="<td class='centered percentage'><span>".$pct."</span></td>";
        $bottlesBCtable.="</tr>"; 
      }
      
      $bottlesBCtable.="<tr class='totalrow'>";
      $bottlesBCtable.="<td class='hidden'></td>";
      $bottlesBCtable.="<td>Total</td>";
      for ($i=0;$i<count($salesPerPeriod);$i++){
        $bottlesBCtable.="<td class='centered number'><span>".$salesPerPeriod[$i]."</span></td>";
      }
      $bottlesBCtable.="<td class='centered number'><span>".$totalBCBottles."</span></td>";
      $bottlesBCtable.="<td class='centered percentage'><span>100</span></td>";
      $bottlesBCtable.="</tr>";
      $bottlesBCtable.="</tbody>";
      $bottlesBCtable.="</table>";
    }
	}
	
  if ($userrole != ROLE_SALES){
    echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteCierre'), array( 'class' => 'btn btn-primary')); 
  }
	if (!$bool_bottles && $userrole != ROLE_SALES){
		echo "<h2>".__('Reporte de Cierre en Dinero')."</h2>"; 
		echo $salestable; 
	}
	else {
		echo "<h2>".__('Reporte de Cierre en Envases para Calidad A')."</h2>"; 
		echo $bottlesAtable;
    if ($userrole != ROLE_SALES){  
      echo "<h2>".__('Reporte de Cierre en Envases para Calidad B y C')."</h2>"; 
      echo $bottlesBCtable; 
    }
	}
	
	
	$_SESSION['reporteCierre'] = $salestable.$bottlesAtable.$bottlesBCtable;
?>