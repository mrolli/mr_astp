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
	var $feuser_id = 0;
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

        // get codes and decide which function is used to process the content
        $codes = t3lib_div::trimExplode(',', $this->conf['code'] ? $this->conf['code'] : 'NONE', 1);
        if (!count($codes)) { // no code at all
            $codes = array('NONE');
		    $noCode = true;
        }

        try {
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

        $this->conf = $conf;
        // db adapter for Zend_Db_Table-Classes
        Zend_Loader::loadClass('Zend_Db');
        Zend_Loader::loadClass('Zend_Db_Table_Abstract');
        $db = Zend_Db::factory('Mysqli', array('host'     => 'localhost',
                                               'username' => 'typo3admin',
                                               'password' => 't3pass',
                                               'dbname'   => 't3_astp'));
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        $db->query('SET NAMES utf8');

        // default mail transport for all mails generated
        Zend_Loader::loadClass('Zend_Mail');
        Zend_Loader::loadClass('Zend_Mail_Transport_Sendmail');
        $tr = new Zend_Mail_Transport_Sendmail('-fbounces@astp.ch');
        Zend_Mail::setDefaultTransport($tr);

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
        $this->conf['feuserPID'] = intval($this->conf['feuserPID']) ? strval(intval($this->conf['feuserPID'])) : $TSFE->id;
        $row = $this->conf['pidRecord']->getPage($this->conf['feuserPID']);
        $this->conf['thePidTitle'] = trim($this->conf['pidTitleOverride']) ? trim($this->conf['pidTitleOverride']) : $row['title'];
        $this->conf['registerPID'] = intval($this->conf['registerPID']) ? strval(intval($this->conf['registerPID'])) : $TSFE->id;
        $this->conf['editPID'] = intval($this->conf['editPID']) ? strval(intval($this->conf['editPID'])) : $TSFE->id;
        $this->conf['confirmPID'] = intval($this->conf['confirmPID']) ? strval(intval($this->conf['confirmPID'])) : $this->registerPID;
        $this->conf['loginPID'] = intval($this->conf['loginPID']) ? strval(intval($this->conf['loginPID'])) : $TSFE->id;
            
		// "CODE" decides what is rendered: codes can be set by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->conf['code'] = $code ? $code : $this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);
		
        $this->conf['labels'] = array();
        $this->conf['labels']['salutation'] = 'label_' . $this->languages[$this->conf['sys_language_content']];
        $this->conf['labels']['canton_long'] = 'label_' . $this->languages[$this->conf['sys_language_content']];
        $this->conf['labels']['canton_short'] = 'abbrevation';
        $this->conf['labels']['country_long'] = 'cn_short_' . $this->languages[$this->conf['sys_language_content']];
        $this->conf['labels']['country_short'] = 'cn_iso_2 ';
        $this->conf['labels']['language_long'] = 'label_' . $this->languages[$this->conf['sys_language_content']];
        $this->conf['labels']['section_long'] = 'label_' . $this->languages[$this->conf['sys_language_content']];
        $this->conf['labels']['status_long'] = 'label_' . $this->languages[$this->conf['sys_language_content']];
		//t3lib_div::debug($this->conf);
		
        // current loggedin user, 0 if no user logged in
        if ($TSFE->loginUser) {
            $this->feuser_id = $TSFE->fe_user->user['uid'];
        }
	}

	public function displayForwardNotices()
	{
	    global $TSFE;
	    $content = '';
	    $loggedIn = (bool) $TSFE->loginUser;
	    if (!$loggedIn) {
	        $content .= '<p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('click_here_to_register'), null, null, 1, $this->conf['registerPID']) . '</p>';
	        $content .= '<p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('click_here_to_getusername'), null, null, 1, $this->conf['getusernamePID']) . '</p>';
	    } else {
	        $content .= '<p>' . $this->pi_linkTP_keepPIvars($this->pi_getLL('click_here_to_edit'), null, null, 1, $this->conf['editPID']) . '</p>';
	    }
	    return $content;
	}
	
	public function displayRegistrationForm($errors = array())
	{
	    Zend_Loader::loadClass('Mrastp_Form_Registration');
	    $registrationForm = new Mrastp_Form_Registration($this);
	    if (isset($_POST['submitButton']) && $registrationForm->isValid($_POST)) {
	        $data = $registrationForm->getValues();
	        $this->_logger->debug("Valid member registration form: \n" . print_r($data) . "\n");
            $this->processRegistration($data);
            $content.= '<div class="box">';
            $content.= '<p>' . $this->decorateLabel('v_dear', $data) . '</p>';
            $content.= '<p>' . $this->decorateLabel('v_please_confirm', $data) . '</p>';
            $content.= '<p>' . $this->decorateLabel('v_your_account_was_created', $data) . '</p>';
            $content.= '<p>' . $this->decorateLabel('v_follow_instructions_review1', $data) . '</p>';
            $content.= '<p>' . $this->decorateLabel('v_follow_instructions_review2', $data) . '</p>';
            $content.= '<p>' . $this->decorateLabel('kind_regards', $data) . '<br />' . $this->conf['contactName'] . '</p>';
            $content.= '</div>';
            return $content;
	    }
        $content.= $registrationForm->render();
	    return $content;
	}
	
	public function processRegistration($data)
	{
	    global $TYPO3_DB;
	    $person_row = array();
	    $feuser_row = array();

	    $person_row['tstamp'] = time();
	    $person_row['crdate'] = time();
	    $person_row['hidden'] = 1;
	    $person_row['salutation_id'] = (int) $data['salutation_id'];
	    $person_row['firstname'] = $data['firstname'];
	    $person_row['name'] = $data['name'];
	    $person_row['street'] = $data['street'];
	    $person_row['compl'] = $data['compl'];
	    $person_row['zip'] = (int) $data['zip'];
	    $person_row['city'] = $data['city'];
	    $person_row['canton_id'] = $data['canton_id'];
	    $person_row['country_id'] = $data['country_id'];
	    $person_row['phone'] = $data['phone'];
	    $person_row['mobile'] = $data['mobile'];
	    $person_row['fax'] = $data['fax'];
	    $person_row['email'] = $data['email'];
	    $person_row['language_id'] = $data['language_id'];
	    $person_row['section_id'] = $data['section_id'];
	    $person_row['status'] = $data['status'];
	    $person_row['entry_date'] = time();

	    $feuser_row['tstamp'] = $feuser_row['crdate'] = time();
	    $feuser_row['username'] = trim($data['username']);
	    $feuser_row['password'] = $data['password'];
	    $feuser_row['usergroup'] = $this->conf['userGroupUponRegistration'];
	    $feuser_row['disable'] = 1;
	    $feuser_row['name'] = $person_row['firstname'] . ' ' . $person_row['name'];
	    $feuser_row['email'] = $person_row['email'];
	    $feuser_row['city'] = $person_row['city'];

        $newFieldList = 'tstamp,username,password,usergroup,disable,name,email,city';	    
        $this->cObj->DBgetInsert('fe_users', $this->conf['feuserPID'], $feuser_row, $newFieldList, true);
        $feuserUid = $TYPO3_DB->sql_insert_id();
        $this->_logger->debug("New fe_user (" . $feuserUid . ") created: \n" . print_r($feuser_row) . "\n");

        $person_row['feuser_id'] = $feuserUid;

	    $newFieldList = 'pid,tstamp,crdate,cruser_id,hidden,salutation_id,firstname,name,street,compl,zip,city,canton_id,country_id,phone,mobile,fax,email,language_id,section_id,status,entry_date,feuser_id';
        $this->cObj->DBgetInsert('tx_mrastp_person', $this->conf['astpdbPID'], $person_row, $newFieldList, true);
        $personUid = $TYPO3_DB->sql_insert_id();
        $this->_logger->debug("New person (" . $personUid . ") created: \n" . print_r($person_row) . "\n");
        
        //setup commands for this user
        $commands = $this->_setupCommands($personUid);
        $this->_logger->debug("Commands for new member: \n" . print_r($commands) . "\n");
        // Mailings
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_initiated_review1', $data) . "\r\n";        
        $body.= '<a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $commands['CONFIRM']['hash'] . '">' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $commands['CONFIRM']['hash'] . "</a>\r\n";
        $body.= $this->decorateLabel('copy_paste_link', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_initiated_review2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_initiated_message2', $data) . "\r\n";
        $body.= '<a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $commands['DELETE']['hash'] . '">' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $commands['DELETE']['hash'] . "</a>\r\n";
        $body.= $this->decorateLabel('copy_paste_link', $data) . "\r\n";
        $body.= $this->decorateLabel('excuse_us', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_initiated_message3', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_ini', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        Zend_Loader::loadClass('Zend_Mail');
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject($this->decorateLabel('v_please_confirm', $data)));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        try {
            $cust_email->send();
            $this->_logger->info("Customer confirmation mail sent to " . $data['email']);
            $this->_logger->debug(print_r($cust_email) . "\n");
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('Customer confirmation mail not sent to ' . $data['email'] . ' (person ' . $personUid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        // an astp
        $body = $this->decorateLabel('v_registration_initiated', $data) . "\r\n\r\n";
        $body.= $this->pi_getLL('name') . ' ' . $data['firstname'] . ' ' . $data['name'] . "\r\n";
        $body.= $this->pi_getLL('email') . ' ' . $data['email'] . "\r\n";
        $body.= $this->pi_getLL('username') . ' ' . $data['username'] . "\r\n\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_initiated', $data)));
        $astp_email->setBodyText($body);
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
	    try {
            $astp_email->send();
            $this->_logger->info('astp begin registration mail sent to ' . $this->conf['contactEmail']);
            $this->_logger->debug(print_r($astp_email) . "\n");
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('astp begin registration mail not sent to ' . $data['contactEmail'] . ' (person ' . $personUid . "\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        $this->_logger->info('New member saved to db: (' . $personUid . ') ' . $data['firstname'] . ' ' . $data['name'] . ', ' . $data['city'] . ', feuser_id=' . $person_row['feuser_id']);
	}

	public function displayEditForm()
	{
	    if (!$this->feuser_id) {
	        return '<div class="box">ERROR<br />No frontend user logged in. Action cancelled';
	    }
	    $pi2_getVars = t3lib_div::_GET('tx_mrastp_pi2');
        if(isset($pi2_getVars['action'])) {
            $action = $pi2_getVars['action'];
        } else {
            $action = 'show';
        }
        if ($action == 'editWorkaddress' && !isset($pi2_getVars['uid'])) {
            $action = 'show';
        }
	   if ($action == 'deleteWorkaddress' && !isset($pi2_getVars['uid'])) {
            $action = 'show';
        }
        switch ($action) {
            case 'editPersonal':
                $this->_logger->debug('feuser (' . $this->feuser_id . ') entering editPersonal.');
                $content = $this->editPersonal();
                break;
            case 'editAccount':
                $this->_logger->debug('feuser (' . $this->feuser_id . ') entering editAccount.');
                $content = $this->editAccount();
                break;
            case 'newWorkaddress':
                $this->_logger->debug('feuser (' . $this->feuser_id . ') entering newWorkaddress.');
                $content = $this->newWorkaddress();
                break;
            case 'editWorkaddress':
                $this->_logger->debug('feuser (' . $this->feuser_id . ') entering editWorkaddress (' . $pi2_getVars['uid'] . ').');
                $content = $this->editWorkaddress($pi2_getVars['uid']);
                break;
            case 'deleteWorkaddress':
                $this->_logger->debug('feuser (' . $this->feuser_id . ') entering deleteWorkaddress (' . $pi2_getVars['uid'] . ').');
                $content = $this->deleteWorkaddress($pi2_getVars['uid']);
                break;
            default:
                $this->_logger->debug('feuser (' . $this->feuser_id . ') entering showAccountDetails.');
                $content = $this->showAccountDetails();
                break;
        }
        return $content;
	}

	public function showAccountDetails()
	{
	    $label['salutation'] = $this->conf['labels']['salutation'];
	    $label['canton'] = $this->conf['labels']['canton_long'];
	    $label['country'] = $this->conf['labels']['country_long'];
	    $label['language'] = $this->conf['labels']['language_long'];
	    $label['section'] = $this->conf['labels']['section_long'];
	    $label['status'] = $this->conf['labels']['status_long'];

	    $content = '';
	    Zend_Loader::loadClass('Mrastp_Db_Table_Person');
	    Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
	    Zend_Loader::loadClass('Mrastp_Db_Table_Workaddress');
        Zend_Loader::loadClass('Mrastp_Form_Person');
        $personTable = new Mrastp_Db_Table_Person();
        $person = $personTable->fetchRow(array('feuser_id = ?' => $this->feuser_id));
        if (!$person) {
            throw new Zend_Exception('feuser (' . $this->feuser_uid . ') has no astp_person!');
        }
        $feuserTable = new Mrastp_Db_Table_Feuser();
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $this->feuser_id));
        $workaddressTable = new Mrastp_Db_Table_Workaddress();
        $workaddresses = $workaddressTable->fetchAll(array('parentuid = ?' => $person->uid, 'deleted = ?' => 0));
        $this->_logger->debug('feuser (' . $feuser->uid . ') (person ' . $person->uid . ') has ' . count($workaddresses) . ' workaddress(es).');
        
        // Personendetails
        $content.= '<h2>' . $this->pi_getLL('account_overview') . '</h2>';
        $content.= '<h3>' . $this->pi_getLL('personal_data') . '</h3>';
        $content.= '<div class="box">';
        $content.= '<table cellspacing="5">';
        $content.= '<tr><td>' . $this->pi_getLL('salutation_id') . '</td><td>' . $person->findParentRow('Mrastp_Db_Table_Salutation')->$label['salutation'] . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('name') . '</td><td>' . $person->firstname . ' ' . $person->name . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('street') . '</td><td>' . $person->street . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('compl') . '</td><td>' . $person->compl . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('city') . '</td><td>' . $person->zip . ' ' . $person->city . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('canton_id') . '</td><td>' . $person->findParentRow('Mrastp_Db_Table_Canton')->$label['canton'] . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('country_id') . '</td><td>' . $person->findParentRow('Mrastp_Db_Table_Country')->$label['country'] . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('phone') . '</td><td>' . $person->phone . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('mobile') . '</td><td>' . $person->mobile . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('fax') . '</td><td>' . $person->fax . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('email') . '</td><td>' . $person->email . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('language_id') . '</td><td>' . $person->findParentRow('Mrastp_Db_Table_Language')->$label['language'] . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('section_id') . '</td><td>' . $person->findParentRow('Mrastp_Db_Table_Section')->$label['section'] . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('status') . '</td><td>' . $person->findParentRow('Mrastp_Db_Table_Status')->$label['status'] . '</td></tr>';
        $content.= '<tr><td>' . $this->pi_getLL('entry_date') . '</td><td>' . date('d.m.Y', $person->entry_date) . '</td></tr>';
        $content.= '<tr><td><a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['editPID'] . '&tx_mrastp_pi2[action]=editPersonal' . '"><img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/edit2.gif" title="' . $this->pi_getLL('change_data') . '" /></a></td></tr>';
        $content.= '</table>';
        $content.= '</div>';
        $content.= '<h3>' . $this->pi_getLL('online_account') . '</h3>';
        $content.= '<div class="box">';
        if ($feuser) {
            $content.= '<table cellspacing="5">';
            $content.= '<tr><td>' . $this->pi_getLL('username') . '</td><td>' . $feuser->username . '</td></tr>';
            $content.= '<tr><td>' . $this->pi_getLL('password') . '</td><td>********</td></tr>';
            $content.= '<tr><td><a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['editPID'] . '&tx_mrastp_pi2[action]=editAccount' . '"><img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/edit2.gif" title="' . $this->pi_getLL('change_data') . '" /></a></td></tr>';
            $content.= '</table>';
            $content.= '</div>';
        }
        $content.= '<h3>' . $this->pi_getLL('workaddresses') . '</h3>';
        $content.= '<div class="box">';
        $content.= '<table cellspacing="5">';
        if (count($workaddresses) > 0) {
            foreach ($workaddresses as $workaddress) {
                $this->_logger->debug('feuser (' . $feuser->uid . ') (person ' . $person->uid . ') owns workaddress ' . $workaddress->uid);
                $content.= '<tr><td><a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['editPID'] . '&tx_mrastp_pi2[action]=editWorkaddress&tx_mrastp_pi2[uid]=' . $workaddress->uid . '"><img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/edit2.gif" title="' . $this->pi_getLL('change_data') . '" /></a>';
                $content.= '&nbsp;&nbsp;<a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['editPID'] . '&tx_mrastp_pi2[action]=deleteWorkaddress&tx_mrastp_pi2[uid]=' . $workaddress->uid . '"><img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/delete_record.gif" title="' . $this->pi_getLL('delete_workaddress') . '" /></a></td>';
            	$content.= '<td>&nbsp;' . $workaddress->name_practice . ', ' . $workaddresses->supplement . '</td><td>' . $workaddress->address1 . ', ' . $workaddress->zip . ' ' . $workaddress->city . '</td></tr>';
            	
            }
        }
        $content.= '<tr><td colspan="2"><a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['editPID'] . '&tx_mrastp_pi2[action]=newWorkaddress' . '"><img src="' . t3lib_extMgm::extRelPath('mr_astp') . '/icons/new_record.gif" title="' . $this->pi_getLL('new_workaddress') . '" /></a></td></tr>';
        $content.= '</table>';
        $content.= '</div>';
        return $content;
	}

	public function editPersonal()
	{
	    $content = '';
        $label['salutation'] = $this->conf['labels']['salutation'];
        $label['canton'] = $this->conf['labels']['canton_long'];
        $label['country'] = $this->conf['labels']['country_long'];
        $label['language'] = $this->conf['labels']['language_long'];
        $label['section'] = $this->conf['labels']['section_long'];
        $label['status'] = $this->conf['labels']['status_long'];
	    Zend_Loader::loadClass('Mrastp_Db_Table_Person');
	    Zend_Loader::loadClass('Mrastp_Form_Person');
	    Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
	    $personTable = new Mrastp_Db_Table_Person();
	    $person = $personTable->fetchRow(array('feuser_id = ?' => $this->feuser_id));
	    if (!$person) {
            throw new Zend_Exception('feuser (' . $this->feuser_uid . ') has no astp_person!');
        }
	    $form = new Mrastp_Form_Person($this);
	    $_POST['uid'] = $person->uid;
	    if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
	        $origValues = $person->toArray();
	        $newValues = $form->getValues();
	        $changeset = array_diff_assoc($newValues, $origValues);
	        if (count($changeset) > 0) {
	            $this->_logger->debug('feuser (' . $this->feuser_id . ') (person ' . $person->uid . ") has changed personals:\n" . var_export($changeset, true));
	            $person->setFromArray($changeset);
	            $person->tstamp = time();
	            $record_id = $person->save();
	            $this->_logger->debug('feuser (' . $this->feuser_id . ') saved row tx_mrastp_person.' . (string) $record_id);
	            // updating corresponding feuser record to the person values (name, firstname, city, email)
	            $feuserTable = new Mrastp_Db_Table_Feuser();
                $feuser = $feuserTable->fetchRow(array('uid = ?' => $this->feuser_id));
                $data = array('email' => $person->email, 'name' => $person->firstname . ' ' . $person->name, 'city' => $person->city);
                $feuser->setFromArray($data);
                $feuser->tstamp = time();
                $record_id = $feuser->save();
                $this->_logger->debug('feuser (' . $this->feuser_id . ') saved row fe_users.' . (string) $record_id);

	            $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => '');
	            $this->_logger->info('feuser (' . $this->feuser_id . '), (person ' . $person->uid . ') updated his/her personal information');

	            // Mailings
                Zend_Loader::loadClass('Zend_Mail');
                $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('v_registration_updated_subject', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('v_registration_updated_message1', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('kind_regards', $data) . "\r\n";
                $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
                $cust_email = new Zend_Mail('utf-8');
                $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_updated_subject', $data))));
                $cust_email->setBodyHtml(nl2br($body));
                $cust_email->setBodyText(strip_tags($body));
                $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
                $cust_email->addTo($data['email']);
	            try {
                    $cust_email->send();
                    $this->_logger->info('feuser (' . $this->feuser_id . ') Customer mail "personals updated" sent to ' . $data['email']);
                    $this->_logger->debug(var_export($cust_email, true));
                } catch (Zend_Mail_Transport_Exception $e) {
                    $this->_logger->alert('Customer mail "personals updated" not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                }
                
                // an astp
                $body = $this->decorateLabel('v_registration_updated', $data) . "\r\n\r\n";
                foreach ($changeset as $key => $value) {
                    switch ($key) {
                        case 'salutation_id':
                            $body.= $this->pi_getLL($key) . ' ' . $person->findParentRow('Mrastp_Db_Table_Salutation')->$label['salutation'] . "\r\n";
                            break;
                        case 'canton_id':
                            $body.= $this->pi_getLL($key) . ' ' . $person->findParentRow('Mrastp_Db_Table_Canton')->$label['canton'] . "\r\n";
                            break;
                        case 'country_id':
                            $body.= $this->pi_getLL($key) . ' ' . $person->findParentRow('Mrastp_Db_Table_Country')->$label['country'] . "\r\n";
                            break;
                        case 'language_id':
                            $body.= $this->pi_getLL($key) . ' ' . $person->findParentRow('Mrastp_Db_Table_Language')->$label['language'] . "\r\n";
                            break;
                        default:
                            $body.= $this->pi_getLL($key) . ' ' . $origValues[$key] . ' -> ' . $newValues[$key] . "\r\n";
                            break;
                    }
                }
                $body.= "\r\n";
                $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
                $astp_email = new Zend_Mail('utf-8');
                $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_updated', $data))));
                $astp_email->setBodyText(strip_tags($body));
                $astp_email->setBodyHtml(nl2br($body));
                $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
                $astp_email->addTo($this->conf['contactEmail']);
	            try {
                    $astp_email->send();
                    $this->_logger->info('feuser (' . $this->feuser_id . ') astp mail "personals updated" sent to ' . $this->conf['contactEmail']);
                    $this->_logger->debug(var_export($astp_email, true));
                } catch (Zend_Mail_Transport_Exception $e) {
                    $this->_logger->alert('astp mail "personals updated" not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                }
	        }
	        header('Location: /' . $form->getAction());
	    }
	    if (count($form->getMessages()) > 0) {
	        foreach ($form->getMessages() as $key => $message) {
	            $this->_logger->debug('feuser (' . $this->feuser_id . ') ' . $key . ' ' . implode(', ', array_keys($message)));
	        }
	    }
	    $action = $form->getAction() . '?' . $this->prefixId . '[action]=editPersonal';
	    $form->setAction($action);
	    $form->populate($person->toArray());
	    $content.= '<h2>' . $this->pi_getLL('account_overview') . '</h2>';
	    $content.= $form->render();
	    return $content;
	}

	public function editAccount()
	{
        $content = '';
        Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
        Zend_Loader::loadClass('Mrastp_Form_Account');
        $feuserTable = new Mrastp_Db_Table_Feuser();
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $this->feuser_id));
	    if (!$feuser) {
            throw new Zend_Exception('feuser (' . $this->feuser_uid . ') was unable to fetch his fe_users row');
        }
        $form = new Mrastp_Form_Account($this);
        $_POST['uid'] = $feuser->uid; // for testing for duplicates in Validators
        if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
            $origValues = $feuser->toArray();
            $newValues = $form->getValues();
            if ($origValues['username'] != $newValues['username']) {
                $changeset['username'] = $newValues['username'];
            }
            if (!empty($newValues['password']) && $origValues['password'] != $newValues['password']) {
                $changeset['password'] = $newValues['password'];
            }
            if (count($changeset) > 0) {
                $this->_logger->debug('feuser (' . $this->feuser_id . ") has changed personals:\n" . var_export($changeset, true));
                $feuser->setFromArray($changeset);
                $feuser->tstamp = time();
                $record_id = $feuser->save();
                $this->_logger->debug('feuser (' . $this->feuser_id . ') saved row fe_users.' . (string) $record_id);
                $data = array('firstname' => '', 'name' => $feuser->name, 'email' => $feuser->email, 'username' => $feuser->username);
                $this->_logger->info('feuser (' . $this->feuser_id . ') updated his/her account details');

                // Mailings
                $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('v_registration_updated_subject', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('v_registration_updated_message1', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('kind_regards', $data) . "\r\n";
                $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
                Zend_Loader::loadClass('Zend_Mail');
                $cust_email = new Zend_Mail('utf-8');
                $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_updated_subject', $data))));
                $cust_email->setBodyHtml(nl2br($body));
                $cust_email->setBodyText(strip_tags($body));
                $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
                $cust_email->addTo($data['email']);
                try {
                    $cust_email->send();
                    $this->_logger->info('feuser (' . $this->feuser_id . ') Customer mail "account details updated" sent to ' . $data['email']);
                    $this->_logger->debug(var_export($cust_email, true));
                } catch (Zend_Mail_Transport_Exception $e) {
                    $this->_logger->alert('Customer mail "account details updated" not sent to ' . $data['email'] . ', feuser ' . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                }
                
                // an astp
                $body = $this->decorateLabel('v_registration_updated', $data) . "\r\n\r\n";
                foreach ($changeset as $key => $value) {
                    switch ($key) {
                        case 'password':
                            $body.= $this->pi_getLL($key) . ' ' . $this->pi_getLL('changed') . "\r\n";
                            break;
                        default:
                            $body.= $this->pi_getLL($key) . ' ' . $origValues[$key] . ' -> ' . $newValues[$key] . "\r\n";
                            break;
                    }
                }
                $body.= "\r\n";
                $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
                $astp_email = new Zend_Mail('utf-8');
                $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_updated', $data))));
                $astp_email->setBodyText(strip_tags($body));
                $astp_email->setBodyHtml(nl2br($body));
                $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
                $astp_email->addTo($this->conf['contactEmail']);
                try {
                    $astp_email->send();
                    $this->_logger->info('feuser (' . $this->feuser_id . ') astp mail "account details updated" sent to ' . $this->conf['contactEmail']);
                    $this->_logger->debug(var_export($astp_email, true));
                } catch (Zend_Mail_Transport_Exception $e) {
                    $this->_logger->alert('astp mail "account details updated" not sent to ' . $this->conf['contactEmail'] . ', feuser ' . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                }
            }
            header('Location: /' . $form->getAction());
        }
	    if (count($form->getMessages()) > 0) {
            foreach ($form->getMessages() as $key => $message) {
                $this->_logger->debug('feuser (' . $this->feuser_id . ') ' . $key . ' ' . implode(', ', array_keys($message)));
            }
        }
        $action = $form->getAction() . '?' . $this->prefixId . '[action]=editAccount';
        $form->setAction($action);
        $form->populate($feuser->toArray());
        $form->setDefault('password', '');
        $form->setDefault('password-repeat', '');
        $content.= '<h2>' . $this->pi_getLL('account_overview') . '</h2>';
        $content.= $form->render();
        return $content;
	}

	public function newWorkaddress()
	{
	    $content = '';
        $label['canton'] = $this->conf['labels']['canton_long'];
        $label['country'] = $this->conf['labels']['country_long'];
        Zend_Loader::loadClass('Mrastp_Form_Workaddress');
        $form = new Mrastp_Form_Workaddress($this);
        if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
            Zend_Loader::loadClass('Mrastp_Db_Table_Workaddress');
            Zend_Loader::loadClass('Mrastp_Db_Table_Person');
            $personTable = new Mrastp_Db_Table_Person();
            $person = $personTable->fetchRow(array('feuser_id = ?' => $this->feuser_id));
            if (!$person) {
                throw new Zend_Exception('feuser (' . $this->feuser_uid . ') has no astp_person!');
            }
            $workaddressTable = new Mrastp_Db_Table_Workaddress();
            $newRow = $workaddressTable->createRow($form->getValues());
            $newRow->pid = $this->conf['astpdbPID'];
            $newRow->cruser_id = 0;
            $newRow->hidden = $newRow->deleted = 0;
            $newRow->parentuid = $person->uid;
            $newRow->parenttable = 'tx_mrastp_person';
            $newRow->tstamp = $newRow->crdate = time();
            $humanTimestamp = $newRow->startofwork;
            $timestamp = 0;
            $splitArr = explode('.',$newRow->startofwork);
            if(count($splitArr) == 3) {
                $timestamp = mktime(23, 59, 0, $splitArr[1], $splitArr[0], $splitArr[2]);
            }
            $newRow->startofwork = $timestamp;
            $record_id = $newRow->save();
            $this->_logger->debug('feuser (' . $this->feuser_id . '), (person ' . $person->uid . ') saved row tx_mrastp_workaddress.' . (string) $record_id);
            $person->workaddress++;
            $person->save();
            $this->_logger->debug('Increased workaddress count for feuser (' . $this->feuser_id . '), (person ' . $person->uid . ') tx_mrasp_person.workaddress.');
            $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => '');
            $this->_logger->info('feuser (' . $this->feuser_id . '), (person ' . $person->uid . ') added a new workaddress');

            // Mailings
            $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
            $body.= $this->decorateLabel('v_registration_updated_subject', $data) . "\r\n\r\n";
            $body.= $this->decorateLabel('v_registration_updated_message1', $data) . "\r\n\r\n";
            $body.= $this->decorateLabel('kind_regards', $data) . "\r\n";
            $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
            Zend_Loader::loadClass('Zend_Mail');
            $cust_email = new Zend_Mail('utf-8');
            $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_updated_subject', $data))));
            $cust_email->setBodyHtml(nl2br($body));
            $cust_email->setBodyText(strip_tags($body));
            $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
            $cust_email->addTo($data['email']);
            try {
                $cust_email->send();
                $this->_logger->info('feuser (' . $this->feuser_id . ') Customer mail "new workaddress" sent to ' . $data['email']);
                $this->_logger->debug(var_export($cust_email, true));
            } catch (Zend_Mail_Transport_Exception $e) {
                $this->_logger->alert('Customer mail "new workaddress" not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
            }
                
            // an astp
            $body = $this->decorateLabel('v_registration_updated', $data) . "\r\n\r\nNeue Arbeitsaddresse erfasst:\r\n";
            $newAddress = $newRow->toArray();
            foreach ($newAddress as $key => $value) {
                switch ($key) {
                    case 'crdate':
                    case 'tstamp':
                    case 'uid':
                    case 'pid':
                    case 'parentuid':
                    case 'parenttable':
                    case 'cruser_id':
                    case 'sorting':
                    case 'starttime':
                    case 'endtime':
                    case 'deleted':
                    case 'hidden':
                        break;
                    case 'employment':
                        $body . $this->pi_getLL($key) . ' ' . $this->pi_getLL('employment.I.' . $value) . "\r\n";
                        break;
                    case 'canton_id':
                        $body.= $this->pi_getLL($key) . ' ' . $newRow->findParentRow('Mrastp_Db_Table_Canton')->$label['canton'] . "\r\n";
                        break;
                    case 'country_id':
                        $body.= $this->pi_getLL($key) . ' ' . $newRow->findParentRow('Mrastp_Db_Table_Country')->$label['country'] . "\r\n";
                        break;
                    case 'startofwork':
                        $body.= $this->pi_getLL($key) . ' ' . $humanTimestamp;
                        break;
                    default:
                        $body.= $this->pi_getLL($key) . ' ' . $value . "\r\n";
                        break;
                }
            }
            $body.= "\r\n";
            $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
            $astp_email = new Zend_Mail('utf-8');
            $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_updated', $data))));
            $astp_email->setBodyText(strip_tags($body));
            $astp_email->setBodyHtml(nl2br($body));
            $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
            $astp_email->addTo($this->conf['contactEmail']);
            try {
                $astp_email->send();
                $this->_logger->info('feuser (' . $this->feuser_id . ') astp mail "new workaddress" sent to ' . $this->conf['contactEmail']);
                $this->_logger->debug(var_export($astp_email, true));
            } catch (Zend_Mail_Transport_Exception $e) {
                $this->_logger->alert('astp mail "new workaddress" not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
            }
            header('Location: /' . $form->getAction());
        }
	    if (count($form->getMessages()) > 0) {
            foreach ($form->getMessages() as $key => $message) {
                $this->_logger->debug('feuser (' . $this->feuser_id . ') ' . $key . ' ' . implode(', ', array_keys($message)));
            }
        }
        $action = $form->getAction() . '?' . $this->prefixId . '[action]=newWorkaddress';
        $form->setAction($action);
        $content.= '<h2>' . $this->pi_getLL('account_overview') . '</h2>';
        $content.= $form->render();
        return $content;
	}

	public function editWorkaddress($uid)
	{
        $content = '';
        $label['canton'] = $this->conf['labels']['canton_long'];
        $label['country'] = $this->conf['labels']['country_long'];
        Zend_Loader::loadClass('Mrastp_Db_Table_Workaddress');
        Zend_Loader::loadClass('Mrastp_Form_Workaddress');
        $workaddressTable = new Mrastp_Db_Table_Workaddress();
        $workaddress = $workaddressTable->fetchRow(array('uid = ?' => $uid));
        $person = $workaddress->findParentRow('Mrastp_Db_Table_Person');
	    if (!$person) {
            throw new Zend_Exception('feuser (' . $this->feuser_uid . ') has no astp_person!');
        }
        if ($person->feuser_id != $this->feuser_id) {
            $this->_logger->warn('feuser (' . $this->_feuser_id . ') tried to edit workaddress ' . $uid . ' which belongs to person ' . $person->uid . ' (feuser ' . $person->feuser_id . ')');
            return '<div class="box">ERROR:<br /><br />Your are not allowed to edit this address!</div>';
        }
        $form = new Mrastp_Form_Workaddress($this);
        if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
            $origValues = $workaddress->toArray();
            $newValues = $form->getValues();
            $timestamp = 0;
            $humanTimestamp = $newValues['startofwork'];
            $splitArr = explode('.',$newValues['startofwork']);
            if(count($splitArr) == 3) {
                $timestamp = mktime(23, 59, 0, $splitArr[1], $splitArr[0], $splitArr[2]);
            }
            $newValues['startofwork'] = $timestamp;
            $changeset = array_diff_assoc($newValues, $origValues);
            if (count($changeset) > 0) {
                $this->_logger->debug('feuser (' . $this->feuser_id . ') (person ' . $person->uid . ") has changed workaddress " . $workaddress->uid . ":\n" . var_export($changeset, true));
                $workaddress->setFromArray($changeset);
                $workaddress->tstamp = time();
                $record_id = $workaddress->save();
                $this->_logger->debug('feuser (' . $this->feuser_id . ') saved row tx_mrastp_workaddress.' . (string) $record_id);
                $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => '');
                $this->_logger->info('feuser (' . $this->feuser_id . '), (person ' . $person->uid . ') updated workaddress ' . $uid);

                // Mailings
                $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('v_registration_updated_subject', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('v_registration_updated_message1', $data) . "\r\n\r\n";
                $body.= $this->decorateLabel('kind_regards', $data) . "\r\n";
                $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
                Zend_Loader::loadClass('Zend_Mail');
                $cust_email = new Zend_Mail('utf-8');
                $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_updated_subject', $data))));
                $cust_email->setBodyHtml(nl2br($body));
                $cust_email->setBodyText(strip_tags($body));
                $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
                $cust_email->addTo($data['email']);
                try {
                    $cust_email->send();
                    $this->_logger->info('feuser (' . $this->feuser_id . ') Customer mail "workaddress updated" sent to ' . $data['email']);
                    $this->_logger->debug(var_export($cust_email, true));
                } catch (Zend_Mail_Transport_Exception $e) {
                    $this->_logger->alert('Customer mail "workaddress updated" not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $this->feuser_id . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                }
                
                // an astp
                $body = $this->decorateLabel('v_registration_updated', $data) . "\r\n\r\n";
                foreach ($changeset as $key => $value) {
                    switch ($key) {
                        case 'crdate':
                        case 'tstamp':
                        case 'uid':
                        case 'pid':
                        case 'parentuid':
                        case 'parenttable':
                        case 'cruser_id':
                        case 'sorting':
                        case 'starttime':
                        case 'endtime':
                        case 'deleted':
                        case 'hidden':
                            break;
                        case 'employment':
                            $body . $this->pi_getLL($key) . ' ' . $this->pi_getLL('employment.I.' . $value) . "\r\n";
                            break;
                        case 'canton_id':
                            $body.= $this->pi_getLL($key) . ' ' . $workaddress->findParentRow('Mrastp_Db_Table_Canton')->$label['canton'] . "\r\n";
                            break;
                        case 'country_id':
                            $body.= $this->pi_getLL($key) . ' ' . $workaddress->findParentRow('Mrastp_Db_Table_Country')->$label['country'] . "\r\n";
                            break;
                        case 'startofwork':
                            $body.= $this->pi_getLL($key) . ' ' . $humanTimestamp;
                            break;
                        default:
                            $body.= $this->pi_getLL($key) . ' ' . $origValues[$key] . ' -> ' . $newValues[$key] . "\r\n";
                            break;
                    }
                }
                $body.= "\r\n";
                $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
                $astp_email = new Zend_Mail('utf-8');
                $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_updated', $data))));
                $astp_email->setBodyText(strip_tags($body));
                $astp_email->setBodyHtml(nl2br($body));
                $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
                $astp_email->addTo($this->conf['contactEmail']);
                try {
                    $astp_email->send();
                    $this->_logger->info('feuser (' . $this->feuser_id . ') astp mail "workaddress updated" sent to ' . $this->conf['contactEmail']);
                    $this->_logger->debug(var_export($astp_email, true));
                } catch (Zend_Mail_Transport_Exception $e) {
                    $this->_logger->alert('astp mail "workaddress updated" not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $this->feuser_id . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
                }
            }
            header('Location: /' . $form->getAction());
        }
        if (count($form->getMessages()) > 0) {
            foreach ($form->getMessages() as $key => $message) {
                $this->_logger->debug('feuser (' . $this->feuser_id . ') ' . $key . ' ' . implode(', ', array_keys($message)));
            }
        }
        $action = $form->getAction() . '?' . $this->prefixId . '[action]=editWorkaddress&' . $this->prefixId . '[uid]=' . $uid;
        $form->setAction($action);
        $form->populate($workaddress->toArray());
        $form->getElement('startofwork')->setValue(date('d.m.Y', $form->getElement('startofwork')->getValue()));
        $content.= '<h2>' . $this->pi_getLL('account_overview') . '</h2>';
        $content.= $form->render();
        return $content;
	}

	public function deleteWorkaddress($uid)
    {
        $content = '';
        $label['canton'] = $this->conf['labels']['canton_long'];
        $label['country'] = $this->conf['labels']['country_long'];
        Zend_Loader::loadClass('Mrastp_Db_Table_Workaddress');
        Zend_Loader::loadClass('Mrastp_Form_Workaddress');
        $workaddressTable = new Mrastp_Db_Table_Workaddress();
        $workaddress = $workaddressTable->fetchRow(array('uid = ?' => $uid));
        $person = $workaddress->findParentRow('Mrastp_Db_Table_Person');
        if (!$person) {
            throw new Zend_Exception('feuser (' . $this->feuser_uid . ') has no astp_person!');
        }
        if ($person->feuser_id != $this->feuser_id) {
            $this->_logger->warn('feuser (' . $this->_feuser_id . ') tried to delete workaddress ' . $uid . ' which belongs to person ' . $person->uid . ' (feuser ' . $person->feuser_id . ')');
            return '<div class="box">ERROR:<br /><br />Your are not allowed to delete this address!</div>';
        }
        $changeset = array('deleted' => 1);
        $workaddress->setFromArray($changeset);
        $record_id = $workaddress->save();
        $this->_logger->debug('feuser (' . $this->feuser_id . ') saved row tx_mrastp_workaddress.' . (string) $record_id);
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => '');
        $this->_logger->info('feuser (' . $this->feuser_id . '), (person ' . $person->uid . ') deleted workaddress ' . $uid);

        // Mailings
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_updated_subject', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_updated_message1', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        Zend_Loader::loadClass('Zend_Mail');
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_updated_subject', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        try {
            $cust_email->send();
            $this->_logger->info('feuser (' . $this->feuser_id . ') Customer mail "workaddress deleted" sent to ' . $data['email']);
            $this->_logger->debug(var_export($cust_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('Customer mail "workaddress deleted" not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $this->feuser_id . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        // an astp
        $body = $this->decorateLabel('v_registration_updated', $data) . "\r\n\r\nArbeitsaddresse gelöscht:\r\n";
        $workaddressArray = $workaddress->toArray();
        foreach ($workaddressArray as $key => $value) {
            switch ($key) {
                case 'crdate':
                case 'tstamp':
                case 'uid':
                case 'pid':
                case 'parentuid':
                case 'parenttable':
                case 'cruser_id':
                case 'sorting':
                case 'starttime':
                case 'endtime':
                case 'deleted':
                case 'hidden':
                    break;
                case 'employment':
                    $body . $this->pi_getLL($key) . ' ' . $this->pi_getLL('employment.I.' . $value) . "\r\n";
                    break;
                case 'canton_id':
                    $body.= $this->pi_getLL($key) . ' ' . $workaddress->findParentRow('Mrastp_Db_Table_Canton')->$label['canton'] . "\r\n";
                    break;
                case 'country_id':
                    $body.= $this->pi_getLL($key) . ' ' . $workaddress->findParentRow('Mrastp_Db_Table_Country')->$label['country'] . "\r\n";
                    break;
                case 'startofwork':
                    $body.= $this->pi_getLL($key) . ' ' . $humanTimestamp;
                    break;
                default:
                    $body.= $this->pi_getLL($key) . ' ' . $value . "\r\n";
                    break;
                }
        }
        $body.= "\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_updated', $data))));
        $astp_email->setBodyText(strip_tags($body));
        $astp_email->setBodyHtml(nl2br($body));
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
        try {
            $astp_email->send();
            $this->_logger->info('feuser (' . $this->feuser_id . ') astp mail "workaddress updated" sent to ' . $this->conf['contactEmail']);
            $this->_logger->debug(var_export($astp_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('astp mail "workaddress updated" not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $this->feuser_id . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        header('Location: /' . $this->pi_getPageLink($GLOBALS['TSFE']->id));
    }
	
	public function handleConfirmation()
	{
	    $content = '';
	    $pi1_getVars = t3lib_div::_GET('tx_mrastp_pi2');
	    $hash = isset($pi1_getVars['hash']) ? $pi1_getVars['hash'] : false;
	    if ($hash) {
	        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
	        $hashTable = new Mrastp_Db_Table_Hashes();
	        $hashRow = $hashTable->fetchRow(array('hash = ?' => $hash));
	        switch ($hashRow->command) {
	            case 'CONFIRM':
	                $this->_logger->debug('Entering account confirmation for member ' . $hashRow->parentuid . ':');
	                $content .= $this->processConfirmation($hashRow->parentuid);
	                break;
	            case 'DELETE':
	                $this->_logger->debug('Entering account deletion' . $hashRow->parentuid . ':');
	                $content .= $this->processDeletion($hashRow->parentuid);
	                break;
                case 'ACCEPT':
                    $this->_logger->debug('Entering account acceptation' . $hashRow->parentuid . ':');
                    $content .= $this->processAcceptation($hashRow->parentuid);
                    break;
	            case 'REFUSE':
	                $this->_logger->debug('Entering account refusal' . $hashRow->parentuid . ':');
	                $content .= $this->processRefusal($hashRow->parentuid);
	                break;
	            default:
	                $this->_logger->notice('Invalid hash specified, maybe not valid anymore or hash guessing');
	                $content.= '<div class="box">ERROR<br /><br />URL not found or not valid anymore.</div>';
	        }
	        return $content;
	    } else {
	       return '<div class="box">ERROR<br /><br />No hash set.</div>';
	    }
	}
	
	public function processConfirmation($uid)
	{
	    Zend_Loader::loadClass('Mrastp_Db_Table_Person');
	    Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
	    $personTable = new Mrastp_Db_Table_Person();
	    $feuserTable = new Mrastp_Db_Table_Feuser();
	    $person = $personTable->fetchRow(array('uid = ?' => $uid));
	    if (!$person) {
	        throw new Zend_Exception('processConfirmation: Unable to find member ' . $uid);
	    }
	    $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
	    if (!$feuser) {
	        throw new Zend_Exception('processConfirmation: Unable to find frontend user for member ' . $uid);
	    }
	    $feuser->usergroup = $this->conf['userGroupAfterConfirmation'];
	    $record_id = $feuser->save();
	    $this->_logger->debug('processConfirmation: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') saved row fe_users.' . $record_id);
	    $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);
	    $this->_logger->info('processConfirmation: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') successfully confirmed his/her email.');
	    
	    // Mailings
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed_subject', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed_review1', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed_review2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_ini', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        Zend_Loader::loadClass('Zend_Mail');
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_confirmed_subject', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
	    try {
            $cust_email->send();
            $this->_logger->info('processConfirmation: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') Customer mail sent to ' . $data['email']);
            $this->_logger->debug(var_export($cust_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processConfirmation: Customer mail not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        // an astp
        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashTable = new Mrastp_Db_Table_Hashes();
        $hashes = $hashTable->fetchAll(array('parentuid = ?' => $person->uid));
        foreach ($hashes as $hash) {
            if ($hash->command == 'ACCEPT') {
                $acceptHash = $hashTable->fetchRow(array('parentuid = ?' => $uid, 'command = ?' => 'ACCEPT'));
            } elseif ($hash->command == 'REFUSE') {
                $refuseHash = $hashTable->fetchRow(array('parentuid = ?' => $uid, 'command = ?' => 'REFUSE'));
            } else {
                $hash->delete();
            }
        }
        $body = $this->decorateLabel('v_to_the_administrator', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_review_message3', $data) . "\r\n";
        $body.= $this->pi_getLL('name') . ' ' . $person->firstname . ' ' . $person->name . "\r\n";
        $body.= $this->pi_getLL('street') . ': ' . $person->street . "\r\n";
        $body.= $this->pi_getLL('city') . ' ' . $person->zip . ' ' . $person->city . "\r\n";
        $body.= $this->pi_getLL('phone') . ': ' . $person->phone . "\r\n";
        $body.= $this->pi_getLL('email') . ' ' . $person->email . "\r\n";
        $body.= $this->pi_getLL('username') . ' ' . $feuser->username . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_review_message1', $data) . "\r\n";
        $body.= '<a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $acceptHash->hash . '">' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $acceptHash->hash . "</a>\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_review_message2', $data) . "\r\n";
        $body.= '<a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $refuseHash->hash . '">' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['confirmPID'] . '&L=' . $this->conf['sys_language_content'] . '&tx_mrastp_pi2[hash]=' . $refuseHash->hash . "</a>\r\n\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_review_subject', $data))));
        $astp_email->setBodyText(strip_tags($body));
        $astp_email->setBodyHtml(nl2br($body));
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
	    try {
            $astp_email->send();
            $this->_logger->info('processConfirmation: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') astp mail sent to ' . $this->conf['contactEmail']);
            $this->_logger->debug(var_export($astp_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processConfirmation: astp mail not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        $content.= '<div class="box">';
        $content.= '<p>' . $this->decorateLabel('v_dear', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_confirmed_subject', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_confirmed_review1', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_confirmed_review2', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('kind_regards', $data) . '<br />' . $this->conf['contactName'] . '</p>';
        $content.= '</div>';
        return $content;
	}
	
	public function processDeletion($uid)
	{
        Zend_Loader::loadClass('Mrastp_Db_Table_Person');
        Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
        $personTable = new Mrastp_Db_Table_Person();
        $feuserTable = new Mrastp_Db_Table_Feuser();
        $person = $personTable->fetchRow(array('uid = ?' => $uid));
        if (!$person) {
            throw new Zend_Exception('processDeletion: Unable to find member ' . $uid);
        }
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
        if (!$feuser) {
            throw new Zend_Exception('processDeletion: Unable to find frontend user for member ' . $uid);
        }
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);
        $record_id = $person->delete();
        $this->_logger->debug('processDeletion: Successfully deleted row tx_mrastp_person.' . $record_id);
        $record_id = $feuser->delete();
        $this->_logger->debug('processDeletion: Successfully deleted row fe_users.' . $record_id);
        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashes = $hashTable->fetchAll(array('parentuid = ?' => $uid));
        foreach ($hashes as $hash) {
            $hash->delete();
        }
        $hashTable = new Mrastp_Db_Table_Hashes();
        $hashTable->delete(array('parentuid = ?' => $uid));
        $this->_logger->info('processDeletion: Member ' . $uid . ' successfully deleted registration');
        
        // Mailings
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_cancelled_subject', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_cancelled_message1', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_cancelled_message2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_del', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        Zend_Loader::loadClass('Zend_Mail');
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_cancelled_subject', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
	    try {
            $cust_email->send();
            $this->_logger->info('processDeletion: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') Customer mail sent to ' . $data['email']);
            $this->_logger->debug(var_export($cust_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processDeletion: Customer mail not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        // an astp
        $body = $this->decorateLabel('v_registration_cancelled', $data) . "\r\n\r\n";
        $body.= $this->pi_getLL('name') . ': ' . $data['firstname'] . ' ' . $data['name'] . "\r\n";
        $body.= $this->pi_getLL('email') . ' ' . $data['email'] . "\r\n";
        $body.= $this->pi_getLL('username') . ': ' . $data['username'] . "\r\n\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_cancelled', $data))));
        $astp_email->setBodyText(strip_tags($body));
        $astp_email->setBodyHtml(nl2br($body));
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
	    try {
            $astp_email->send();
            $this->_logger->info('processDeletion: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') astp mail sent to ' . $this->conf['contactEmail']);
            $this->_logger->debug(var_export($astp_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processDeletion: astp mail not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        $content.= '<div class="box">';
        $content.= '<p>' . $this->decorateLabel('v_dear', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_cancelled_subject', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_cancelled_message1', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_cancelled_message2', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('kind_regards_del', $data) . '<br />' . $this->conf['contactName'] . '</p>';
        $content.= '</div>';
        return $content;
    }

    public function processAcceptation($uid)
    {
        Zend_Loader::loadClass('Mrastp_Db_Table_Person');
        Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
        $personTable = new Mrastp_Db_Table_Person();
        $feuserTable = new Mrastp_Db_Table_Feuser();
        $person = $personTable->fetchRow(array('uid = ?' => $uid));
        if (!$person) {
            throw new Zend_Exception('processAcceptation: Unable to find member ' . $uid);
        }
        $person->hidden = 0;
        $person->entry_date = time();
        $record_id = $person->save();
        $this->_logger->debug('processAcceptation: Successfully saved row tx_mrastp_person.' . $record_id);
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
        if (!$feuser) {
            throw new Zend_Exception('processAcceptation: Unable to find frontend user for member ' . $uid);
        }
        $feuser->usergroup = $this->conf['userGroupAfterAcceptation'];
        $feuser->disable = 0;
        $record_id = $feuser->save();
        $this->_logger->debug('processAcceptation: Successfully saved row fe_users.' . $record_id . ' for member ' . $person->uid);
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);

        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashes = $hashTable->fetchAll(array('parentuid = ?' => $uid));
        foreach ($hashes as $hash) {
            $hash->delete();
        }
        $this->_logger->info('processAcceptation: Member ' . $person->uid . ' was successfully accepted');
        
        // Mailings
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_accepted_subject2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_accepted_message3', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_accepted_message4', $data);
        $body.= '<a href="' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['loginPID'] . '&L=' . $this->conf['sys_language_content'] . '">' . $this->conf['siteUrl'] . 'index.php?id=' . $this->conf['loginPID'] . '&L=' . $this->conf['sys_language_content'] . "</a>\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_cre', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\n" . $this->conf['siteUrl'] . "\r\n" . $this->conf['contactEmail'];
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_accepted_subject2', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        try {
            $cust_email->send();
            $this->_logger->info('processAcceptation: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') Customer mail sent to ' . $data['email']);
            $this->_logger->debug(var_export($cust_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processAcceptation: Customer mail not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        // an astp
        $body = $this->decorateLabel('v_registration_accepted', $data) . "\r\n\r\n";
        $body.= $this->pi_getLL('name') . ': ' . $data['firstname'] . ' ' . $data['name'] . "\r\n";
        $body.= $this->pi_getLL('email') . ' ' . $data['email'] . "\r\n";
        $body.= $this->pi_getLL('username') . ': ' . $data['username'] . "\r\n\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        Zend_Loader::loadClass('Zend_Mail');
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_accepted', $data))));
        $astp_email->setBodyText(strip_tags($body));
        $astp_email->setBodyHtml(nl2br($body));
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
        try {
            $astp_email->send();
            $this->_logger->info('processAcceptation: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') astp mail sent to ' . $this->conf['contactEmail']);
            $this->_logger->debug(var_export($astp_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processAcceptation: astp mail not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        $content.= '<div class="box">';
        $content.= '<p>' . $this->decorateLabel('v_to_the_administrator', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_accepted_subject', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_accepted_message1', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_accepted_message2', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('kind_regards', $data) . '<br />' . $this->conf['contactName'] . '</p>';
        $content.= '</div>';
        return $content;
    }

    public function processRefusal($uid)
    {
        Zend_Loader::loadClass('Mrastp_Db_Table_Person');
        Zend_Loader::loadClass('Mrastp_Db_Table_Feuser');
        $personTable = new Mrastp_Db_Table_Person();
        $feuserTable = new Mrastp_Db_Table_Feuser();
        $person = $personTable->fetchRow(array('uid = ?' => $uid));
        if (!$person) {
            throw new Zend_Exception('processRefusal: Unable to find member ' . $uid);
        }
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
        if (!$feuser) {
            throw new Zend_Exception('processRefusal: Unable to find frontend user for member ' . $uid);
        }
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);
        $person->deleted = 1;
        $record_id = $person->save();
        $this->_logger->debug('processRefusal: Successfully saved row tx_mrastp_person.' . $record_id);
        $feuser->usergroup = 6;
        $record_id = $feuser->save();
        $this->_logger->debug('processRefusal: Successfully saved row fe_users.' . $record_id);
        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashes = $hashTable->fetchAll(array('parentuid = ?' => $uid));
        foreach ($hashes as $hash) {
            $hash->delete();
        }
        $this->_logger->info('processRefusal: Member ' . $person->uid . ' was successfully refused');
        
        // Mailings
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_refused_subject2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_refused_message3', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_refused_message4', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_del', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        Zend_Loader::loadClass('Zend_Mail');
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_refused_subject2', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        try {
            $cust_email->send();
            $this->_logger->info('processRefusal: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') Customer mail sent to ' . $data['email']);
            $this->_logger->debug(var_export($cust_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processRefusal: Customer mail not sent to ' . $data['email'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        // an astp
        $body = $this->decorateLabel('v_registration_refused_subject', $data) . "\r\n\r\n";
        $body.= $this->pi_getLL('name') . ': ' . $data['firstname'] . ' ' . $data['name'] . "\r\n";
        $body.= $this->pi_getLL('email') . ' ' . $data['email'] . "\r\n";
        $body.= $this->pi_getLL('username') . ': ' . $data['username'] . "\r\n\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_refused_subject', $data))));
        $astp_email->setBodyText(strip_tags($body));
        $astp_email->setBodyHtml(nl2br($body));
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
        try {
            $astp_email->send();
            $this->_logger->info('processRefusal: feuser (' . $feuser->uid . ') (person ' . $person->uid . ') astp mail sent to ' . $this->conf['contactEmail']);
            $this->_logger->debug(var_export($astp_email, true));
        } catch (Zend_Mail_Transport_Exception $e) {
            $this->_logger->alert('processRefusal: astp mail not sent to ' . $this->conf['contactEmail'] . ' (person ' . $person->uid . ", feuser " . $feuser->uid . ")\n" . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n");
        }
        
        $content.= '<div class="box">';
        $content.= '<p>' . $this->decorateLabel('v_to_the_administrator', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_refused', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_refused_message1', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('v_registration_refused_message2', $data) . '</p>';
        $content.= '<p>' . $this->decorateLabel('kind_regards', $data) . '<br />' . $this->conf['contactName'] . '</p>';
        $content.= '</div>';
        return $content;
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
    
    public function getFeUserLang()
    {
        return $this->languages[$this->conf['sys_language_content']];
    }
    
    public function decorateLabel($label, $data)
    {
        return sprintf($this->pi_getLL($label), $this->conf['thePidTitle'], $data['username'], $data['firstname'] . ' ' . $data['name'], $data['email']);
    }
    
    public function _setupCommands($uid)
    {
        //CONFIRM: email bestätigt
        //ACCEPT: als Mitglied aufgenommen
        //REFUSE: Aufnahme abgelehnt
        //DELETE: Mitglied hat gelöscht
        //APPROVE: ???
        $commands = array();
        $commands['CONFIRM'] = array('hash' => md5('CONFIRM' . $uid), 'parentuid' => $uid, 'command' => 'CONFIRM');
        $commands['ACCEPT'] = array('hash' => md5('ACCEPT' . $uid), 'parentuid' => $uid, 'command' => 'ACCEPT');
        $commands['REFUSE'] = array('hash' => md5('REFUSE' . $uid), 'parentuid' => $uid, 'command' => 'REFUSE');
        $commands['DELETE'] = array('hash' => md5('DELETE' . $uid), 'parentuid' => $uid, 'command' => 'DELETE');
        
        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashesTable = new Mrastp_Db_Table_Hashes();
        foreach ($commands as $comand) {
            $row = $hashesTable->createRow($comand);
            $row->crdate = time();
            $record_id = $row->save();
            $this->_logger->debug('Person (' . $uid . ') saved new hashes on registration.');
        }
        unset($hashesTable);
        return $commands; 
    }
    
    public function reduceSubject($subject)
    {
        if (mb_strlen($subject) > 65) {
            $subject = substr($subject, 0, 62) . '...'; 
        }
        return $subject;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi2/class.tx_mrastp_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi2/class.tx_mrastp_pi2.php']);
}

?>
