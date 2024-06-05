<?php  
class ComboboxHelper extends AppHelper { 
    var $helpers = array('Html','Ajax','Javascript'); 
     
    /** 
     * Creates Combobox form input field. 
     *  
     * This is a drop-in replacement for $ajax->autoComplete(). 
     * The button image size is 'height' => 16, 'width' => 20. 
     * 
     * @param string $fieldId Same as Ajax->autoComplete() 
     * @param string $url Same as Ajax->autoComplete() 
     * @param array $options Same as Ajax->autoComplete(), except 'var' is not available. 
     *         'comboboxTitle' - Optional title for the arrow button. 
     *         'comboboxImage' - Optional arrow button image filename. default = 'combobox_arrow.gif' 
     * @return string HTML code 
     */ 
    function create($fieldId, $url, $options) { 
        static $needs_javascript = true; 
         
        if ($needs_javascript) { 
            $this->Javascript->codeBlock('function comboboxButton(ac){ac.changed = false; ac.element.focus(); ac.hasFocus = true; var temp = ac.element.value; ac.element.value = ""; ac.getUpdatedChoices(); ac.element.value = temp; ac.tokenBounds = [-1, 0];}',
                array('inline' => false)); 
            $needs_javascript = false; 
        } 
         
        $options['var'] = Inflector::camelize(str_replace(".", "_", $fieldId)) . 'Combobox'; 
         
        $onclick = 'comboboxButton(' . $options['var'] . '); return false;'; 
         
        $title = null; 
        if (isset($options['comboboxTitle'])) { 
            $title = $options['comboboxTitle']; 
            unset($options['comboboxTitle']); 
        } 
         
        $img = 'combobox_arrow.gif'; 
        if (isset($options['comboboxImage'])) { 
            $img = $options['comboboxImage']; 
            unset($options['comboboxImage']); 
        } 
         
        $ac = $this->Ajax->autoComplete($fieldId, $url, $options); 
         
        $arrow = '/>' . $this->Html->image($img, compact('onclick','title')); 
         
        // Have to do this because can't use 'after' option of $form->text() 
        // because $ajax->autoComplete() passes 'after' option to Scriptaculous. 
        return $this->output(str_replace('/>', $arrow, $ac)); 
    } 
} 
?>