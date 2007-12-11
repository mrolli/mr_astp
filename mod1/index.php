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
        $this->extKey = 'mr_astp';
        $this->db['locallang_db'] = t3lib_div::readLLfile(t3lib_extMgm::extPath($this->extKey).'locallang_db.php',$BE_USER->uc['lang']);
        $this->db['prefix'] = 'tx_mrastp_';
        $this->db['tables'] = array('person', 'workaddress', 'salutation', 'canton', 'section', 'state', 'language', 'country', 'persons_groups_rel', 'group', 'group_cat');
        foreach($this->db['tables'] as $key => $table) {
            $tableName = $this->db['prefix'] . $table;
            t3lib_div::loadTCA($tableName);
            $this->db['tca'][$tableName] = $TCA[$tableName];
            $this->db['tables'][$table] = $tableName;
            unset($this->db['tables'][$key]);
        }
        $this->db['field_groups'] = array();
        $this->db['field_groups']['group_private'] = array('tx_mrastp_salutation.label_%s as salutation_label', 'tx_mrastp_person.firstname', 'tx_mrastp_person.name', 'tx_mrastp_person.street', 'tx_mrastp_person.compl', 'tx_mrastp_person.zip', 'tx_mrastp_person.city', 'tx_mrastp_canton.label_%s as canton_label', 'tx_mrastp_country.cn_short_%s as country_label');
        $this->db['field_groups']['group_private_com'] = array('tx_mrastp_person.phone', 'tx_mrastp_person.mobile', 'tx_mrastp_person.fax', 'tx_mrastp_person.email');
        $this->db['field_groups']['group_section'] = array('tx_mrastp_section.label_%s as section_label');
        $this->db['field_groups']['group_status'] = array('tx_mrastp_state.label_%s as state_label');
        $this->db['field_groups']['group_language'] = array('tx_mrastp_language.label_%s as language_label');
        $this->db['sortable_fields'] = array('tx_mrastp_canton.label_%s', 'tx_mrastp_language.label_%s', 'tx_mrastp_state.label_%s');

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
        $this->tableLayout['xls'] = array('table'      => array('<table>', '</table'),
                                          'defRow'     => array('<tr>' => array('<tr>', '</tr>'),
                                                                'defCol' => array('<td>', '</td>'),
                                                               ),
                                          0            => array('defCol' => array('<th>', '</th>')),
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
                                             '4' => $LANG->getLL('custom_reports'),
                                             '5' => $LANG->getLL('backups'),
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
        $this->id = $this->conf['pid_astp'];
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
                $this->content.=$this->doc->section($LANG->getLL('custom_reports') . ':', $this->createCustomReportsView(), 0, 1);
                break;
            case 5:
                $this->content.=$this->doc->section($LANG->getLL('backup_view') . ':', $this->createBackupView(), 0, 1);
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
/*
        $action = t3lib_div::_GP('list_action');
        if($action) {
            $this->action = t3lib_div::_GP('list_action');
            $this->list = t3lib_div::_GP('list');
            $this->processList();
        }
        $tableRows = array();

        $tableRows = array_merge($tableRows, $this->getGroupsArray());
        $tableRows = array_merge($tableRows, $this->getCantonsArray());

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
*/
        return 'kommt in neuem Kleid wieder';
    }

    function createCustomReportsView() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;
        $content = '';
        $post = array();

        if(count($_POST) > 0) {
            $post = t3lib_div::_POST();
            $filters = array();
            $selects = array();
            $orderBy = array();
            foreach ($post as $field => $value) {
                $field = $this->fkDecode($field);
                switch($field) {
                    case 'format':
                    case 'submit':
                    case 'SET':
                        break;
                    case 'tx_mrastp_person.canton_id':
                    case 'tx_mrastp_person.language_id':
                    case 'tx_mrastp_person.status':
                    case 'tx_mrastp_workaddress.employment':
                    case 'tx_mrastp_person.section_id':
                    case 'tx_mrastp_persons_groups_rel.groupid':
                    case 'tx_mrastp_person.city':
                        if(!empty($value)) {
                            $filters[$field] = $value;
                        }
                        break;
                    case 'sorting_field':
                        $orderBy = $post['sorting_field'];
                        break;
                    default:
                        $selects[$field] = $value;
                }
            }
            if(isset($post['format'])) {
                switch($post['format']) {
                    case 'html':
                        $content.= $this->renderHtmlList($this->generateReport($selects, $filters, $orderBy), array());
                        break;
                    case 'xls':
                        $content = $this->renderXlsFile($this->generateReport($selects, $filters, $orderBy));
                        $headers = array('application/vnd-ms-excel');
                        $this->sendFile($content, $headers);
                }
            }

        }
        $content = $this->getReportGeneratorForm($post) . $content;
        return $content;
    }

    function createBackupView() {
        return 'to be done';
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
                $orderBy = $label = 'label_en';
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

	function renderHtmlList($rows, $heading='') {
        $content = $this->doc->table($rows, $this->tableLayout['zebra']);
        return $content;
	}

	function renderXlsFile($rows) {
	    $content = $this->doc->table($rows, $this->tableLayout['xls']);
	    return $content;
	}

	function getReportGeneratorForm($post) {
	    global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

        $content = '<form action="" method="post" enctype="multipart/form-data">';
        $content.= '<fieldset><legend><b>' . $LANG->getLL('filters') . '</b></legend>';
        $content.= '<table>';
        $content.= '<tr><td><label for="city">' . $this->getDbLL($BE_USER->uc['lang'], $this->db['tables']['person'], 'city') . ': </label></td>';
        $content.= '<td><input id="city" name="tx_mrastp_person|city" value="' . (isset($post['tx_mrastp_person|city']) ? $post['tx_mrastp_person|city'] : '') . '" /></td></tr>';
        $content.= '<tr>' . $this->getSelectOfTable('canton', 'tx_mrastp_person|canton_id', (isset($post['tx_mrastp_person|canton_id']) ? $post['tx_mrastp_person|canton_id'] : false)) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('state', 'tx_mrastp_person|status', (isset($post['tx_mrastp_person|status']) ? $post['tx_mrastp_person|status'] : false)) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('language', 'tx_mrastp_person|language_id', (isset($post['tx_mrastp_person|language_id']) ? $post['tx_mrastp_person|language_id'] : false)) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('section', 'tx_mrastp_person|section_id', (isset($post['tx_mrastp_person|section_id']) ? $post['tx_mrastp_person|section_id'] : false)) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('group', 'tx_mrastp_persons_groups_rel|groupid', (isset($post['tx_mrastp_persons_groups_rel|groupid']) ? $post['tx_mrastp_persons_groups_rel|groupid'] : false)) . '</tr>';
        $content.= '</table>';
        $content.= '</fieldset><fieldset style="margin-top: 10px"><legend><b>' . $LANG->getLL('output_params') . '</b></legend>';
        $content.= '<fieldset><legend>' . $LANG->getLL('output_fields') . '</legend><table>';
        $content.= $this->generateRadioSwitch('group_private', (isset($post['group_private']) ? $post['group_private'] : 1));
        $content.= $this->generateRadioSwitch('group_private_com', (isset($post['group_private_com']) ? $post['group_private_com'] : 0));
        $content.= $this->generateRadioSwitch('group_section', (isset($post['group_section']) ? $post['group_section'] : 0));
        $content.= $this->generateRadioSwitch('group_status', (isset($post['group_status']) ? $post['group_status'] : 1));
        $content.= $this->generateRadioSwitch('group_language', (isset($post['group_language']) ? $post['group_language'] : 1));
        $content.= '</table></fieldset>';
        $content.= '<fieldset><legend>' . $LANG->getLL('output_sorting') . '</legend><table>';
        for($i=0;$i<3;$i++) {
            $content.= '<tr><td>' . $LANG->getLL('sorting_field') . ' ' . ($i+1) . '</td><td>' . $this->getSelectOfOrderBys($post['sorting_field'][$i]) . '</td></tr>';
        }
        $content.= '</table></fieldset><fieldset><legend>' . $LANG->getLL('output_others') . '</legend>';
        $content.= '<table><tr><td><label>' . $LANG->getLL('output_format') . '</label></td>';
        $content.= '<td><input type="radio" id="html" name="format" value="html"  checked="checked" /><label for="html">' . $LANG->getLL('output_format_html') . '</label></td>';
        $content.= '<td><input type="radio" id="xls" name="format" value="xls" /><label for="xls">' . $LANG->getLL('output_format_xls') . '</label></td></tr></table>';
        $content.= '</fieldset>';
        $content.= '<input type="submit" name="submit" value="' . $LANG->getLL('form_generate') . '" />';
        $content.= '</fieldset></form>';
        return $content;
	}

    function getSelectOfOrderBys($preselect=false) {
        global $BE_USER, $LANG;
        $content = '<select id="sorting_field" name="sorting_field[]" size="1">';
        $content.= '<option value=""></option>';
        foreach ($this->db['sortable_fields'] as $field) {
            $fieldParts = explode('.', $field);
            $tableName = $fieldParts[0];
            $fieldName = $fieldParts[1];
            $encodedField = $this->fkEncode(sprintf($field, $BE_USER->uc['lang']));
            $selected = ($preselect && $preselect == $encodedField) ? ' selected="selected"' : '';
            $content.= '<option value="' . $encodedField . '" ' . $selected . '>' . $this->getDbLL($BE_USER->uc['lang'], $tableName) . '</option>';
        }
        $content.= '</select>';
        return $content;
    }

	function getSelectOfTable($table, $fkField, $preselect) {
	    global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;
	    $userLang = $BE_USER->uc['lang'];
	    switch($userLang) {
	        case 'en':
	            $label = 'label_en';
	            if($table != 'canton') {
	                $label = 'label_de';
	            }
	            break;
	        case 'fr':
	            $label = 'label_fr';
	            break;
	        default:
	            $label = 'label_de';
	            break;
	    }

        $select = 'uid, ' . $label . ' as label';
        $from = $this->db['tables'][$table];
        $where = '1=1' . t3lib_BEfunc::deleteClause($from);
	    $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, '', $label);

	    $output = '<td><label for="' . $table . '">' . $this->getDbLL($BE_USER->uc['lang'], $from) . ': </label></td>';
	    $output.= '<td><select id="' . $table . '" name="' . $fkField . '" size="1">'; // multiple="multiple">';
	    $output.= '<option value="0"></option>';
	    while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $selected = '';
            if($preselect == $row['uid']) {
                $selected = ' selected="selected"';
            }
	        $output.= '<option value="' . $row['uid'] . '"' . $selected . '>' . $row['label'] . '</option>';
	    }
	    $output.= '</select></td>';
	    return $output;
	}

	function generateFieldSwitch($table, $field) {
        global $LANG, $BE_USER;

        $field = $this->fkEncode($field);
	    $xhtml.= '<tr><td><label>' . $this->getDbLL($BE_USER->uc['lang'], $table, $field) . '</label></td>';
	    $xhtml.= '<td><input type="radio" id="' . $table . '|' . $field . '" name="' . $table . '|' . $field . '" value="1" checked="checked"/>';
	    $xhtml.= '<label for="' . $table . '|' . $field . '">' . $LANG->getLL('yes') . '</label></td>';
        $xhtml.= '<td><input type="radio" id="' . $table . '|' . $field . '" name="' . $table . '|' . $field . '" value="0" /> ';
        $xhtml.= '<label for="' . $table . '|' . $field . '">' . $LANG->getLL('no') . '</label></td></tr>';
        return $xhtml;
	}

	function generateRadioSwitch($name, $preselect) {
	    global $LANG, $BE_USER;

	    $xhtml.= '<tr><td><label>' . $LANG->getLL($name) . '</label></td>';
	    $xhtml.= '<td><input type="radio" id="' . $name . '" name="' . $name . '" value="1" ' . ($preselect ? ' checked="checked"' : '') . '/>';
	    $xhtml.= '<label for="' . $name . '">' . $LANG->getLL('yes') . '</label></td>';
        $xhtml.= '<td><input type="radio" id="' . $name . '" name="' . $name . '" value="0" ' . (!$preselect ? ' checked="checked"' : '') . '/> ';
        $xhtml.= '<label for="' . $name . '">' . $LANG->getLL('no') . '</label></td></tr>';
        return $xhtml;
	}

	function generateReport($selects=false, $filters=false, $sortings=false) {
        global $TYPO3_DB, $BE_USER;
        $select = $from = $join = $where = $groupBy = $orderBy = $limit = '';
        $fromTables = array();

        foreach ($filters as $field => $value) {
            list($table, $column) = explode('.', $field);
            if(in_array($table, $this->db['tables'])) {
                $join.= $this->getRelationWhere($table . '.' . $column);
                if(is_array($this->db['tca'][$table]['columns'][$column])) {
                    if(!empty($value)) {
                        $where.= strlen($where) == 0 ? $field . "='" . $value . "'" : ' AND ' . $field . "='" . $value . "'";
                    }
                }
            }
        }

        foreach ($selects as $field => $value) {
            if((int) $value == 1) {
                if(preg_match('/^group_/', $field)) {
                    foreach ($this->db['field_groups'][$field] as $real_field) {
                        $select.= ($select == '') ? sprintf($real_field, $BE_USER->uc['lang']) : ', ' . sprintf($real_field, $BE_USER->uc['lang']);
                        list($table, $column) = explode('.', $real_field);
                        if($table != 'tx_mrastp_person' && !preg_match('/' . $table . '/', $join)) {
                            $join.= $this->getRelationWhere($table);
                        }
                    }
                } else {
                    $select.= ($select == '') ? $field : ', ' . $field;
                }
            }
        }
        if($select == '') {
            $select = 'tx_mrastp_person.firstname, tx_mrastp_person.name';
        }
        foreach ($sortings as $field) {
            if(!empty($field)) {
                $field = $this->fkDecode($field);
                list($table, $column) = explode('.', $field);
                if($table != 'tx_mrastp_person' && !preg_match('/' . $table . '/', $join)) {
                    $join.= $this->getRelationWhere($table);
                }
                $orderBy.= ($orderBy == '') ? ' ORDER BY ' . $field : ', ' . $field;
            }
        }
        $orderBy.= ($orderBy == '') ? ' ORDER BY name' : ', name';

        if (!$where) {
            $where = '1=1';
        }

        $sql = 'SELECT ' . $select . ' FROM tx_mrastp_person ' . $join . ' WHERE ' . $where . $orderBy;
        $result = $TYPO3_DB->admin_query($sql);

        $tableRows = array();
        $splitSelect = explode(', ', $select);
        foreach($splitSelect as $tableField) {
            $fieldParts = explode('.', $tableField);
            $tableName = $fieldParts[0];
            $fieldName = preg_replace('/.* as (.*)/', '\1', $fieldParts[1]);
            $tableRows[0][] = utf8_encode($this->getDbLL($BE_USER->uc['lang'], $tableName, $fieldName));
        }
        while ($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $tableRows[] = $row;
        }
        return $tableRows;
	}

	function getRelationWhere($table) {
	    switch($table) {
            case 'tx_mrastp_persons_groups_rel.groupid':
            case 'tx_mrastp_persons_groups_rel':
            case 'tx_mrastp_group':
                return ' LEFT JOIN tx_mrastp_persons_groups_rel ON tx_mrastp_person.uid = tx_mrastp_persons_groups_rel.personid LEFT JOIN tx_mrastp_group ON tx_mrastp_persons_groups_rel.groupid = tx_mrastp_group.uid';
                break;
	        case 'tx_mrastp_person.canton_id':
	        case 'tx_mrastp_canton':
	            return ' LEFT JOIN tx_mrastp_canton ON tx_mrastp_person.canton_id = tx_mrastp_canton.uid';
	            break;
	        case 'tx_mrastp_person.status':
	        case 'tx_mrastp_state':
	            return ' LEFT JOIN tx_mrastp_state ON tx_mrastp_person.status = tx_mrastp_state.uid';
	            break;
	        case 'tx_mrastp_person.section_id':
	        case 'tx_mrastp_section':
	            return ' LEFT JOIN tx_mrastp_section ON tx_mrastp_person.section_id = tx_mrastp_section.uid';
	            break;
	        case 'tx_mrastp_person.country_id':
	        case 'tx_mrastp_country':
	            return ' LEFT JOIN tx_mrastp_country ON tx_mrastp_person.country_id = tx_mrastp_country.uid';
	            break;
            case 'tx_mrastp_person.salutation_id':
            case 'tx_mrastp_salutation':
                return ' LEFT JOIN tx_mrastp_salutation ON tx_mrastp_person.salutation_id = tx_mrastp_salutation.uid';
                break;
            case 'tx_mrastp_person.language_id':
            case 'tx_mrastp_language':
                return ' LEFT JOIN tx_mrastp_language ON tx_mrastp_person.language_id = tx_mrastp_language.uid';
                break;
            case 'tx_mrastp_country':
                return ' LEFT JOIN tx_mrastp_country ON tx_mrastp_person.country_id = tx_mrastp_country.uid';
                break;
	        default:
	            return '';
	    }
	}

	function helperMembersAlphabet() {
		$items = array('alle', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
		$links = array();
		foreach ($items as $item) {
		    $links[] = '<a href="/' . PATH_typo3_mod . '?show=' . $item . '">' . $item . '</a>';
		}
		return '<div style="width: 60%; margin: 10px 5px">' . implode(' | ', $links) . '</div>';
	}

	function getDbLL($lang, $table, $column=false) {
	    if($lang === 'en') {
	        $lang = 'default';
	    }
	    if(!$column) {
	        $llPointer = $this->db['tca'][$table]['ctrl']['title'];
	    } else {
            if(isset($this->db['tca'][$table]['columns'][$column]['label'])) {
	            $llPointer = $this->db['tca'][$table]['columns'][$column]['label'];
            } else {
                $llPointer = 'LLL:EXT:mr_astp/locallang_db.xml:' . $table . '.' . $column;
            }
	    }
	    $llParts = explode(':', $llPointer);
	    $label = $llParts[3];
	    return $this->db['locallang_db'][$lang][$label];
	}

	function sendFile($content, $headers) {
        $export_file = 'astp-Adressliste_' . date('Y-m-d_H-m-s') . '.xls';
        header('Pragma: public');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Pragma: no-cache');
        header('Content-Transfer-Encoding: none');
        foreach ($headers as $header) {
            header($header);
        }
        header('Content-Disposition: attachment; filename="' . basename($export_file) . '"');
	    echo $content; exit;
	}

    function fkEncode($string) {
        return str_replace('.', '|', $string);
    }

    function fkDecode($string) {
        return str_replace('|', '.', $string);
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
