<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Michael Rolli <michael@rollis.ch>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of'hidden
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');
set_include_path(t3lib_extMgm::extPath('mr_astp') . '/library' . PATH_SEPARATOR . get_include_path());
require_once('Zend/Loader.php');

/**
 * Plugin 'Data Stores' for the 'mr_astp' extension.
 *
 * @author	Michael Rolli <michael@rollis.ch>
 * @package	TYPO3
 * @subpackage	tx_mrastp
 */
class tx_mrastp_pi3 extends tslib_pibase {
	var $prefixId      = 'tx_mrastp_pi3';		// Same as class name
	var $scriptRelPath = 'pi3/class.tx_mrastp_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'mr_astp';	// The extension key.
	var $pi_checkCHash = true;
	var $feuser_id = false;
	var $_logger = null;

	var $config = array();
	var $pageArray = array(); // Is initialized with an array of the pages in the pid-list
	var $languages = array(0 => 'de', 1 => 'fr');

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
	    
        $this->init($conf);

	    $pi_getVars = t3lib_div::_GET('tx_mrastp_pi3');
	    if (isset($pi_getVars['dataStore']) && $pi_getVars['dataStore'] != $this->config['store']) {
           echo $pi_getVars['dataStore'] . ' vs ' . $this->config['store'];
	       return '';
	    }
        if(isset($pi_getVars['action'])) {
            $action = $pi_getVars['action'];
        } else {
            $action = 'show';
        }
        $this->currentAction = $action;

        try {
		    switch(strtoupper($action)) {
		        case 'SHOW':
		            $content .= $this->displayStore();
		            break;
    			case 'EDIT':
    			    if(isset($pi_getVars['item']) && isset($pi_getVars['dataStore'])) {
    			        $content .= $this->editItem((int) $pi_getVars['item']);
    			    } else {
    			        throw new Exception('No item id or dataStore provided.');
    			    }
    			    break;
    			case 'DELETE':
    			    if(isset($pi_getVars['dataStore'])) {
    			        $content .= $this->deleteItem((int) $pi_getVars['item']);
    			    } else {
                        throw new Exception('No dataStore provided.');
                    }
    			    break;
    			case 'ADD':
    			    if(isset($pi_getVars['dataStore'])) {
    			        $content .= $this->addItem();
    			    } else {
                        throw new Exception('No dataStore provided.');
                    }
    			    break;
    			case 'EXPORT':
                    if(isset($pi_getVars['dataStore'])) {
    			        $content .= $this->exportStore();
                    } else {
                        throw new Exception('No dataStore provided.');
                    }
    			    break;
		        default:
		            throw new Exception('Unknown action ' . $action);
    		}
        } catch (Exception $e) {
            $this->_logger->crit('feuser (' . $this->feuser_id . ') ' . $e->getMessage() . ":\n"  . $e->getTraceAsString() . "\n\n");
            $content = '<div class="box">Ein Systemfehler ist aufgetreten. Entsprechende Daten wurden für den Systemadministrator aufgezeichnet. Versuchen Sie es später erneut</div>';
        }

		/*$content='
			<strong>This is a few paragraphs:</strong><br />
			<p>This is line 1</p>
			<p>This is line 2</p>

			<h3>This is a form:</h3>
			<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).'" method="POST">
				<input type="hidden" name="no_cache" value="1">
				<input type="text" name="'.$this->prefixId.'[input_field]" value="'.htmlspecialchars($this->piVars['input_field']).'">
				<input type="submit" name="'.$this->prefixId.'[submit_button]" value="'.htmlspecialchars($this->pi_getLL('submit_button_label')).'">
			</form>
			<br />
			<p>You can click here to '.$this->pi_linkToPage('get to this page again',$GLOBALS['TSFE']->id).'</p>
		';*/

		return $this->pi_wrapInBaseClass($content);
	}

	private function init($conf) 
	{
	    global $TSFE;
	    Zend_Loader::loadClass('Zend_Exception');
	    // first we need a logger
	    Zend_Loader::loadClass('Zend_Log');
	    $this->_logger = new Zend_Log();
	    $loggerConf = t3lib_extMgm::extPath('mr_astp') . '/logger.conf.php';
	    // fetch logger conf from external file if readable
	    if (is_readable($loggerConf)) {
	        include_once $loggerConf;
	    } else {
	        Zend_Loader::loadClass('Zend_Log_Writer_Null');
	        $this->_logger->addWriter(new Zend_Log_Writer_Null());
	    }

        $this->config = $conf;

        $this->pi_USER_INT_obj = 1;
        $this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
	    $this->config['sys_language_content'] = intval($TSFE->config['config']['sys_language_uid']) ? intval($TSFE->config['config']['sys_language_uid']) : 0;
	    $this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin

		$this->config['store'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'store_name', 'sDEF');
		$this->config['fields'] = $this->splitFields($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'fields', 'sDEF'));

		$this->basePath = str_replace('typo3conf/ext/' . $this->extKey . '/', '', t3lib_extMgm::extPath($this->extKey));

        if ($TSFE->loginUser) {
            $this->feuser = $TSFE->fe_user->user;
        }
	}
	
	protected function displayStore()
	{
	    $this->loadStore();
	    $content = '<div class="box"><table class="contenttable contenttable-1">';
	    for ($i=0; $i < count($this->items); $i++) {
	        $class = ($i%2) ? 'tr-even' : 'tr-odd';
            $item = $this->items[$i];	        
            if ($i == 0) {
                $content.= '<tr class="tr-0">';
                foreach($item as $header) {
                    $content.= '<th>' . $header . '</th>';    
                }
                $content.= '</tr>';
            } else {
                $content.= '<tr class="' . $class . '">';

    	        foreach($item as $value) {
    	            $content.= '<td>' . $value . '</td>';
    	        }
    	        $content.='<td style="width: 20px; text-align: center">' . $this->createLink('<img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/edit2.gif" title="' . $this->pi_getLL('editItem') . '" />', $GLOBALS['TSFE']->id, array('action' => 'edit', 'dataStore' => $this->config['store'], 'item' => $i)) . '</td>';
    	        $content.='<td style="width: 20px; text-align: center">' . $this->createLink('<img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/delete_record.gif" title="' . $this->pi_getLL('deleteItem') . '" />', $GLOBALS['TSFE']->id, array('action' => 'delete', 'dataStore' => $this->config['store'], 'item' => $i)) . '</td>';  
    	        $content.= '</tr>';
            }
	    }
	    $content.= '</table></div>';
	    $content.= '<p>' . $this->createLink('<img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/new_record.gif" title="' . $this->pi_getLL('newItem') . '" />', $GLOBALS['TSFE']->id, array('action' => 'add', 'dataStore' => $this->config['store'])) . '</p>';
	    $content.= '<p>' . $this->createLink('<img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/icon_xls.gif" title="' . $this->pi_getLL('export_xls') . '" />', $GLOBALS['TSFE']->id, array('action' => 'export',  'dataStore' => $this->config['store'],'format' => 'xls')) . '</p>';
	    return $content;
	}
	
    protected function addItem()
	{
        $this->loadStore();
	    $content.= '<div class="box">';
	    Zend_Loader::loadClass('Mrastp_Form_StoreItem');
	    $form = new Mrastp_Form_StoreItem($this);
        $form->setAction($this->createUrl(array('action' => 'add', 'dataStore' => $this->config['store'])));
	    if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
	        $this->items[] = $form->getValues();
	        $this->saveStore();
	        header('Location: /' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
	        exit;
	    }
        $content.= $form->render();
	    $content.= '</div>';
	    return $content;
	}
	
	protected function editItem($id)
	{
        $this->loadStore();
	    if (!isset($this->items[$id]) && !is_array($this->item[$id])) {
	        throw new Exception('Invalid item id ' . $id);
	    }
	    $content.= '<div class="box">';
        Zend_Loader::loadClass('Mrastp_Form_StoreItem');
        $form = new Mrastp_Form_StoreItem($this);
        $form->setDefaults($this->items[$id]);
        $form->setAction($this->createUrl(array('action' => $this->currentAction, 'dataStore' => $this->config['store'], 'item' => $id)));
        if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
            $this->items[$id] = $form->getValues();
            $this->saveStore();
            header('Location: /' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
            exit;
        }
        $content.= $form->render();
        $content.= '</div>';
        return $content;
	}
	
	protected function deleteItem($id)
	{
        $this->loadStore();
	    unset($this->items[$id]);
	    $this->saveStore();
        header('Location: /' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
        exit;
	}
	
	protected function splitFields($fieldString)
	{
	    $fields = explode(',', $fieldString);
        $retval = array();
        foreach ($fields as $field) {
            $retval[] = array(strtolower(str_replace(' ', '_', $field)), $field);
        }
	    return $retval;
	}
	
	protected function loadStore()
	{
	    $filename = $this->basePath . 'fileadmin/datastores/' . $this->config['store'] . '.csv';
	    if (!file_exists($filename)) {
	        touch($filename);
            $rubriken = array();
            foreach ($this->config[$fields] as $item) {
                $rubriken[] = $item[1];
            }
	        file_put_contents($filename, implode(';', $rubriken));
	    }
	    $fp = fopen($filename, 'rb');
        $changed = false;
        $rubrik = true;
	    // @todo: strip escaping!
	    while ($row = fgetcsv($fp, null, ';')) {
	        $fields = $this->config['fields'];
            $item = array();
            if ($rubrik) {
                if (count($this->config['fields']) > count($row)) {
                    // neue Rubriken
                    $changed = true;
                }
                foreach ($fields as $field) {
                    $item[$field[0]] = $field[1];
                }
                $rubrik = false;
            } else {
	            for ($i=0; $i < count($fields); $i++) {
	                $item[$fields[$i][0]] = str_replace(array('\"', "\'"), array('"', "'"), $row[$i]);
	            }
            }
	        $this->items[] = $item;
	        
	    }
        fclose($fp);
        if ($changed) { 
            foreach ($this->config['fields'] as $field) {
                $this->items[0][$field] = $field;
            }
            $this->saveStore();
        }
	}
	
	protected function saveStore()
	{
	    $filename = $this->basePath . 'fileadmin/datastores/' . $this->config['store'] . '.csv';
	    $fp = fopen($filename, 'wb');
	    foreach ($this->items as $item) {
	        fputcsv($fp, $item, ';', '"');
	    }
	    fclose($fp);
	}
	
	protected function exportStore()
	{
        $this->loadStore();
	    $pi_getVars = t3lib_div::_GET('tx_mrastp_pi3');
	    $format = (isset($pi_getVars['format'])) ? $pi_getVars['format'] : 'xls';
	    
	    switch ($format) {
	        case 'xls':
	            header('Content-Type: application/vnd.ms-excel');
	            header('Content-Disposition: attachment; filename="' . $this->config['store']. '.xls"');
	            echo $this->array2xls($this->items);
	            exit;
	            break;
	        default:
	           throw new Exception('Unknown export format: ' . $format);
	    }
	}
	
	protected  function array2xls($inputArray)
	{
	    $i=0;
	    $xls = <<<EOD
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html;charset=utf-8" />
<style id="Classeur1_16681_Styles">
</style>

</head>
<body>

<div id="Classeur1_16681" align=center x:publishsource="Excel">

<table x:str border=1 cellpadding=0 cellspacing=0 style="border-collapse: collapse">
EOD;
	    foreach ($inputArray as $item) {
	        $i++;
	        $line = '';
	        foreach ($item as $field => $value) {
	            if ($i == 1) {
	                $line.= '<td class=xl2216681 nowrap><b>' . $value . '</b></td>';
	            } else {
	                $line.= '<td class=xl2216681 nowrap>' . $value . '</td>';
	            }
	        }
	        $xls.= '<tr>' . $line . '</tr>';
	    }
	    $xls.= <<<EOD
</div>
</body>
</html>
EOD;
	    return $xls;
	}

    public function getFeUserLang()
    {
        return $this->languages[$this->config['sys_language_content']];
    }
    
    public function createLink($name, $pid, $params = array())
    {
        return $this->pi_linkTP_keepPIvars($name, $params, 1, 1, $pid);
    }
    
    public function createUrl($params = array())
    {
        return $this->pi_linkTP_keepPIvars_url($params, 1, 1, 0);
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi3/class.tx_mrastp_pi3.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi3/class.tx_mrastp_pi3.php']);
}

?>
