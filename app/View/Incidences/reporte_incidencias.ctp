<script>
	function formatNumbers(){
		$("td.number").each(function(){
			if (Math.abs(parseFloat($(this).text()))<0.001){
				$(this).text("0");
			}
			if (parseFloat($(this).text())<0){
				$(this).prepend("-");
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
	
	$(document).ready(function(){
		formatNumbers();
		formatCSCurrencies();
		formatUSDCurrencies();
	});
</script>

<div class="incidences index fullwidth">
<?php 
	echo "<h2>".__('Reporte de Incidencias')."</h2>";
  $excelExport="";  
	echo $this->Form->create('Report');
		echo "<fieldset>";
      echo "<div class='container-fluid'>";
        echo "<div class='row'>";  
          echo "<div class='col-md-6'>";	
            echo $this->Form->input('Report.startdate',array('type'=>'date','label'=>__('Start Date'),'dateFormat'=>'DMY','default'=>$startDate));
            echo $this->Form->input('Report.enddate',array('type'=>'date','label'=>__('End Date'),'dateFormat'=>'DMY','default'=>$endDate));
            echo $this->Form->input('Report.display_id',array('label'=>__('Mostrar datos'),'default'=>$displayId));
          echo "</div>";  
          echo "<div class='col-md-6'>";	  
            $overviewTable="";
            $overviewTableHeader="";
            $overviewTableHeader.="<thead>";
              $overviewTableHeader.="<tr>";
              switch ($displayId){
                case DISPLAY_BY_INCIDENCE:
                  $overviewTableHeader.="<th>Incidencia</th>";
                  break;
                case DISPLAY_BY_OPERATOR:
                  $overviewTableHeader.="<th>Operador</th>";
                  break;
                case DISPLAY_BY_MACHINE:
                  $overviewTableHeader.="<th>Máquina</th>";
                  break;
                case DISPLAY_BY_SHIFT:
                  $overviewTableHeader.="<th>Turno</th>";
                  break;  
              }
                $overviewTableHeader.="<th>Cantidad</th>";
              $overviewTableHeader.="</tr>";
            $overviewTableHeader.="</thead>";
            $overviewTableBody="";
            $total=0;
            switch ($displayId){
              case DISPLAY_BY_INCIDENCE:
                foreach ($incidences as $incidence){
                  //pr($incidence);
                  $total+=count($incidence['ProductionRuns']);
                  $overviewTableBody.="<tr>";
                    $overviewTableBody.="<td>".$this->Html->Link($incidence['Incidence']['name'],['action'=>'verIncidencia',$incidence['Incidence']['id']])."</td>";
                    $overviewTableBody.="<td>".count($incidence['ProductionRuns'])."</td>";
                  $overviewTableBody.="</tr>";
                }  
                break;
              case DISPLAY_BY_OPERATOR:
                foreach ($operators as $operator){
                  $total+=count($operator['ProductionRuns']);
                  $overviewTableBody.="<tr>";
                    $overviewTableBody.="<td>".$this->Html->Link($operator['Operator']['name'],['action'=>'view',$operator['Operator']['id']])."</td>";
                    $overviewTableBody.="<td>".count($operator['ProductionRuns'])."</td>";
                  $overviewTableBody.="</tr>";
                }
                break;
              case DISPLAY_BY_MACHINE:
                foreach ($machines as $machine){
                  $total+=count($machine['ProductionRuns']);
                  $overviewTableBody.="<tr>";
                    $overviewTableBody.="<td>".$this->Html->Link($machine['Machine']['name'],['action'=>'view',$machine['Machine']['id']])."</td>";
                    $overviewTableBody.="<td>".count($machine['ProductionRuns'])."</td>";
                  $overviewTableBody.="</tr>";
                }
                break;
              case DISPLAY_BY_SHIFT:
                foreach ($shifts as $shift){
                  $total+=count($shift['ProductionRuns']);
                  $overviewTableBody.="<tr>";
                    $overviewTableBody.="<td>".$this->Html->Link($shift['Shift']['name'],['action'=>'view',$shift['Shift']['id']])."</td>";
                    $overviewTableBody.="<td>".count($shift['ProductionRuns'])."</td>";
                  $overviewTableBody.="</tr>";
                }
                break;  
            }
            $totalRow="";
            $totalRow.="<tr class='totalrow'>";
              $totalRow.="<td>Total</td>";
              $totalRow.="<td>".$total."</td>";
            $totalRow.="</tr>";
            $overviewTableBody="<tbody>".$totalRow.$overviewTableBody.$totalRow."</tbody>";
            $tableId="resumen_por_";
            switch ($displayId){
              case DISPLAY_BY_INCIDENCE:
                $tableId.="Incidencia";
                break;
              case DISPLAY_BY_OPERATOR:
                $tableId.="Operador";
                break;
              case DISPLAY_BY_MACHINE:
                $tableId.="Máquina";
                break;
              case DISPLAY_BY_SHIFT:
                $tableId.="Turno";
                break;
            }         
            $overviewTable="<table id='".$tableId."'>".$overviewTableHeader.$overviewTableBody."</table>";
            echo $overviewTable;
            $excelExport.=$overviewTable;
          echo "</div>";
        echo "</div>";
      echo "</div>";
		echo "</fieldset>";
		echo "<button id='previousmonth' class='monthswitcher'>Mes Previo</button>";
		echo "<button id='nextmonth' class='monthswitcher'>Mes Siguiente</button>";
	echo $this->Form->end(__('Refresh'));
  echo "<br/>";
	echo $this->Html->link(__('Guardar como Excel'), array('action' => 'guardarReporteIncidencias',$displayId), array( 'class' => 'btn btn-primary'));
?> 
</div>
<div>
<?php
	
  switch ($displayId){
    case DISPLAY_BY_INCIDENCE:
      foreach ($incidences as $incidence){
        if (count($incidence['ProductionRuns'])){
          $tableContents=$this->ProductionRunDisplay->productionRunTableContents($incidence['ProductionRuns'], false,$userrole);
          $table="<table id='incidencia_".$incidence['Incidence']['name']."'>".$tableContents."</table>";
          echo "<h3>Incidencia ".$incidence['Incidence']['name']."</h3>";
          echo $table;
          $excelExport.=$table;
        }
      }  
      break;
	  case DISPLAY_BY_OPERATOR:
      foreach ($operators as $operator){
        if (count($operator['ProductionRuns'])){
          $tableContents=$this->ProductionRunDisplay->productionRunTableContents($operator['ProductionRuns'], true,$userrole);
          $table="<table id='incidencia_".$operator['Operator']['name']."'>".$tableContents."</table>";
          echo "<h3>Operador ".$operator['Operator']['name']."</h3>";
          echo $table;
          $excelExport.=$table;
        }
      }
      break;
    case DISPLAY_BY_MACHINE:
      foreach ($machines as $machine){
        if (count($machine['ProductionRuns'])){
          $tableContents=$this->ProductionRunDisplay->productionRunTableContents($machine['ProductionRuns'], true,$userrole);
          $table="<table id='incidencia_".$machine['Machine']['name']."'>".$tableContents."</table>";
          echo "<h3>Máquina ".$machine['Machine']['name']."</h3>";
          echo $table;
          $excelExport.=$table;
        }
      }
      break;
    case DISPLAY_BY_SHIFT:
      
      foreach ($shifts as $shift){
        if (count($shift['ProductionRuns'])){
          $tableContents=$this->ProductionRunDisplay->productionRunTableContents($shift['ProductionRuns'], true,$userrole);
          $table="<table id='incidencia_".$shift['Shift']['name']."'>".$tableContents."</table>";
          echo "<h3>Turno ".$shift['Shift']['name']."</h3>";
          echo $table;
          $excelExport.=$table;
        }
      }
      break;
  }
	$_SESSION['reporteIncidencias'] = $excelExport;
?>
</div>
