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
set_include_path(t3lib_extMgm::extPath('mr_astp') . PATH_SEPARATOR . t3lib_extMgm::extPath('mr_astp') . 'pi2/classes' . PATH_SEPARATOR . get_include_path());
require_once('Zend/Loader.php');

/**
 * Plugin 'Job offers' for the 'mr_astp' extension.
 *
 * @author	Michael Rolli <michael@rollis.ch>
 * @package	TYPO3
 * @subpackage	tx_mrastp
 */
class tx_mrastp_pi2 extends tslib_pibase {
	var $prefixId      = 'tx_mrastp_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_mrastp_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'mr_astp';	// The extension key.
	var $pi_checkCHash = true;

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

        // get codes and decide which function is used to process the content
        $codes = t3lib_div::trimExplode(',', $this->conf['code'] ? $this->conf['code'] : 'NONE', 1);
        if (!count($codes)) { // no code at all
            $codes = array('NONE');
		    $noCode = true;
        }

		foreach($codes as $code) {
		    $code = $this->theCode = (string)strtoupper(trim($code));
		    switch($code) {
		        case 'FORWARD':
		            $content .= $this->displayForwardNotices();
		            break;
    			case 'CREATE':
    			    $content .= $this->displayRegistrationForm();
    			    break;
    			case 'EDIT':
    			    $content .= $this->displayEditForm();
    			    break;
    			case 'CONFIRM':
    			    $content .= $this->handleConfirmation();
    			    break;
		        default:
		            $content .= $this->pi_getLL('no_view_selected');
    		}
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
        $this->conf = $conf;

        $this->pi_USER_INT_obj = 1;
        $this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
	    $this->conf['sys_language_content'] = intval($TSFE->config['config']['sys_language_uid']) ? intval($TSFE->config['config']['sys_language_uid']) : 0;
	    $this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin            
            
        // set the pid's and the title language overlay
        $this->conf['pidRecord'] = t3lib_div::makeInstance('t3lib_pageSelect');
        $this->conf['pidRecord']->init(0);
        $this->conf['pidRecord']->sys_language_uid = $this->conf['sys_language_content'];
        $this->conf['thePid'] = intval($this->conf['pid']) ? strval(intval($this->conf['pid'])) : $TSFE->id;
        $row = $this->conf['pidRecord']->getPage($this->conf['thePid']);
        $this->conf['thePidTitle'] = trim($this->conf['pidTitleOverride']) ? trim($this->conf['pidTitleOverride']) : $row['title'];
        $this->conf['registerPID'] = intval($this->conf['registerPID']) ? strval(intval($this->conf['registerPID'])) : $TSFE->id;
        $this->conf['editPID'] = intval($this->conf['editPID']) ? strval(intval($this->conf['editPID'])) : $TSFE->id;
        $this->conf['confirmPID'] = intval($this->conf['confirmPID']) ? strval(intval($this->conf['confirmPID'])) : $this->registerPID;
        $this->conf['loginPID'] = intval($this->conf['loginPID']) ? strval(intval($this->conf['loginPID'])) : $TSFE->id;
            
		// "CODE" decides what is rendered: codes can be set by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->conf['code'] = $code ? $code : $this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);
		
		//t3lib_div::debug($this->conf);
	}

	public function displayForwardNotices()
	{
	    global $TSFE;
	    $content = '';
	    $loggedIn = (bool) $TSFE->loginUser;
	    if (!$loggedIn) {
	        $content .= '<p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('click_here_to_register'), null, null, 1, $this->conf['registerPID']) . '</p>';
	    } else {
	        $content .= '<p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('click_here_to_edit'), null, null, 1, $this->conf['editPID']) . '</p>';
	    }
	    return $content;
	}
	
	public function displayRegistrationForm($errors = array())
	{
	    Zend_Loader::loadClass('Form_Registration');
	    $registrationForm = new Mrastp_Form_Registration($this);
	    if (isset($_POST['submitButton']) && $registrationForm->isValid($_POST)) {
	        // do something
	        return 'Danke für die Anmeldung';
	        //$this->processRegistration($registrationForm->getValues());
	    }
        $content.= $registrationForm->render();
	    return $content;
	}
	
	public function processRegistration()
	{
	    global $TYPO3_DB;
	    $errors = array();
	    $person_row = array();
	    $feuser_row = array();
	    
	    $person = t3lib_div::_POST('person');
	    $feuser = t3lib_div::_POST('feuser');

	    $person_row['pid'] = $this->conf['astpdbPID'];
	    $person_row['tstamp'] = time();
	    $person_row['crdate'] = time();
	    $person_row['cruser_id'] = 0;
	    $person_row['hidden'] = 1;
	    $person_row['salutation_id'] = (int) $person['salutation_id'];
	    $person_row['firstname'] = trim($person['firstname']);
	    $person_row['name'] = trim($person['name']);
	    $person_row['street'] = $person['street'];
	    $person_row['compl'] = $person['compl'];
	    $person_row['zip'] = (int) $person['zip'];
	    $person_row['city'] = $person['city'];
	    $person_row['canton_id'] = $person['canton_id'];
	    $person_row['country_id'] = $person['country_id'];
	    $person_row['phone'] = $person['phone'];
	    $person_row['mobile'] = $person['mobile'];
	    $person_row['fax'] = $person['fax'];
	    $person_row['email'] = $person['email'];
	    $person_row['language_id'] = $person['language_id'];
	    $person_row['section_id'] = $person['section_id'];
	    $person_row['state'] = $person['state'];

	    $feuser_row['pid'] = $this->conf['feuserPID'];
	    $feuser_row['tstamp'] = $feuser_row['crdate'] = time();
	    $feuser_row['username'] = trim($feuser['username']);
	    $feuser_row['password'] = $feuser['password'];
	    $feuser_row['usergroup'] = $this->conf['usergroup_before_accept'];
	    $feuser_row['disable'] = 1;
	    $feuser_row['name'] = $person_row['firstname'] . ' ' . $person_row['name'];
	    $feuser_row['email'] = $person_row['email'];
	    $feuser_row['city'] = $person_row['city'];
	    
	    t3lib_div::debug($person_row);
	    t3lib_div::debug($feuser_row);
	    if (!$person_row['salutation_id'] || !$person_row['firstname'] || !$person_row['name'] || !$person_row['street'] || !preg_match('/[0-9]{3,4}/', $person_row['zip']) || !$person_row['city']
	        || !$person_row['canton_id'] || !$person_row['country_id'] || !$person_row['phone'] || !$feuser['username'] || !$feuser['password'] || !$feuser['password-repeat']) {
	        $errors[] = $this->pi_getLL('form_error');
        }
        if (!$this->_isEmailAddress($person_row['email'])) {
            $errors[] = $this->pi_getLL('incorrect_email');
        }
        if ($this->_isDuplicateMember($person_row)) {
            $errors[] = $this->pi_getLL('member_exists');
        }
        t3lib_div::debug($errors);
	    if (count($errors) > 0) {
	        return $this->displayRegistrationForm($errors);
	    }
	    return 'abspitzen!';
	}

	public function displayEditForm()
	{
	    return 'Edit Form';
	}
	
	public function handleConfirmation()
	{
	    return 'Confirmation message';
	}
	
	protected function _getFormValue($fieldName)
	{
	   $fieldName = str_replace(']', '', $fieldName);
	   $fieldParts = explode('[', $fieldName);
	   $numParts = count($fieldParts);
	   $value = $_POST;
	   while ($key = array_shift($fieldParts)) {
	       if (isset($value[$key])) {
	           $value = $value[$key];
	       } else {
	           return '';
	       }
	   }
	   return $value;
	}
	
	public function getSelectSalutation()
	{
	    $options = array($this->pi_getLL('choose_one'));
	    
	    global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery('uid, label_' . $this->getFeUserLang() . ' as label', 'tx_mrastp_salutation','1=1 ' . $this->cObj->enableFields('tx_mrastp_salutation'));

        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $options[$row['uid']] = $row['label'];
        }
        return $options;
	}

    public function getSelectCanton()
    {
        $options = array($this->pi_getLL('choose_one'));
        
        global $TYPO3_DB;
        $label_suffix = $this->getFeUserLang();
        $result = $TYPO3_DB->exec_SELECTquery('uid, abbrevation, CONCAT(abbrevation, " - ",  label_' . $label_suffix . ') as label', 'tx_mrastp_canton','1=1 ' . $this->cObj->enableFields('tx_mrastp_canton'), 'abbrevation ASC');

        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $options[$row['uid']] = $row['label'];
        }
        return $options;
    }

    public function getSelectCountry()
    {
        $options = array($this->pi_getLL('choose_one'));
        
        global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery('uid, cn_short_' . $this->getFeUserLang() . ' as label', 'tx_mrastp_country','1=1 ' . $this->cObj->enableFields('tx_mrastp_country'));

        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $options[$row['uid']] = $row['label'];
        }
        return $options;
    }

    public function getSelectLanguage()
    {
        $options = array($this->pi_getLL('choose_one'));
        
        global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery('uid, label_' . $this->getFeUserLang() . ' as label', 'tx_mrastp_language','1=1 ' . $this->cObj->enableFields('tx_mrastp_language'));

        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $options[$row['uid']] = $row['label'];
        }
        return $options;
    }
    
    public function getSelectSection()
    {
        $options = array($this->pi_getLL('choose_one'));
        
        global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery('uid, label_' . $this->getFeUserLang() . ' as label', 'tx_mrastp_section','1=1 ' . $this->cObj->enableFields('tx_mrastp_section'));

        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $options[$row['uid']] = $row['label'];
        }
        return $options;
    }
    
    public function getSelectStatus()
    {
        $options = array($this->pi_getLL('choose_one'));
        
        global $TYPO3_DB;
        $result = $TYPO3_DB->exec_SELECTquery('uid, label_' . $this->getFeUserLang() . ' as label', 'tx_mrastp_state','1=1 ' . $this->cObj->enableFields('tx_mrastp_state'));

        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            if ($row['uid'] != 3 && $row['uid'] != 4) {
                $options[$row['uid']] = $row['label'];
            }
        }
        return $options;
    }

    protected function _isDuplicateMember($member)
    {
        global $TYPO3_DB;
        $TYPO3_DB->debugOutput = 1;
        $where = "(firstname='" . $member['firstname'] . "' AND name='" . $member['name'] . "' AND city='" . $member['city'] . "')";
        if (!empty($member['email'])) {
            $where.= " OR email='" . $member['email'] . "'";
        }
        $result = $TYPO3_DB->exec_SELECTquery('uid', 'tx_mrastp_person', $where);
        $numRows = mysql_num_rows($result);
        $retval = ($numRows > 0) ? true : false;
        return $retval;
    }
    
    protected function _isEmailAddress($email)
    {
        require_once 'Zend/Validate/EmailAddress.php';
        $validator = new Zend_Validate_EmailAddress();
        return $validator->isValid($email);
    }
    
    public function getFeUserLang()
    {
        return $this->languages[$this->conf['sys_language_content']];
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi2/class.tx_mrastp_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi2/class.tx_mrastp_pi2.php']);
}

?>
