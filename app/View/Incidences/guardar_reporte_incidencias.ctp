<?php 
  $title="Reporte_Incidencias_por_";
  define('DISPLAY_BY_INCIDENCE','0');
  define('DISPLAY_BY_OPERATOR','1');
  define('DISPLAY_BY_MACHINE','2');
  define('DISPLAY_BY_SHIFT','3');
		
  switch ($displayId){
    case DISPLAY_BY_INCIDENCE:
      $title.="Incidencia_";
      break;
    case DISPLAY_BY_OPERATOR:
      $title.="Operador_";
      break;
    case DISPLAY_BY_MACHINE:
      $title.="Máquina_";
      break;
    case DISPLAY_BY_SHIFT:
      $title.="Turno_";
      break;
  }
  $title.=date('d_m_Y').".xlsx";
	$this->PhpExcel->generalExport($exportData,$title,"");
?>
	
	