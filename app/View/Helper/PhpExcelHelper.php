<?php
App::uses('AppHelper', 'View/Helper');

/**
 * Helper for working with PHPExcel class.
 *
 * @package PhpExcel
 * @author segy
 */
class PhpExcelHelper extends AppHelper {
    /**
     * Instance of PHPExcel class
     *
     * @var PHPExcel
     */
    protected $_xls;

    /**
     * Pointer to current row
     *
     * @var int
     */
    protected $_row = 1;

    /**
     * Internal table params
     *
     * @var array
     */
    protected $_tableParams;

    /**
     * Number of rows
     *
     * @var int
     */
    protected $_maxRow = 0;

    /**
     * Create new worksheet or load it from existing file
     *
     * @return $this for method chaining
     */
    public function createWorksheet() {
        // load vendor classes
        App::import('Vendor', 'PhpExcel.PHPExcel');

        $this->_xls = new PHPExcel();
        $this->_row = 1;

        return $this;
    }

    /**
     * Create new worksheet from existing file
     *
     * @param string $file path to excel file to load
     * @return $this for method chaining
     */
    public function loadWorksheet($file) {
        // load vendor classes
        App::import('Vendor', 'PhpExcel.PHPExcel');

        $this->_xls = PHPExcel_IOFactory::load($file);
        $this->setActiveSheet(0);
        $this->_row = 1;

        return $this;
    }

    /**
     * Add sheet
     *
     * @param string $name
     * @return $this for method chaining
     */
    public function addSheet($name) {
        $index = $this->_xls->getSheetCount();
        $this->_xls->createSheet($index)
            ->setTitle($name);

        $this->setActiveSheet($index);

        return $this;
    }

    /**
     * Set active sheet
     *
     * @param int $sheet
     * @return $this for method chaining
     */
    public function setActiveSheet($sheet) {
        $this->_maxRow = $this->_xls->setActiveSheetIndex($sheet)->getHighestRow();
        $this->_row = 1;

        return $this;
    }

    /**
     * Set worksheet name
     *
     * @param string $name name
     * @return $this for method chaining
     */
    public function setSheetName($name) {
        $this->_xls->getActiveSheet()->setTitle($name);

        return $this;
    }

    /**
     * Overloaded __call
     * Move call to PHPExcel instance
     *
     * @param string function name
     * @param array arguments
     * @return the return value of the call
     */
    public function __call($name, $arguments) {
        return call_user_func_array(array($this->_xls, $name), $arguments);
    }

    /**
     * Set default font
     *
     * @param string $name font name
     * @param int $size font size
     * @return $this for method chaining
     */
    public function setDefaultFont($name, $size) {
        $this->_xls->getDefaultStyle()->getFont()->setName($name);
        $this->_xls->getDefaultStyle()->getFont()->setSize($size);

        return $this;
    }

    /**
     * Set row pointer
     *
     * @param int $row number of row
     * @return $this for method chaining
     */
    public function setRow($row) {
        $this->_row = (int)$row;

        return $this;
    }

    /**
     * Start table - insert table header and set table params
     *
     * @param array $data data with format:
     *   label   -   table heading
     *   width   -   numeric (leave empty for "auto" width)
     *   filter  -   true to set excel filter for column
     *   wrap    -   true to wrap text in column
     * @param array $params table parameters with format:
     *   offset  -   column offset (numeric or text)
     *   font    -   font name of the header text
     *   size    -   font size of the header text
     *   bold    -   true for bold header text
     *   italic  -   true for italic header text
     * @return $this for method chaining
     */
    public function addTableHeader($data, $params = array()) {
        // offset
        $offset = 0;
        if (isset($params['offset']))
            $offset = is_numeric($params['offset']) ? (int)$params['offset'] : PHPExcel_Cell::columnIndexFromString($params['offset']);

        // font name
        if (isset($params['font']))
            $this->_xls->getActiveSheet()->getStyle($this->_row)->getFont()->setName($params['font']);

        // font size
        if (isset($params['size']))
            $this->_xls->getActiveSheet()->getStyle($this->_row)->getFont()->setSize($params['size']);

        // bold
        if (isset($params['bold']))
            $this->_xls->getActiveSheet()->getStyle($this->_row)->getFont()->setBold($params['bold']);

        // italic
        if (isset($params['italic']))
            $this->_xls->getActiveSheet()->getStyle($this->_row)->getFont()->setItalic($params['italic']);

        // set internal params that need to be processed after data are inserted
        $this->_tableParams = array(
            'header_row' => $this->_row,
            'offset' => $offset,
            'row_count' => 0,
            'auto_width' => array(),
            'filter' => array(),
            'wrap' => array()
        );

        foreach ($data as $d) {
            // set label
            $this->_xls->getActiveSheet()->setCellValueByColumnAndRow($offset, $this->_row, $d['label']);

            // set width
            if (isset($d['width']) && is_numeric($d['width']))
                $this->_xls->getActiveSheet()->getColumnDimensionByColumn($offset)->setWidth((float)$d['width']);
            else
                $this->_tableParams['auto_width'][] = $offset;

            // filter
            if (isset($d['filter']) && $d['filter'])
                $this->_tableParams['filter'][] = $offset;

            // wrap
            if (isset($d['wrap']) && $d['wrap'])
                $this->_tableParams['wrap'][] = $offset;

            $offset++;
        }
        $this->_row++;

        return $this;
    }

    /**
     * Write array of data to current row
     *
     * @param array $data
     * @return $this for method chaining
     */
    public function addTableRow($data) {
        $offset = $this->_tableParams['offset'];

        foreach ($data as $d)
            $this->_xls->getActiveSheet()->setCellValueByColumnAndRow($offset++, $this->_row, $d);

        $this->_row++;
        $this->_tableParams['row_count']++;

        return $this;
    }

    /**
     * End table - set params and styles that required data to be inserted first
     *
     * @return $this for method chaining
     */
    public function addTableFooter() {
        // auto width
        foreach ($this->_tableParams['auto_width'] as $col)
            $this->_xls->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);

        // filter (has to be set for whole range)
        if (count($this->_tableParams['filter']))
            $this->_xls->getActiveSheet()->setAutoFilter(PHPExcel_Cell::stringFromColumnIndex($this->_tableParams['filter'][0]) . ($this->_tableParams['header_row']) . ':' . PHPExcel_Cell::stringFromColumnIndex($this->_tableParams['filter'][count($this->_tableParams['filter']) - 1]) . ($this->_tableParams['header_row'] + $this->_tableParams['row_count']));

        // wrap
        foreach ($this->_tableParams['wrap'] as $col)
            $this->_xls->getActiveSheet()->getStyle(PHPExcel_Cell::stringFromColumnIndex($col) . ($this->_tableParams['header_row'] + 1) . ':' . PHPExcel_Cell::stringFromColumnIndex($col) . ($this->_tableParams['header_row'] + $this->_tableParams['row_count']))->getAlignment()->setWrapText(true);

        return $this;
    }

    /**
     * Write array of data to current row starting from column defined by offset
     *
     * @param array $data
     * @return $this for method chaining
     */
    public function addData($data, $offset = 0) {
        // solve textual representation
        if (!is_numeric($offset))
            $offset = PHPExcel_Cell::columnIndexFromString($offset);

        foreach ($data as $d)
            $this->_xls->getActiveSheet()->setCellValueByColumnAndRow($offset++, $this->_row, $d);

        $this->_row++;

        return $this;
    }

    /**
     * Get array of data from current row
     *
     * @param int $max
     * @return array row contents
     */
    public function getTableData($max = 100) {
        if ($this->_row > $this->_maxRow)
            return false;

        $data = array();

        for ($col = 0; $col < $max; $col++)
            $data[] = $this->_xls->getActiveSheet()->getCellByColumnAndRow($col, $this->_row)->getValue();

        $this->_row++;

        return $data;
    }

    /**
     * Get writer
     *
     * @param $writer
     * @return PHPExcel_Writer_Iwriter
     */
    public function getWriter($writer) {
        return PHPExcel_IOFactory::createWriter($this->_xls, $writer);
    }

    /**
     * Save to a file
     *
     * @param string $file path to file
     * @param string $writer
     * @return bool
     */
    public function save($file, $writer = 'Excel2007') {
        $objWriter = $this->getWriter($writer);
        return $objWriter->save($file);
    }

    /**
     * Output file to browser
     *
     * @param string $file path to file
     * @param string $writer
     * @return exit on this call
     */
    public function output($filename = 'export.xlsx', $writer = 'Excel2007') {
        // remove all output
        ob_end_clean();

        // headers
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // writer
        $objWriter = $this->getWriter($writer);
        $objWriter->save('php://output');

        exit;
    }

    /**
     * Free memory
     *
     * @return void
     */
    public function freeMemory() {
        $this->_xls->disconnectWorksheets();
        unset($this->_xls);
    }
	
	public function generalExport($content=null,$filename=null,$writer = 'Excel2007'){
		ini_set("memory_limit", "-1");
		ini_set("set_time_limit", "0");
		set_time_limit(0);

		$username = "Ornasa";
		$usermail = "";
		$usercompany = "intersinaptico";

		//echo "running ...";
		if(empty($content)) {
		  echo "<br />No Table Variable Present, nothing to Export.";
		  exit;
		}
		else {
			$tablevar =$content;
		}
		
		//if(!isset($_GET['limit'])) {
		//  $limit = 16;// maximum number of Excel tabs to create, optional 
		//}
		//else{ 
		//  $limit = $_GET['limit'];
		//}
		$htmltable = $content;
		
		//echo "still running ...";
		if(strlen($htmltable) == strlen(strip_tags($htmltable)) ) {
			echo "<br />Invalid HTML Table after Stripping Tags, nothing to Export.";
			exit;
		}
		$htmltable = strip_tags($htmltable, "<table><tr><th><thead><tbody><tfoot><td><br><br /><b><span>");
		$htmltable = str_replace("<br />", "\n", $htmltable);
		$htmltable = str_replace("<br/>", "\n", $htmltable);
		$htmltable = str_replace("<br>", "\n", $htmltable);
		$htmltable = str_replace("&nbsp;", " ", $htmltable);
		$htmltable = str_replace("\n\n", "\n", $htmltable);
		//echo $htmltable;
		//exit;

		//  Extract HTML table contents to array

		$dom = new domDocument;
		//$dom->loadHTML($htmltable);
		$dom->loadHTML('<?xml encoding="UTF-8">' . $htmltable);


		// dirty fix
		foreach ($dom->childNodes as $item)
			if ($item->nodeType == XML_PI_NODE)
				$dom->removeChild($item); // remove hack
		$dom->encoding = 'UTF-8'; // insert proper

		//echo "dirty fix applied\r\n";

		if(!$dom) {
		  echo "<br />Invalid HTML DOM, nothing to Export.";
		  exit;
		}
	
		$dom->preserveWhiteSpace = false;             // remove redundant whitespace
		$tables = $dom->getElementsByTagName('table');
		if(!is_object($tables)) {
		  echo "<br />Invalid HTML Table DOM, nothing to Export.";
		  exit;
		}
		$tbcnt = $tables->length - 1;                 // count minus 1 for 0 indexed loop over tables
		//echo "table count is ".$tbcnt."!<br/>";
		//exit;
		//if($tbcnt > $limit) {
		//  $tbcnt = $limit;
		//}
		//echo "table count (starting from 0) is ".$tbcnt."\r\n";

		// Create new PHPExcel object with default attributes
		//$objPHPExcel = new PHPExcel();
		$this->createWorksheet(); 
		$objPHPExcel =$this->_xls;     
        //$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		//$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
		$tm = date('Ymd');
		$pos = strpos($usermail, "@");
		$user = substr($usermail, 0, $pos);
		$user = str_replace(".","",$user);
		//$tfn = $user."_".$tm."_".$tablevar.".xlsx";
		if (!isset($filename)){
			$filename="report";
		}
		//echo "file name retrieved \r\n";
		$tfn = $tm.$filename.".xlsx";
		$fname = "AuditLog/".$tfn;
		$fname = $tfn;
		
		$objPHPExcel->getProperties()->setCreator($username)
									 ->setLastModifiedBy($username)
									 ->setTitle("Automated Export")
									 ->setSubject("Automated Report Generation")
									 ->setDescription("Automated report generation.")
									 ->setKeywords("Exported File")
									->setCompany($usercompany)
									 ->setCategory("Export");
		//
		// Loop over tables in DOM to create an array, each table becomes a worksheet
		//
		for($z=0;$z<=$tbcnt;$z++) {
		  $maxcols = 0;
		  $totrows = 0;
		  $headrows = array();
		  $bodyrows = array();
		  $r = 0;
		  $h = 0;
		  $rows = $tables->item($z)->getElementsByTagName('tr');
		  $totrows = $rows->length;
		  //echo "before entering row loop \r\n";		  
		  foreach ($rows as $row) {
			  $ths = $row->getElementsByTagName('th');
			  if(is_object($ths)) {
				if($ths->length > 0) {
				  $headrows[$h]['colcnt'] = $ths->length;
				  if($ths->length > $maxcols) {
					$maxcols = $ths->length;
					
				  }
				  $nodes = $ths->length - 1;
				  for($x=0;$x<=$nodes;$x++) {
					$thishdg = $ths->item($x)->nodeValue;
					$visible=true;
					if($ths->item($x)->hasAttribute('class')) {
						if ($ths->item($x)->getAttribute('class')=='separator'||$ths->item($x)->getAttribute('class')=='hidden'){
							$visible=false;
						}
					}
					if ($visible){
						$headrows[$h]['th'][] = $thishdg;
						//$headrows[$h]['bold'][] = findBoldText(innerHTML($ths->item($x)));
						//$headrows[$h]['bold'][] = $this->findBoldText($this->innerHTML($ths->item($x)));
						
						//if($ths->item($x)->hasAttribute('style')) {
						//  $style = $ths->item($x)->getAttribute('style');
						//  $stylecolor = findStyleColor($style);
						//  if($stylecolor == '') {
						//		$headrows[$h]['color'][] = $this->findSpanColor($this->innerHTML($ths->item($x)));
						//  }
						//	else{
						//		$headrows[$h]['color'][] = $stylecolor;
						//	}
						//}
						//else{
						//  $headrows[$h]['color'][] = $this->findSpanColor($this->innerHTML($ths->item($x)));
						//}
						if($ths->item($x)->hasAttribute('colspan')) {
						  $headrows[$h]['colspan'][] = $ths->item($x)->getAttribute('colspan');
						}
						else{
						  $headrows[$h]['colspan'][] = 1;
						}
						if($ths->item($x)->hasAttribute('align')) {
						  $headrows[$h]['align'][] = $ths->item($x)->getAttribute('align');
						}
						else{
						  $headrows[$h]['align'][] = 'left';
						}
						if($ths->item($x)->hasAttribute('valign')) {
						  $headrows[$h]['valign'][] = $ths->item($x)->getAttribute('valign');
						}
						else{
						  $headrows[$h]['valign'][] = 'top';
						}
						if($ths->item($x)->hasAttribute('bgcolor')) {
						  $headrows[$h]['bgcolor'][] = str_replace("#", "", $ths->item($x)->getAttribute('bgcolor'));
						}
						else{
						  $headrows[$h]['bgcolor'][] = 'FFFFFF';
						}
					}
				  }
				  $h++;
				}
			  }
		  }
		  //echo "headerrow processed, now moving to the general rows<br/>";
		  foreach ($rows as $row) {
			  $tds = $row->getElementsByTagName('td');
			  if(is_object($tds)) {
				if($tds->length > 0) {
				  $bodyrows[$r]['colcnt'] = $tds->length;
				  if($tds->length > $maxcols) {
					$maxcols = $tds->length;
				  }
				  $nodes = $tds->length - 1;
				  for($x=0;$x<=$nodes;$x++) {
					$thistxt = $tds->item($x)->nodeValue;
					$visible=true;
					if($tds->item($x)->hasAttribute('class')) {
						if ($tds->item($x)->getAttribute('class')=='separator'||$tds->item($x)->getAttribute('class')=='hidden'){
							$visible=false;
						}
					}
					if ($visible){
						//echo "\$thistxt is ".$thistxt."<br/>";
						$bodyrows[$r]['td'][] = $thistxt;
						//$bodyrows[$r]['bold'][] = $this->findBoldText($this->innerHTML($tds->item($x)));
						
						//if($tds->item($x)->hasAttribute('style')) {
						//  $style = $tds->item($x)->getAttribute('style');
						//  $stylecolor = findStyleColor($style);
						//  if($stylecolor == '') {
							//$bodyrows[$r]['color'][] = findSpanColor(innerHTML($tds->item($x)));
						//  }
						//  else{
						//	$bodyrows[$r]['color'][] = $stylecolor;
						//  }
						//}
						//else{
						  //$bodyrows[$r]['color'][] = findSpanColor(innerHTML($tds->item($x)));
						//}
						if($tds->item($x)->hasAttribute('colspan')) {
						  $bodyrows[$r]['colspan'][] = $tds->item($x)->getAttribute('colspan');
						}else{
						  $bodyrows[$r]['colspan'][] = 1;
						}
						if($tds->item($x)->hasAttribute('align')) {
						  $bodyrows[$r]['align'][] = $tds->item($x)->getAttribute('align');
						}else{
						  $bodyrows[$r]['align'][] = 'left';
						}
						if($tds->item($x)->hasAttribute('valign')) {
						  $bodyrows[$r]['valign'][] = $tds->item($x)->getAttribute('valign');
						}else{
						  $bodyrows[$r]['valign'][] = 'top';
						}
						if($tds->item($x)->hasAttribute('bgcolor')) {
						  $bodyrows[$r]['bgcolor'][] = str_replace("#", "", $tds->item($x)->getAttribute('bgcolor'));
						}else{
						  $bodyrows[$r]['bgcolor'][] = 'FFFFFF';
						}
					}
				  }
				  $r++;
				}
			  }
		  }
		  //echo "starting sheet creation \r\n";
		  
		  if($z > 0) {
			//$objPHPExcel->createSheet($z);
		  }
		  $suf = $z + 1;
		  //$tableid = $tablevar.$suf;
		  // Higher up, wksheetname has been set to the id of the table
		  if($tables->item($z)->hasAttribute('id')) {
			$wksheetname = $tables->item($z)->getAttribute('id');
		  }
		  else{
			$tableid = "Reporte".$suf;
			$wksheetname = ucfirst($tableid);
		  }
		  echo "z is ".$z."<br/>";
		  echo "wksheetname is ".$wksheetname."<br/>";
		  
		  if($z > 0) {
			$this->addSheet($wksheetname);
			echo "sheet added<br/>";
		  }
		  else {
			$this->setSheetName($wksheetname);
		  }
		  
		  //$objPHPExcel->setActiveSheetIndex($z);                      // each sheet corresponds to a table in html
		  //$objPHPExcel->getActiveSheet()->setTitle($wksheetname);     // tab name
		  $worksheet = $objPHPExcel->getActiveSheet();                // set worksheet we're working on
		  $style_overlay = array('font' =>
							array('color' =>
							  array('rgb' => '000000'),'bold' => false,),
								  'fill' 	=>
									  array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'CCCCFF')),
								  'alignment' =>
									  array('wrap' => true, 'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
												 'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP),
								  'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
													 'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
													 'left' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
													 'right' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
							   );
		  $xcol = '';
		  $xrow = 1;
		  $usedhdrows = 0;
		  $heightvars = array(1=>'42', 2=>'42', 3=>'48', 4=>'52', 5=>'58', 6=>'64', 7=>'68', 8=>'76', 9=>'82');
		  $mergedcells=false;
		  for($h=0;$h<count($headrows);$h++) {
			$th = $headrows[$h]['th'];
			$colspans = $headrows[$h]['colspan'];
			$aligns = $headrows[$h]['align'];
			$valigns = $headrows[$h]['valign'];
			$bgcolors = $headrows[$h]['bgcolor'];
			$colcnt = $headrows[$h]['colcnt'];
			//$colors = $headrows[$h]['color'];
			//$bolds = $headrows[$h]['bold'];
			$usedhdrows++;
			$mergedcells = false;
			for($t=0;$t<count($th);$t++) {
			  if($xcol == '') {$xcol = 'A';}else{$xcol++;}
			  $thishdg = $th[$t];
			  $thisalign = $aligns[$t];
			  $thisvalign = $valigns[$t];
			  $thiscolspan = $colspans[$t];
			  //$thiscolor = $colors[$t];
			  $thisbg = $bgcolors[$t];
			  //$thisbold = $bolds[$t];
			  //$strbold = ($thisbold==true) ? 'true' : 'false';
			  //if($thisbg == 'FFFFFF') {
				//$style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_NONE;
			  //}else{
				//$style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
			  //}
			  $style_overlay['alignment']['vertical'] = $thisvalign;              // set styles for cell
			  $style_overlay['alignment']['horizontal'] = $thisalign;
			  //$style_overlay['font']['color']['rgb'] = $thiscolor;
			  //$style_overlay['font']['bold'] = $thisbold;
			  //$style_overlay['fill']['color']['rgb'] = $thisbg;
			  $worksheet->setCellValue($xcol.$xrow, $thishdg);
			  $worksheet->getStyle($xcol.$xrow)->applyFromArray($style_overlay);
			  if($thiscolspan > 1) {                                                // spans more than 1 column
				$mergedcells = true;
				$lastxcol = $xcol;
				for($j=1;$j<$thiscolspan;$j++) {
				  $lastxcol++;
				  $worksheet->setCellValue($lastxcol.$xrow, '');
				  $worksheet->getStyle($lastxcol.$xrow)->applyFromArray($style_overlay);
				}
				$cellRange = $xcol.$xrow.':'.$lastxcol.$xrow;
				$worksheet->mergeCells($cellRange);
				$worksheet->getStyle($cellRange)->applyFromArray($style_overlay);
				$num_newlines = substr_count($thishdg, "\n");                       // count number of newline chars
				if($num_newlines > 1) {
				  $rowheight = $heightvars[1];                                      // default to 35
				  if(array_key_exists($num_newlines, $heightvars)) {
					$rowheight = $heightvars[$num_newlines];
				  }else{
					$rowheight = 75;
				  }
				  $worksheet->getRowDimension($xrow)->setRowHeight($rowheight);     // adjust heading row height
				}
				$xcol = $lastxcol;
			  }
			}
			$xrow++;
			$xcol = '';
		  }
		  //Put an auto filter on last row of heading only if last row was not merged
		  if(!$mergedcells) {
			$worksheet->setAutoFilter("A$usedhdrows:" . $worksheet->getHighestColumn() . $worksheet->getHighestRow() );
		  }
		  // Freeze heading lines starting after heading lines
		  $usedhdrows++;
		  $worksheet->freezePane("A$usedhdrows");
		  //
		  // Loop thru data rows and write them out
		  //
		  //echo "start data row loop \r\n";
		 
		  $xcol = '';
		  $xrow = $usedhdrows;
		  for($b=0;$b<count($bodyrows);$b++) {
			$td = $bodyrows[$b]['td'];
			$colcnt = $bodyrows[$b]['colcnt'];
			$colspans = $bodyrows[$b]['colspan'];
			$aligns = $bodyrows[$b]['align'];
			$valigns = $bodyrows[$b]['valign'];
			$bgcolors = $bodyrows[$b]['bgcolor'];
			//$colors = $bodyrows[$b]['color'];
			//$bolds = $bodyrows[$b]['bold'];
			for($t=0;$t<count($td);$t++) {
			  if($xcol == '') {$xcol = 'A';}else{$xcol++;}
			  $thistext = $td[$t];
			  //echo $thistext;
			  $thisalign = $aligns[$t];
			  $thisvalign = $valigns[$t];
			  $thiscolspan = $colspans[$t];
			  //$thiscolor = $colors[$t];
			  $thisbg = $bgcolors[$t];
			  //$thisbold = $bolds[$t];
			  //$strbold = ($thisbold==true) ? 'true' : 'false';
			  if($thisbg == 'FFFFFF') {
				$style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_NONE;
			  }
			  else{
				$style_overlay['fill']['type'] = PHPExcel_Style_Fill::FILL_SOLID;
			  }
			  $style_overlay['alignment']['vertical'] = $thisvalign;              // set styles for cell
			  $style_overlay['alignment']['horizontal'] = $thisalign;
			  //$style_overlay['font']['color']['rgb'] = $thiscolor;
			  //$style_overlay['font']['bold'] = $thisbold;
			  //$style_overlay['fill']['color']['rgb'] = $thisbg;
			  if($thiscolspan == 1) {
				$worksheet->getColumnDimension($xcol)->setWidth(25);
			  }
			  $worksheet->setCellValue($xcol.$xrow, $thistext);
			  $worksheet->getStyle($xcol.$xrow)->applyFromArray($style_overlay);
			  if($thiscolspan > 1) {                                                // spans more than 1 column
				$lastxcol = $xcol;
				for($j=1;$j<$thiscolspan;$j++) {
				  $lastxcol++;
				}
				$cellRange = $xcol.$xrow.':'.$lastxcol.$xrow;
				//if($debug) {
				//  fwrite($handle, "\nmergeCells: ".$xcol.":".$xrow." ".$lastxcol.":".$xrow);
				//}
				$worksheet->mergeCells($cellRange);
				$worksheet->getStyle($cellRange)->applyFromArray($style_overlay);
				$xcol = $lastxcol;
			  }
			}
			$xrow++;
			$xcol = '';
		  }
		  
		  //echo "end data row loop, start autosizing \r\n";

		  // autosize columns to fit data
		  $azcol = 'A';
		  for($x=1;$x==$maxcols;$x++) {
			$worksheet->getColumnDimension($azcol)->setAutoSize(true);
			$azcol++;
		  }
		} // end for over tables
		$objPHPExcel->setActiveSheetIndex(0); // set to first worksheet before close
		
		// Write to Browser
		//echo "sending to browser now \r\n";
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header("Content-Disposition: attachment;filename=$fname");
		header('Cache-Control: max-age=0');
		
		//PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
		//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter = $this->getWriter('Excel2007');
		//$objWriter->save($fname);
        $objWriter->save('php://output');
		
		$this->output($filename,'Excel2007');
		   
        // headers
        //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        //header('Content-Disposition: attachment;filename="' . $filename . '"');
        //header('Cache-Control: max-age=0');

        // writer
        //$objWriter = $this->getWriter($writer);
        //$objWriter->save('php://output');

        exit;

		
	}
}
