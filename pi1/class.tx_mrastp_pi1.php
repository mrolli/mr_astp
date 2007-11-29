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


/**
 * Plugin 'Job offers' for the 'mr_astp' extension.
 *
 * @author	Michael Rolli <michael@rollis.ch>
 * @package	TYPO3
 * @subpackage	tx_mrastp
 */
class tx_mrastp_pi1 extends tslib_pibase {
	var $prefixId      = 'tx_mrastp_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_mrastp_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'mr_astp';	// The extension key.
	var $pi_checkCHash = true;

	var $config = array();
	var $pageArray = array(); // Is initialized with an array of the pages in the pid-list

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{

	    $this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.
	    $this->pi_USER_INT_obj=1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!
            $this->init($conf);

            // get codes and decide which function is used to process the content
            $codes = t3lib_div::trimExplode(',', $this->config['code'] ? $this->config['code'] : 'NONE', 1);
            if (!count($codes)) { // no code at all
                $codes = array('NONE');
		$noCode = true;
            }

		foreach($codes as $code) {
		    $code = $this->theCode = (string)strtoupper(trim($code));
		    switch($code) {
		        case 'ADDRESSLIST':
		            $content .= $this->displayAdressList();
		            break;
			case 'MEMBERLIST':
			    $content .= $this->displayMemberList();
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

	private function init($conf) {
	    $this->conf = $conf;
	    $langid = intval (t3lib_div::_GP('L'));
            $languages = array(0 => 'de', 1 => 'fr');
	    $this->conf['lang'] = $languages[$langid];
	    $this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm(); // Init FlexForm configuration for plugin

		// "CODE" decides what is rendered: codes can be set by TS or FF with priority on FF
		$code = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		$this->config['code'] = $code ? $code : $this->cObj->stdWrap($this->conf['code'], $this->conf['code.']);
	}

	private function displayAdressList() {
	    global $TYPO3_DB;
	    $content = '';

	    $lists = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'addresslist', 'sDEF');
	    $lists = t3lib_div::trimExplode(',', $lists);
	    foreach ($lists as $list) {
		$content.= $this->renderGroup( $this->getGroupTitle($list), $this->getGroupMembers($list) );
	    }
	    return $content;
	}

	private function displayMemberList() {
            global $TYPO3_DB;
            $TYPO3_DB->debugOutput = TRUE;

	    $pi1_getVars = t3lib_div::_GET('tx_mrastp_pi1');
	    if(isset($pi1_getVars['show'])) {
		$show = $pi1_getVars['show'];
	    } else {
		$show = 'A';
	    }

            $members = array();
	    $content = $this->helperMembersAlphabet($show);
            $fields = 'firstname, name, street, compl, zip, city, phone, email, canton_id';

	    switch($show) {
		case '':
		    $where = 'name LIKE \'A%\'';
		    break;
		case 'alle':
		    $where = '1=1';
		    break;
		default:
		    $where = 'name like \'' . substr($show, 0, 1) . '%\'';
		    break;
	    }
            $where.= $this->cObj->enableFields('tx_mrastp_person');
            $groupby = '';
            $orderby = 'name, firstname ASC';
            $limit = '';
	    $i=0;

	    $content.= '<table class="contenttable contenttable-2">';
            $result = $TYPO3_DB->exec_SELECTquery($fields, 'tx_mrastp_person', $where, $groupby, $orderby, $limit);
            while($member = $TYPO3_DB->sql_fetch_assoc($result)) {
                if(0 == $i%2) {
                    $zebra = 'tr-even';
                } else {
                    $zebra = 'tr-odd';
                }
                $compl = (!empty($member['compl'])) ? '<br />' . $member['compl'] : '';
                $canton_data = $this->getCantonData( $member['canton_id']);
                $canton = $canton_data['abbrevation'];
                $email = (!empty($member['email'])) ? $this->local_cObj->mailto_makelinks('mailto:' . $member['email'], $this->conf['makelinks.']['mailto.']) : '';
                $i++;
                $content.= '<tr class="' . $zebra . '">';
                $content.= '<td>' . $member['name'] . ', ' . $member['firstname'] . '</td>';
                $content.= '<td>' . $member['street'] . $compl . '<br />' . $member['zip'] . ' ' . $member['city'] . '<br />' . $canton . '</td>';
                $content.= '<td>' . $member['phone'] . '</td>';
                $content.= '<td>' . $email . '</td>';
                $content.= '</tr>';
	    }
	    $content.= '</table>';
	    return $content;
	}

	private function getGroupMembers($groupid) {

	    global $TYPO3_DB;
	    //$TYPO3_DB->debugOutput = TRUE;

	    $members = array();
            $fields = 'firstname, name, street, compl, zip, city, phone, email, funktion_' . $this->conf['lang'] . ' as funktion, tx_mrastp_persons_groups_rel.canton_id'; 
	    $where = 'tx_mrastp_person.uid=tx_mrastp_persons_groups_rel.personid AND groupid=' . $groupid . ' AND tx_mrastp_person.deleted=0 AND tx_mrastp_person.hidden=0'; 
	    $groupby = '';
	    $orderby = 'groupsort ASC';
	    $limit = '';

	    $result = $TYPO3_DB->exec_SELECTquery($fields, 'tx_mrastp_person, tx_mrastp_persons_groups_rel', $where, $groupby, $orderby, $limit);
	    while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
		$members[] = $row;
            }
	    $result = null;
	    return $members;
	}

	private function renderGroup($grouptitle, $members) {
	    $content = '<h2>' . $grouptitle . '</h2>';
	    $content.= '<table class="contenttable contenttable-2">';
	    $i=0; 
            foreach ($members as $member) {
		if(0 == $i%2) {
		    $zebra = 'tr-even';
		} else {
		    $zebra = 'tr-odd';
		}
		$compl = (!empty($member['compl'])) ? '<br />' . $member['compl'] : '';
		$canton = '';
		if ($member['canton_id']>0) {
		    $canton_data = $this->getCantonData( (int) $member['canton_id']);
		    $canton = $canton_data['abbrevation'];
		}
		$i++;
		$content.= '<tr class="' . $zebra . '">';
		$content.= '<td>' . $member['firstname'] . ' ' . $member['name'] . '</td>';
		$content.= '<td>' . $member['funktion'] . ' ' . $canton . '</td>';
		$content.= '<td>' . $member['street'] . $compl . '<br />' . $member['zip'] . ' ' . $member['city'] . '</td>';
		$content.= '<td>' . $member['phone'] . '</td>';
		$content.= '<td>' . $this->local_cObj->mailto_makelinks('mailto:' . $member['email'], $this->conf['makelinks.']['mailto.']) . '</td>';
		$content.= '</tr>';
                
	    }
            $content.= '</table>';
	    return $content;
	}

	// Helper function follow
        private function getGroupTitle($groupid) {

            global $TYPO3_DB;

            $fields = 'label_' . $this->conf['lang'] . ' as label';
            $result = $TYPO3_DB->exec_SELECTquery($fields, 'tx_mrastp_group', 'uid=' . $groupid);
            $row = $TYPO3_DB->sql_fetch_assoc($result);
            return $row['label'];
        }

        private function getCantonData($cantonid) {

            global $TYPO3_DB;

            $fields = 'label_' . $this->conf['lang'] . ' as label, abbrevation';
            $result = $TYPO3_DB->exec_SELECTquery($fields, 'tx_mrastp_canton', 'uid=' . $cantonid);
            $row = $TYPO3_DB->sql_fetch_assoc($result);
            return $row;
        }

        function helperMembersAlphabet($show) {
            $items = array('alle', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
            $links = array();
	    $content = '<table class="contenttable fancytable"><tr>';
            foreach ($items as $item) {
		$tdclass = ($show == $item) ? ' class="selected"' : '';
                $content.= '<td' . $tdclass . ' style="width: 3%"><b>' . $this->pi_linkTP_keepPIvars($item,$overrulePIvars=array('show' => $item)) . '</b></td>';
            }
	    $content.= '</tr></table>';
            return $content;
        }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi1/class.tx_mrastp_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/pi1/class.tx_mrastp_pi1.php']);
}

?>