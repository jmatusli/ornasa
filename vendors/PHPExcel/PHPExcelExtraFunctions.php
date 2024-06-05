<?php 
	function innerHTML($node) {
		$doc = $node->ownerDocument;
		$frag = $doc->createDocumentFragment();
		foreach ($node->childNodes as $child) {
			$frag->appendChild($child->cloneNode(TRUE));
		}
		return $doc->saveXML($frag);
	}
	function findSpanColor($node) {
		$pos = stripos($node, "color:");       // ie: looking for style='color: #FF0000;'
		if ($pos === false) {                  //                        12345678911111
			return '000000';                     //                                 01234
		}
		$node = substr($node, $pos);           // truncate to color: start
		$start = "#";                          // looking for html color string
		$end = ";";                            // should end with semicolon
		$node = " ".$node;                     // prefix node with blank
        $ini = stripos($node,$start);          // look for #
        if ($ini === false) return "000000";   // not found, return default color of black
        $ini += strlen($start);                // get 1 byte past start string
        $len = stripos($node,$end,$ini) - $ini; // grab substr between start and end positions
        return substr($node,$ini,$len);        // return the RGB color without # sign
	}
	function findStyleColor($style) {
		$pos = stripos($style, "color:");      // ie: looking for style='color: #FF0000;'
		if ($pos === false) {                  //                        12345678911111
			return '';                           //                                 01234
		}
		$style = substr($style, $pos);           // truncate to color: start
		$start = "#";                          // looking for html color string
		$end = ";";                            // should end with semicolon
		$style = " ".$style;                     // prefix node with blank
        $ini = stripos($style,$start);          // look for #
        if ($ini === false) return "";         // not found, return default color of black
        $ini += strlen($start);                // get 1 byte past start string
        $len = stripos($style,$end,$ini) - $ini; // grab substr between start and end positions
        return substr($style,$ini,$len);        // return the RGB color without # sign
	}
	function findBoldText($node) {
		$pos = stripos($node, "<b>");          // ie: looking for bolded text
		if ($pos === false) {                  //                        12345678911111
			return false;                        //                                 01234
		}
		return true;                           // found <b>
	}
?>
