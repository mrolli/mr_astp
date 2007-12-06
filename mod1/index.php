<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007  <>
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
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


// DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');

$LANG->includeLLFile('EXT:mr_astp/mod1/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]



/**
 * Module 'astp Database' for the 'mr_astp' extension.
 *
 * @author	Michael Rolli <michael@rollis.ch>
 * @package	TYPO3
 * @subpackage	mr_astp
 */
class  mr_astp_module1 extends t3lib_SCbase {
    var $pageinfo;

    /**
     * Initializes the Module
     * @return	void
     */
    function init()	{
        global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA,$CLIENT, $TYPO3_CONF_VARS;

        $this->conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['mr_astp']);
        $this->id = $this->conf['pid_astp'];
        $TYPO3_DB->debugOutput = $this->conf['debug'];

        $this->tableLayout = array();
        $this->tableLayout['zebra'] = array('table'      => array('<table style="width: 100%; border-collapse: collapse; margin: 10px 5px; border: 1px solid #666666;">', '</table'),
                                            'defRowOdd'  => array('tr' => array('<tr style="background: #dddddd">', '</tr>'),
                                                                  'defCol' => array('<td style="padding: 3px">', '</td>'),
                                                                 ),
                                            'defRowEven' => array('tr' => array('<tr>', '</tr>'),
                                                                  'defCol' => array('<td style="padding: 3px">', '</td>'),
                                                                 ),
                                            0            => array('tr' => array('<tr style="background: #dddddd">', '</tr>'),
                                                                  'defCol' => array('<td style="padding: 3px; font-weight: bold; border-bottom: 1px solid #666666;">', '</td>'),
                                                                 ),
                                           );

        parent::init();

        /*
        if (t3lib_div::_GP('clear_all_cache'))	{
            $this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
        }
        */
    }

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return	void
     */
    function menuConfig()	{
        global $LANG;
        $this->MOD_MENU = array (
                            'function' => array (
                                             '1' => $LANG->getLL('members'),
                                             '2' => $LANG->getLL('groups'),
                                             '3' => $LANG->getLL('reports'),
                                             '4' => $LANG->getLL('backups'),
                                          ),
                          );
        parent::menuConfig();
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
     *
     * @return	void
     */
    function main()	{
        global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

        // Access check!
        // The page will show only if there is a valid page and if this page may be viewed by the user
        $this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
        $access = is_array($this->pageinfo) ? 1 : 0;

        if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

            // Draw the header.
            $this->doc = t3lib_div::makeInstance('mediumDoc');
            $this->doc->backPath = $BACK_PATH;
            $this->doc->form='<form action="" method="POST">';

            // JavaScript
            $this->doc->JScode = '
                            <script language="javascript" type="text/javascript">
                                script_ended = 0;
                                function jumpToUrl(URL)	{
                                    document.location = URL;
                                }
                            </script>';
            $this->doc->postCode='
                            <script language="javascript" type="text/javascript">
                                script_ended = 1;
                                if (top.fsMod) top.fsMod.recentIds["web"] = 0;
                            </script>';

            $headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

            $this->content .= $this->doc->startPage($LANG->getLL('title'));
            $this->content .= $this->doc->header($LANG->getLL('title'));
            $this->content .= $this->doc->spacer(5);
            $this->content .= $this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
            $this->content .= $this->doc->divider(5);

            // Render content:
            $this->moduleContent();

            // ShortCut
            if ($BE_USER->mayMakeShortcut())	{
                $this->content .= $this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
            }

            $this->content .= $this->doc->spacer(10);

        } else {
            // If no access or if ID == zero
            $this->doc = t3lib_div::makeInstance('mediumDoc');
            $this->doc->backPath = $BACK_PATH;

            $this->content .= $this->doc->startPage($LANG->getLL('title'));
            $this->content .= $this->doc->header($LANG->getLL('title'));
            $this->content .= $this->doc->spacer(5);
            $this->content .= $this->doc->spacer(10);
        }
    }

    /**
     * Prints out the module HTML
     *
     * @return	void
     */
    function printContent()	{
        $this->content .= $this->doc->endPage();
        echo $this->content;
    }

    /**
     * Generates the module content
     *
     * @return	void
     */
    function moduleContent()	{
        global $LANG;
        switch((string)$this->MOD_SETTINGS['function'])	{
            case 1:
                $this->content.=$this->doc->section($LANG->getLL('members_view') . ':', $this->createMemberView(), 0, 1);
                break;
            case 2:
                $this->content.=$this->doc->section($LANG->getLL('groups_view') . ':', $this->createGroupView(), 0, 1);
                break;
            case 3:
                $this->content.=$this->doc->section($LANG->getLL('lists_view') . ':', $this->createListView(), 0, 1);
                break;
            case 4:
                $this->content.=$this->doc->section($LANG->getLL('lists_view') . ':', $this->createBackupView(), 0, 1);
                /*
                $content='<div align=center><strong>Menu item #3...</strong></div>';
                $this->content.=$this->doc->section('Message #3:',$content,0,1);
                */
                break;
        }
        /*
        $this->content.= '<hr /><br />This is the GET/POST vars sent to the script:<br />'
                       . 'GET:'.t3lib_div::view_array($_GET).'<br />'
                       . 'POST:'.t3lib_div::view_array($_POST) . '<br />' . t3lib_div::debug($this->conf);
        */
    }

    /**
     * Generates Member View
     *
     * @return	void
     */
    function createMemberView() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;
        $userlang = $BE_USER->lang;


        $params='&edit[tx_mrastp_person][' . $this->id . ']=new';
        $content = '<a href="#" onclick="'.
                        htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')).'">';
        $content.='<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_record.gif','width="11" height="12"').' title="'.$LANG->getLL('new_record',1).'" class="absmiddle" alt="" /> ' . $LANG->getLL('new_record') . '</a>';
        $content.= $this->helperMembersAlphabet();

        // define statement parts
        $select  = 'uid, firstname, name, zip, city';
        $from    = 'tx_mrastp_person';
        $where   = (isset($_GET['show']) && t3lib_div::_GET('show') != 'alle') ? " name like '" . t3lib_div::_GET('show') . "%'" : '1=1';
        $where  .= ' ' . t3lib_BEfunc::deleteClause('tx_mrastp_person');
        $groupBy = '';
        $orderBy = 'name';

        // query database, get number of rows and fill in an array
        $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);
        $num_rows = $TYPO3_DB->sql_num_rows($result);

        $tableRows = array();
        $tableRows[] = array('',
                             '<b>' . $LANG->getLL('lastname') .  '</b>',
                             '<b>' . $LANG->getLL('firstname') . '</b>',
                             '<b>' . $LANG->getLL('zip') .       '</b>',
                             '<b>' . $LANG->getLL('city') .      '</b>',
                            );
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $params='&edit[tx_mrastp_person]['.$row['uid'].']=edit';
            $tableRows[] = array('<a href="#" onclick="' .
                                     htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')) . '">' .
                                     '<img' . t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"') . ' title="' . $LANG->getLL('edit',1) . '" class="absmiddle" alt="" /></a>',
                                 $row['name'],
                                 $row['firstname'],
                                 $row['zip'],
                                 $row['city'],
                                 );
        }

        // assemble output
        $content.= '<p><b>' . $LANG->getLL('members_found') . ': ' . $num_rows . '</b><br />';
        $content.= $this->doc->table($tableRows, $this->tableLayout['zebra']);
        $content.= '</p>';

        return $content;
    }

    /**
     * Generates Groups View
     */
    function createGroupView() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

        $params='&edit[tx_mrastp_group][' . $his->id . ']=new';
        $content = '<a href="#" onclick="'.
                        htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')).'">';
        $content.='<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_record.gif','width="11" height="12"').' title="'.$LANG->getLL('new_record',1).'" class="absmiddle" alt="" /> ' . $LANG->getLL('new_record') . '</a>';

        // define statement parts
        $select  = 'uid, label_de, label_fr, persons';
        $from    = 'tx_mrastp_group';
        $where  .= '1=1' . t3lib_BEfunc::deleteClause('tx_mrastp_group');
        $groupBy = '';
        // ordering depends on backend user's selected language
        switch($BE_USER->uc['lang']) {
            case 'fr':
                $orderBy = $label = 'label_fr';
                break;
            default:
                $orderBy = $label = 'label_de';
        }


        $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);

        $tableRows = array();
        $tableRows[] = array('',
                             '<b>' . $LANG->getLL('group')   . '</b>',
                             '<b>' . $LANG->getLL('members') . '</b>',
                            );
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $params='&edit[tx_mrastp_group]['.$row['uid'].']=edit';
            $tableRows[] = array('<a href="#" onclick="'.
                                     htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')).'">' .
                                     '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('edit',1).'" class="absmiddle" alt="" /></a>',
                                 $row[$label],
                                 $row['persons'],
                                );
        }
        $content.= '<p>' . $this->doc->table($tableRows, $this->tableLayout['zebra']) . '</p>';
        return $content;
    }

    /**
     * Generates List View
     */
    function createListView() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

        $action = t3lib_div::_GP('list_action');
        if($action) {
            $this->action = t3lib_div::_GP('list_action');
            $this->list = t3lib_div::_GP('list');
            $this->processList();
        }

/*
        $tableRows = array();

        $tableRows = array_merge($tableRows, $this->getGroupsArray());
        $tableRows = array_merge($tableRows, $this->getCantonsArray());
*/
        // other additional lists
        $miscLists   = array();
        $miscLists[] = array('<b>' . $LANG->getLL('lists') . '</b>',
                             '<b>' . $LANG->getLL('show') . '</b>',
                             '<b>' . $LANG->getLL('download') . '</b>'
                            );
        $miscLists[] = array('IV-Liste',
                             '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/viewdok.gif','width="11" height="12"').' title="'.$LANG->getLL('view').'" class="absmiddle" alt="" />',
                             '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/csv.gif','width="11" height="12"').' title="'.$LANG->getLL('download').'" class="absmiddle" alt="" />',
                            );

        $content = '';
        $content.= $this->doc->section($LANG->getLL('group_comm'), $this->doc->table($this->getGroupsArray(), $this->tableLayout['zebra']), 1, 0);
        $content.= $this->doc->section($LANG->getLL('cantons'), $this->doc->table($this->getCantonsArray(), $this->tableLayout['zebra']), 1, 0);
        $content.= $this->doc->section($LANG->getLL('misc_lists'), $this->doc->table($miscLists, $this->tableLayout['zebra']), 1, 0);
        return $content;
    }

    /**
     * Get array of all groups available
     */
    function getGroupsArray() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

        // define statement parts
        $select = 'uid, label_de, label_fr';
        $from = 'tx_mrastp_group';
        $where.= '1=1' . t3lib_BEfunc::deleteClause('tx_mrastp_group');
        $groupBy = '';
        // ordering depends on backend user's selected language
        switch($BE_USER->uc['lang']) {
            case 'fr':
                $orderBy = $label = 'label_fr';
                break;
            default:
                $orderBy = $label = 'label_de';
        }

        $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);

        $tableRows   = array();
        $tableRows[] = array('<b>' . $LANG->getLL('lists') . '</b>',
                             '<b>' . $LANG->getLL('show') . '</b>',
                             '<b>' . $LANG->getLL('download') . '</b>'
                            );
        /*
        $tableRows[] = array('<b>' . $LANG->getLL('group_comm') .  '</b>',
                             '',
                             '',
                            );
        */
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $tableRows[] = array($row[$label],
                                 '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/viewdok.gif','width="11" height="12"').' title="'.$LANG->getLL('view').'" class="absmiddle" alt="" />',
                                 '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/csv.gif','width="11" height="12"').' title="'.$LANG->getLL('download').'" class="absmiddle" alt="" />',
                                );
        }
        return $tableRows;
    }

    /**
     * Get array of all cantons available
     */
	function getCantonsArray() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

        // define statement parts
        $select = 'uid, label_de, label_fr, label_en';
        $from = 'tx_mrastp_canton';
        $where.= '1=1' . t3lib_BEfunc::deleteClause('tx_mrastp_canton');
        $groupBy = '';
        // ordering depends on backend user's selected language
        switch($BE_USER->uc['lang']) {
            case 'en';
                $rderBy = $label = 'label_en';
                break;
            case 'fr':
                $orderBy = $label = 'label_fr';
                break;
            default:
                $orderBy = $label = 'label_de';
        }

        $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);

        $tableRows   = array();
        $tableRows[] = array('<b>' . $LANG->getLL('lists') . '</b>',
                             '<b>' . $LANG->getLL('show') . '</b>',
                             '<b>' . $LANG->getLL('download') . '</b>'
                            );
        /*
        $tableRows[] = array('<b>' . $LANG->getLL('cantons') . '</b>',
                             '',
                             '',
                            );
        */
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $tableRows[] = array($row[$label],
                                 '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/viewdok.gif','width="11" height="12"').' title="'.$LANG->getLL('view').'" class="absmiddle" alt="" />',
                                 '<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/csv.gif','width="11" height="12"').' title="'.$LANG->getLL('download').'" class="absmiddle" alt="" />',
                                );
        }
        return $tableRows;
	}

	function renderHtmlList($rows, $fields, $heading) {
	}

	function renderCsvList($rows, $fields, $heading) {
	}

	function helperMembersAlphabet() {
		$items = array('alle', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$links = array();
		foreach ($items as $item) {
		    $links[] = '<a href="/' . PATH_typo3_mod . '?show=' . $item . '">' . $item . '</a>';
		}
		return '<div style="width: 60%; margin: 10px 5px">' . implode(' | ', $links) . '</div>';
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/mod1/index.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('mr_astp_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();
