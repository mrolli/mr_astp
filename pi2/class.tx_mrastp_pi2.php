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
        // db adapter for Zend_Db_Table-Classes
        Zend_Loader::loadClass('Zend_Db');
        Zend_Loader::loadClass('Zend_Db_Table_Abstract');
        $db = Zend_Db::factory('Mysqli', array('host'     => 'localhost',
                                               'username' => 'typo3admin',
                                               'password' => 't3pass',
                                               'dbname'   => 't3_astp'));
        Zend_Db_Table_Abstract::setDefaultAdapter($db);

        // default mail transport for all mails generated
        Zend_Loader::loadClass('Zend_Mail');
        //Zend_Loader::loadClass('Zend_Mail_Transport_Sendmail');
        //$tr = new Zend_Mail_Transport_Sendmail('-fbounces@astp.ch');
        Zend_Loader::loadClass('Zend_Mail_Transport_Smtp');
        $tr = new Zend_Mail_Transport_Smtp('smtp.unibe.ch');
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
	    Zend_Loader::loadClass('Mrastp_Form_Registration');
	    $registrationForm = new Mrastp_Form_Registration($this);
	    if (isset($_POST['submitButton']) && $registrationForm->isValid($_POST)) {
	        $data = $registrationForm->getValues();
	        if (false) { // $this->_isDuplicateMember($data)) {
	            $content.= '<p class="errors">' . $this->pi_getLL('member_exists') . '</p>';
	        } else {
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

        $person_row['feuser_id'] = $feuserUid;

	    $newFieldList = 'pid,tstamp,crdate,cruser_id,hidden,salutation_id,firstname,name,street,compl,zip,city,canton_id,country_id,phone,mobile,fax,email,language_id,section_id,status,entry_date,feuser_id';
        $this->cObj->DBgetInsert('tx_mrastp_person', $this->conf['astpdbPID'], $person_row, $newFieldList, true);
        $personUid = $TYPO3_DB->sql_insert_id();
        
        //setup commands for this user
        $commands = $this->_setupCommands($personUid);
        // Mailings
        require_once('Zend/Mail.php');
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
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject($this->decorateLabel('v_please_confirm', $data)));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        $cust_email->send();
        
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
        $astp_email->send();
	}

	public function displayEditForm()
	{
	    return 'Edit Form';
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
	                $content .= $this->processConfirmation($hashRow->parentuid);
	                break;
	            case 'DELETE':
	                $content .= $this->processDeletion($hashRow->parentuid);
	                break;
                case 'ACCEPT':
                    $content .= $this->processAcceptation($hashRow->parentuid);
                    break;
	            case 'REFUSE':
	                $content .= $this->processRefusal($hashRow->parentuid);
	                break;
	            default:
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
	    $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
	    $feuser->usergroup = $this->conf['userGroupAfterConfirmation'];
	    $feuser->save();
	    $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);
	    
	    // Mailings
        require_once('Zend/Mail.php');
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed_subject', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed_review1', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_confirmed_review2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_ini', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_confirmed_subject', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        $cust_email->send();
        
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
        $astp_email->send();
        
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
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);
        $person->delete();
        $feuser->delete();
        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashTable = new Mrastp_Db_Table_Hashes();
        $hashTable->delete(array('parentuid = ?' => $uid));
        
        // Mailings
        require_once('Zend/Mail.php');
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_cancelled_subject', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_cancelled_message1', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_cancelled_message2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_del', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_cancelled_subject', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        $cust_email->send();
        
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
        $astp_email->send();
        
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
        $person->hidden = 0;
        $person->save();
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
        $feuser->usergroup = $this->conf['userGroupAfterAcceptation'];
        $feuser->disable = 0;
        $feuser->save();
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);

        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashTable = new Mrastp_Db_Table_Hashes();
        $hashTable->delete(array('parentuid = ?' => $uid));
        
        // Mailings
        require_once('Zend/Mail.php');
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
        $cust_email->send();
        
        // an astp
        $body = $this->decorateLabel('v_registration_accepted', $data) . "\r\n\r\n";
        $body.= $this->pi_getLL('name') . ': ' . $data['firstname'] . ' ' . $data['name'] . "\r\n";
        $body.= $this->pi_getLL('email') . ' ' . $data['email'] . "\r\n";
        $body.= $this->pi_getLL('username') . ': ' . $data['username'] . "\r\n\r\n";
        $body.= $this->pi_getLL('kind_regards') . "\r\n" . $this->conf['contactName'];
        $astp_email = new Zend_Mail('utf-8');
        $astp_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_notification', $data) . ' ' . $this->decorateLabel('v_registration_accepted', $data))));
        $astp_email->setBodyText(strip_tags($body));
        $astp_email->setBodyHtml(nl2br($body));
        $astp_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $astp_email->addTo($this->conf['contactEmail']);
        $astp_email->send();
        
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
        $feuser = $feuserTable->fetchRow(array('uid = ?' => $person->feuser_id));
        $data = array('firstname' => $person->firstname, 'name' => $person->name, 'email' => $person->email, 'username' => $feuser->username);
        $person->delete();
        $feuser->delete();
        Zend_Loader::loadClass('Mrastp_Db_Table_Hashes');
        $hashTable = new Mrastp_Db_Table_Hashes();
        $hashTable->delete(array('parentuid = ?' => $uid));
        
        // Mailings
        require_once('Zend/Mail.php');
        $body = $this->decorateLabel('v_dear', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_refused_subject2', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_refused_message3', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('v_registration_refused_message4', $data) . "\r\n\r\n";
        $body.= $this->decorateLabel('kind_regards_del', $data) . "\r\n";
        $body.= $this->conf['contactName'] . "\r\nhttp://www.astp.ch/\r\n" . $this->conf['contactEmail'];
        $cust_email = new Zend_Mail('utf-8');
        $cust_email->setSubject($this->reduceSubject(strip_tags($this->decorateLabel('v_registration_refused_subject2', $data))));
        $cust_email->setBodyHtml(nl2br($body));
        $cust_email->setBodyText(strip_tags($body));
        $cust_email->setFrom($this->conf['contactEmail'], $this->conf['contactName']);
        $cust_email->addTo($data['email']);
        $cust_email->send();
        
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
        $astp_email->send();
        
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
            $row->save();
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
