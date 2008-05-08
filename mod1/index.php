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

set_include_path(t3lib_extMgm::extPath('mr_astp') . '/library' . PATH_SEPARATOR . t3lib_extMgm::extPath('mr_astp') . '/mod1' . PATH_SEPARATOR . get_include_path());

/**
 * Module 'astp Database' for the 'mr_astp' extension.
 *
 * @author	Michael Rolli <michael@rollis.ch>
 * @package	TYPO3
 * @subpackage	mr_astp
 */
class mr_astp_module1 extends t3lib_SCbase {
    var $pageinfo;

    /**
     * Initializes the Module
     * @return	void
     */
    function init()	{
        global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA,$CLIENT, $TYPO3_CONF_VARS, $TYPO3_DB;

        $this->include_once[] = 'Zend/Mail.php';
        $this->include_once[] = 'Form_Massmail.php';
        $this->include_once[] = 'Zend/Mail/Transport/Sendmail.php';

        $this->conf = unserialize($TYPO3_CONF_VARS['EXT']['extConf']['mr_astp']);
        if($this->conf['debug']) {
            ini_set('display_errors', true);
            $TYPO3_DB->debugOutput = 1;
        }
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
        $this->db['field_groups']['group_private'] = array('tx_mrastp_salutation.label_%s as salutation_label', 'tx_mrastp_person.firstname', 'tx_mrastp_person.name', 'tx_mrastp_person.street', 'tx_mrastp_person.compl', 'tx_mrastp_person.zip as private_zip', 'tx_mrastp_person.city as private_city', 'pc.label_%s as private_canton', 'pl.cn_short_%s as private_country');
        $this->db['field_groups']['group_private_com'] = array('tx_mrastp_person.phone as private_phone', 'tx_mrastp_person.mobile as private_mobile', 'tx_mrastp_person.fax as private_fax', 'tx_mrastp_person.email as private_email');
        $this->db['field_groups']['group_work'] = array('tx_mrastp_workaddress.name_practice', 'tx_mrastp_workaddress.name_supplement', 'tx_mrastp_workaddress.address1', 'tx_mrastp_workaddress.address2', 'tx_mrastp_workaddress.zip as work_zip', 'tx_mrastp_workaddress.city as work_city', 'wc.label_%s as work_canton', 'wl.cn_short_%s as work_country');
        $this->db['field_groups']['group_work_com'] = array('tx_mrastp_workaddress.phone as work_phone', 'tx_mrastp_workaddress.mobile as work_mobile', 'tx_mrastp_workaddress.fax as work_fax', 'tx_mrastp_workaddress.email as work_email');
        $this->db['field_groups']['group_section'] = array('tx_mrastp_section.label_%s as section_label');
        $this->db['field_groups']['group_status'] = array('tx_mrastp_state.label_%s as state_label');
        $this->db['field_groups']['group_language'] = array('tx_mrastp_language.label_%s as language_label');
        $this->db['field_groups']['group_auditval'] = array('tx_mrastp_person.uid', 'tx_mrastp_section.label_%s as section_label', 'tx_mrastp_state.label_%s as state_label', 'tx_mrastp_salutation.label_%s as salutation_label', 'tx_mrastp_person.firstname', 'tx_mrastp_person.name', 'tx_mrastp_person.street', 'tx_mrastp_person.compl', 'tx_mrastp_person.zip as private_zip', 'tx_mrastp_person.city as private_city', 'pc.label_%s as private_canton', 'pl.cn_short_%s as private_country', 'tx_mrastp_person.phone as private_phone', 'tx_mrastp_person.mobile as private_mobile', 'tx_mrastp_person.fax as private_fax', 'tx_mrastp_person.email as private_email');
        $this->db['field_groups']['group_employment'] = array('tx_mrastp_workaddress.employment');
        $this->db['sortable_fields'] = array('private_canton', 'work_canton', 'tx_mrastp_language.label_%s', 'tx_mrastp_state.label_%s');

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
                                             '3' => $LANG->getLL('workaddresses'),
                                             '4' => $LANG->getLL('reports'),
                                             '5' => $LANG->getLL('custom_reports'),
                                             '6' => $LANG->getLL('backups'),
                                             '7' => $LANG->getLL('massmailer'),
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

        // default mail transport
        $tr = new Zend_Mail_Transport_Sendmail('-fbounces@astp.ch');
        Zend_Mail::setDefaultTransport($tr);

        // Access check!
        $this->id = $this->conf['pid_astp'];
        // The page will show only if there is a valid page and if this page may be viewed by the user
        $this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
        $access = is_array($this->pageinfo) ? 1 : 0;

        if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

            // Draw the header.
            $this->doc = t3lib_div::makeInstance('mediumDoc');
            $this->doc->styleSheetFile_post = "../".substr(t3lib_extMgm::extPath('mr_astp'),strlen(PATH_site)) . "mod1/style.css";
            $this->doc->backPath = $BACK_PATH;
            //$this->doc->form='<form action="" method="POST">';

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
                $this->content.=$this->doc->section($LANG->getLL('workaddress_view') . ':', $this->createWorkaddressView(), 0, 1);
                break;
            case 4:
                $this->content.=$this->doc->section($LANG->getLL('lists_view') . ':', $this->createListView(), 0, 1);
                break;
            case 5:
                $this->content.=$this->doc->section($LANG->getLL('custom_reports') . ':', $this->createCustomReportsView(), 0, 1);
                break;
            case 6:
                $this->content.=$this->doc->section($LANG->getLL('backup_view') . ':', $this->createBackupView(), 0, 1);
                /*
                $content='<div align=center><strong>Menu item #3...</strong></div>';
                $this->content.=$this->doc->section('Message #3:',$content,0,1);
                */
                break;
            case 7:
                $this->content.=$this->doc->section($LANG->getLL('massmailer') . ':', $this->createMassmailerView(), 0, 1);
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
        $userlang = $BE_USER->uc['lang'];


        $params='&edit[tx_mrastp_person][' . $this->id . ']=new';
        $content = '<a href="#" onclick="'.
                        htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')).'">';
        $content.='<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_record.gif','width="11" height="12"').' title="'.$LANG->getLL('new_record',1).'" class="absmiddle" alt="" /> ' . $LANG->getLL('new_record') . '</a>';
        $content.= $this->helperMembersAlphabet();

        // define statement parts
        $select  = 'uid, firstname, name, zip, city, hidden';
        $from    = 'tx_mrastp_person';
        $where   = (isset($_GET['show']) && t3lib_div::_GET('show') != 'alle') ? " name like '" . t3lib_div::_GET('show') . "%'" : '1=1';
        $where  .= ' ' . t3lib_BEfunc::deleteClause('tx_mrastp_person');
        $groupBy = '';
        $orderBy = 'name, firstname';

        // query database, get number of rows and fill in an array
        $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);
        $num_rows = $TYPO3_DB->sql_num_rows($result);

        $tableRows = array();
        $tableRows[] = array('',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_person', 'lastname') .  '</b>',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_person', 'firstname') . '</b>',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_person', 'zip') .       '</b>',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_person', 'city') .      '</b>',
                            );
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $params='&edit[tx_mrastp_person]['.$row['uid'].']=edit';
            $tableRows[] = array('<a href="#" onclick="' .
                                     htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')) . '">' .
                                     '<img' . t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"') . ' title="' . $LANG->getLL('edit',1) . '" class="absmiddle" alt="" /></a>',
                                 $this->grayout($row['name'], $row['hidden']),
                                 $this->grayout($row['firstname'], $row['hidden']),
                                 $this->grayout($row['zip'], $row['hidden']),
                                 $this->grayout($row['city'], $row['hidden']),
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
     * Generates addresslist view
     */
    function createWorkaddressView()
    {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;
        $userlang = $BE_USER->uc['lang'];


        $params='&edit[tx_mrastp_workaddress][' . $this->id . ']=new';
        $content = '<a href="#" onclick="'.
                        htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')).'">';
        $content.='<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_record.gif','width="11" height="12"').' title="'.$LANG->getLL('new_record',1).'" class="absmiddle" alt="" /> ' . $LANG->getLL('new_record') . '</a>';
        $content.= $this->helperCantonsAlphabet();

        // define statement parts
        $select  = 'uid, name_practice, name_supplement, zip, city, hidden';
        $from    = 'tx_mrastp_workaddress';
        $where   = (isset($_GET['canton_id']) && t3lib_div::_GET('canton_id') > -1) ? ' canton_id=' . (int) t3lib_div::_GET('canton_id') : '1=1';
        $where  .= t3lib_BEfunc::deleteClause('tx_mrastp_workaddress');
        $groupBy = '';
        $orderBy = 'zip, name_practice ASC';

        // query database, get number of rows and fill in an array
        $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);
        $num_rows = $TYPO3_DB->sql_num_rows($result);

        $tableRows = array();
        $tableRows[] = array('',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_workaddress', 'name_practice') .  '</b>',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_workaddress', 'name_supplement') . '</b>',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_workaddress', 'zip') .       '</b>',
                             '<b>' . $this->getDbLL($userlang, 'tx_mrastp_workaddress', 'city') .      '</b>',
                            );
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $params='&edit[tx_mrastp_workaddress]['.$row['uid'].']=edit';
            $tableRows[] = array('<a href="#" onclick="' .
                                     htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')) . '">' .
                                     '<img' . t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"') . ' title="' . $LANG->getLL('edit',1) . '" class="absmiddle" alt="" /></a>',
                                 $this->grayout($row['name_practice'], $row['hidden']),
                                 $this->grayout($row['name_supplement'], $row['hidden']),
                                 $this->grayout($row['zip'], $row['hidden']),
                                 $this->grayout($row['city'], $row['hidden']),
                                 );
        }

        // assemble output
        $content.= '<p><b>' . $LANG->getLL('addresses_found') . ': ' . $num_rows . '</b><br />';
        $content.= $this->doc->table($tableRows, $this->tableLayout['zebra']);
        $content.= '</p>';

        return $content;
    }

    /**
     * Generates List View
     */
    function createListView() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;

        $groups = array('Auditval' => array('mit_aktiv', 'mit_passiv', 'mit_ehren', 'mit_iv', 'mit_studi',
                                            'rom_aktiv', 'rom_passiv', 'rom_ehren', 'rom_iv', 'rom_studi',
                                            'ost_aktiv', 'ost_passiv', 'ost_ehren', 'ost_iv', 'ost_studi', 
                                            ),
                       );
        if (count($_GET) > 0) {
            $get = t3lib_div::_GET();
            if ($this->config['debug']) {
                t3lib_div::debug($get);
            }
            switch ($get['list']) {
                case 'mit_aktiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 1, 'tx_mrastp_person|status' => 1);
                    $sortings = array();
                    break;
                case 'mit_passiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 1, 'tx_mrastp_person|status' => 2);
                    $sortings = array();
                    break;
                case 'mit_passiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 1, 'tx_mrastp_person|status' => 2);
                    $sortings = array();
                    break;
                case 'mit_ehren':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 1, 'tx_mrastp_person|status' => 3);
                    $sortings = array();
                    break;
                case 'mit_iv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 1, 'tx_mrastp_person|status' => 4);
                    $sortings = array();
                    break;
                case 'mit_studi':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 1, 'tx_mrastp_person|status' => 5);
                    $sortings = array();
                    break;
                case 'rom_aktiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 3, 'tx_mrastp_person|status' => 1);
                    $sortings = array();
                    break;
                case 'rom_passiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 3, 'tx_mrastp_person|status' => 2);
                    $sortings = array();
                    break;
                case 'rom_ehren':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 3, 'tx_mrastp_person|status' => 3);
                    $sortings = array();
                    break;
                case 'rom_iv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 3, 'tx_mrastp_person|status' => 4);
                    $sortings = array();
                    break;
                case 'rom_studi':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 3, 'tx_mrastp_person|status' => 5);
                    $sortings = array();
                    break;
                case 'ost_aktiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 2, 'tx_mrastp_person|status' => 1);
                    $sortings = array();
                    break;
                case 'ost_passiv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 2, 'tx_mrastp_person|status' => 2);
                    $sortings = array();
                    break;
                case 'ost_ehren':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 2, 'tx_mrastp_person|status' => 3);
                    $sortings = array();
                    break;
                case 'ost_iv':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 2, 'tx_mrastp_person|status' => 4);
                    $sortings = array();
                    break;
                case 'ost_studi':
                    $selects = array('group_auditval' => 1);
                    $filters = array('tx_mrastp_person|section_id' => 2, 'tx_mrastp_person|status' => 5);
                    $sortings = array();
                    break;
            }
            if(isset($get['format'])) {
                switch($get['format']) {
                    case 'html':
                        $content.= $this->renderHtmlList($this->generateReport($selects, $filters, $sortings), array());
                        break;
                    case 'xls':
                        $content = $this->renderXlsFile($this->generateReport($selects, $filters, $sortings));
                        $headers = array('Content-type: application/vnd.ms-excel; charset=UTF-16LE');
                        $content = chr(255).chr(254).mb_convert_encoding($content, 'UTF-16LE', 'UTF-8');
                        $this->sendFile($content, $headers, str_replace(array(', ', ' '), '_', $LANG->getLL($get['list'])) . '.xls');
                }
            }
        }

        $pre_content = '';
        $table_rows[] = array();
        foreach ($groups as $key => $lists) {
            $pre_content.= '<h3>' . $key . '</h3>'; 
            foreach ($lists as $list) {
                $table_rows[] = array($LANG->getLL($list), '<a href="?list=' . $list . '&format=html">' . $LANG->getLL('preview') . '</a>', '<a href="?list=' . $list . '&format=xls">Excel</a>');
            }
            $pre_content.= $this->doc->table($table_rows, $this->tableLayout['zebra']);
        }
        return $pre_content . $content;
    }

    function createCustomReportsView() {
        global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;
        $content = '';
        $post = array();

        if (count($_POST) > 0) {
            $post = t3lib_div::_POST();
            if ($this->conf['debug']) {
                t3lib_div::debug($post);
            }
            $filters = (isset($post['filters'])) ? $post['filters'] : array();
            $selects = (isset($post['selects'])) ? $post['selects'] : array();
            $sortings = (isset($post['sortings'])) ? $post['sortings'] : array();
            if(isset($post['format'])) {
                switch($post['format']) {
                    case 'html':
                        $content.= $this->renderHtmlList($this->generateReport($selects, $filters, $sortings), array());
                        break;
                    case 'xls':
                        $content = $this->renderXlsFile($this->generateReport($selects, $filters, $sortings));
                        $headers = array('Content-type: application/vnd.ms-excel; charset=UTF-16LE');
                        $content = chr(255).chr(254).mb_convert_encoding( $content, 'UTF-16LE', 'UTF-8');
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

    function createMassmailerView() {
        global $LANG, $TYPO3_DB;
        $content = '';
        $form = new Form_Massmail();
        if (isset($_POST['submitButton']) && $form->isValid($_POST)) {
            $mail = new Zend_Mail('utf-8');
            $mail->setFrom($form->getValue('fromemail'), $form->getValue('fromtext'));
            $mail->setSubject($form->getValue('subject'));
            $mail->setBodyText($form->getValue('bodytext', 'utf-8') . "\r\n\r\n");
            $attachment = $form->getValue('userfile');
            if (is_array($attachment) && $attachment['error'] == 0) {
                $at = $mail->createAttachment(file_get_contents($attachment['tmp_name']));
                $at->filename = $attachment['name'];
            }   
            if ($form->getValue('reallysend')) {
                // an alle schicken
                $lang_id = (int) $form->getValue('language_id');
                $select  = 'uid, firstname, name, email, language_id';
                $from    = 'tx_mrastp_person';
                $where   = ' email != \'\'';
                if ($lang_id > 0) {
                    $where.= ' AND language_id=' . $lang_id;
                }
                $where  .= ' ' . t3lib_BEfunc::deleteClause('tx_mrastp_person');
                $groupBy = '';
                $orderBy = 'email';
        
                // query database, get number of rows and fill in an array
                $result = $TYPO3_DB->exec_SELECTquery($select, $from, $where, $groupBy, $orderBy);
                $num_rows = $TYPO3_DB->sql_num_rows($result);
                $i = 0;
                $content.= 'Insgesamt werden ' . $num_rows . ' Emails verschickt:<br />';
                while ($row = $TYPO3_DB->sql_fetch_assoc($result)) {
                    $clone = clone $mail;
                    $clone->addTo($row['email']);
                    $clone->send();
                    unset($clone);
                    $content.= 'Email ' . ++$i . ' gesendet an ' . $row['email'] . '<br />';
                }
                $content.= 'Versand fertig';
            } else {
                $mail->addTo($form->getValue('testemail'));
                $mail->send();
                $content.= $LANG->getLL('email_senttest');
            }
        }
        $reallysend = $form->getElement('reallysend');
        $reallysend->setValue(0);
        $userfile = $form->getElement('userfile');
        $userfile->setValue('');
        $content.= $form->render();
        return $content;
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
        $content.= '<tr><td colspan="2"><b>Personendaten:</b></td></tr>';
        $content.= '<tr>' . $this->getSelectOfTable('state', 'filters[tx_mrastp_person|status]',  (isset($post['filters']['tx_mrastp_person|status']) ? $post['filters']['tx_mrastp_person|status'] : false), true) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('language', 'filters[tx_mrastp_person|language_id]', (isset($post['filters']['tx_mrastp_person|language_id']) ? $post['filters']['tx_mrastp_person|language_id'] : false), true) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('section', 'filters[tx_mrastp_person|section_id]', (isset($post['filters']['tx_mrastp_person|section_id']) ? $post['filters']['tx_mrastp_person|section_id'] : false), true) . '</tr>';
        $content.= '<tr>' . $this->getSelectOfTable('group', 'filters[tx_mrastp_persons_groups_rel|groupid]', (isset($post['filters']['tx_mrastp_persons_groups_rel|groupid']) ? $post['filters']['tx_mrastp_persons_groups_rel|groupid'] : false), true, 6) . '</tr>';
        $content.= '<tr><td colspan="2"><b>Privatadresse:</b></td></tr>';
        $content.= '<tr><td><label for="private.city">' . $this->getDbLL($BE_USER->uc['lang'], $this->db['tables']['person'], 'city') . ': </label></td>';
        $content.= '<td><input id="private.city" name="filters[tx_mrastp_person|city]" value="' . (isset($post['filters']['tx_mrastp_person|city']) ? $post['filters']['tx_mrastp_person|city'] : '') . '" /></td></tr>';
        $content.= '<tr>' . $this->getSelectOfTable('canton', 'filters[tx_mrastp_person|canton_id]', (isset($post['filters']['tx_mrastp_person|canton_id']) ? $post['filters']['tx_mrastp_person|canton_id'] : false), true, 10) . '</tr>';
        $content.= '<tr><td colspan="2"><b>Arbeitsadresse:</b></td></tr>';
        $content.= '<tr><td><label for="work.city">' . $this->getDbLL($BE_USER->uc['lang'], $this->db['tables']['workaddress'], 'city') . ': </label></td>';
        $content.= '<td><input id="work.city" name="filters[tx_mrastp_workaddress|city]" value="' . (isset($post['filters']['tx_mrastp_workaddress|city']) ? $post['filters']['tx_mrastp_workaddress|city'] : '') . '" /></td></tr>';
        $content.= '<tr>' . $this->getSelectOfTable('canton', 'filters[tx_mrastp_workaddress|canton_id]', (isset($post['filters']['tx_mrastp_workaddress|canton_id']) ? $post['filters']['tx_mrastp_workaddress|canton_id'] : false), true, 10) . '</tr>';
        $content.= '<tr><td><input type="radio" name="filters[tx_mrastp_workaddress|employment]" value="1" ';
        if (isset($post['filters']['tx_mrastp_workaddress|employment']) && $post['filters']['tx_mrastp_workaddress|employment'] == 1) {
            $content.= 'selected="selected"';
        }
        $content.= ' /> nur angestellt</td><td><input type="radio" name="filters[tx_mrastp_workaddress|employment]" value="2"  ';
        if (isset($post['filters']['tx_mrastp_workaddress|employment']) && $post['filters']['tx_mrastp_workaddress|employment'] == 2) {
            $content.= 'selected="selected"';
        }
        $content.= ' /> nur selbständig</td></tr>';
        $content.= '</table>';
        $content.= '</fieldset><fieldset style="margin-top: 10px"><legend><b>' . $LANG->getLL('output_params') . '</b></legend>';
        $content.= '<fieldset><legend>' . $LANG->getLL('output_fields') . '</legend><table>';
        $content.= $this->generateRadioSwitch('selects[group_section]', (isset($post['selects']['group_section']) ? $post['selects']['group_section'] : 0));
        $content.= $this->generateRadioSwitch('selects[group_status]', (isset($post['selects']['group_status']) ? $post['selects']['group_status'] : 1));
        $content.= $this->generateRadioSwitch('selects[group_language]', (isset($post['selects']['group_language']) ? $post['selects']['group_language'] : 1));
        $content.= $this->generateRadioSwitch('selects[group_private]', (isset($post['selects']['group_private']) ? $post['selects']['group_private'] : 1));
        $content.= $this->generateRadioSwitch('selects[group_private_com]', (isset($post['selects']['group_private_com']) ? $post['selects']['group_private_com'] : 0));
        $content.= $this->generateRadioSwitch('selects[group_work]', (isset($post['selects']['group_work']) ? $post['selects']['group_work'] : 0));
        $content.= $this->generateRadioSwitch('selects[group_work_com]', (isset($post['selects']['group_work_com']) ? $post['selects']['group_work_com'] : 0));
        $content.= '</table></fieldset>';
        $content.= '<fieldset><legend>' . $LANG->getLL('output_sorting') . '</legend><table>';
        for($i=0;$i<3;$i++) {
            $content.= '<tr><td>' . $LANG->getLL('sorting_field') . ' ' . ($i+1) . '</td><td>' . $this->getSelectOfOrderBys('sortings', $post['sortings'][$i]) . '</td></tr>';
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

    function getSelectOfOrderBys($selectname, $preselect=false) {
        global $BE_USER, $LANG;
        $content = '<select id="' . $selectname . '" name="' . $selectname . '[]" size="1">';
        $content.= '<option value=""></option>';
        foreach ($this->db['sortable_fields'] as $field) {
            $fieldParts = explode('.', $field);
            $tableName = $fieldParts[0];
            $encodedField = $this->fkEncode(sprintf($field, $BE_USER->uc['lang']));
            $selected = ($preselect && $preselect == $encodedField) ? ' selected="selected"' : '';
            $content.= '<option value="' . $encodedField . '" ' . $selected . '>' . $this->getDbLL($BE_USER->uc['lang'], $tableName) . '</option>';
        }
        $content.= '</select>';
        return $content;
    }

	function getSelectOfTable($table, $fkField, $preselect=false, $multiple=false, $size=null) {
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

	    $output = '<td><label for="' . $table . '">' . $this->getDbLL($userLang, $from) . ': </label></td><td>';
        if($multiple) {
            $output.= '<select id="' . $table . '" name="' . $fkField . '[]" size="' . $size . '" multiple="multiple">';
        } else {
            $output.= '<select id="' . $table . '" name="' . $fkField . '" size="1">';
        }
	    while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $selected = '';
            if($preselect) {
                if(is_array($preselect)) {
                    foreach ($preselect as $value) {
                        if($value == $row['uid']) {
                            $selected = ' selected="selected"';
                        }
                    }
                } else {
                    if($value == $row['uid']) {
                        $selected = ' selected="selected"';
                    }
                }
            }
	        $output.= '<option value="' . $row['uid'] . '"' . $selected . '>' . $row['label'] . '</option>';
	    }
	    $output.= '</select></td>';
	    return $output;
	}

	function generateRadioSwitch($name, $preselect) {
	    global $LANG, $BE_USER;

        $realname = preg_replace('/.*\[(.*)\]/', '\1', $name);
	    $xhtml.= '<tr><td><label>' . $LANG->getLL($realname) . '</label></td>';
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

        foreach ($filters as $field => $values) {
            if(!is_array($values)) {
                $values = array($values);
            }
            $field = $this->fkDecode($field);
            list($table, $column) = explode('.', $field);
            if(in_array($table, $this->db['tables'])) {
                if($table != 'tx_mrastp_person' && !preg_match('/' . $table . '/', $join)) {
                            $join.= $this->getRelationWhere($table . '.' . $column);
                }
                if(is_array($this->db['tca'][$table]['columns'][$column])) {
                    $wherePart = '';
                    foreach ($values as $value) {
                        if(!empty($value)) {
                            $wherePart.= strlen($wherePart) == 0 ? $field . "='" . $value . "'" : ' OR ' . $field . "='" . $value . "'";
                        }
                    }
                    if(!empty($wherePart)) {
                        $wherePart = '(' . $wherePart . ')';
                        $where.= strlen($where) == 0 ? $wherePart : ' AND ' . $wherePart;
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
        $orderBy.= ($orderBy == '') ? ' ORDER BY name, firstname' : ', name, firstname';

        if (!$where) {
            $where = '1=1';
        }
        $where.= t3lib_BEfunc::deleteClause('tx_mrastp_person');
        $where.= t3lib_BEfunc::BEenableFields('tx_mrastp_person');
        if(preg_match('/tx_mrastp_workaddress/', $join)) {
            $where .= t3lib_BEfunc::deleteClause('tx_mrastp_workaddress');
            $where .= t3lib_BEfunc::BEenableFields('tx_mrastp_workaddress');
        }

        $sql = 'SELECT ' . $select . ' FROM tx_mrastp_person ' . $join . ' WHERE ' . $where . $orderBy;
        if($this->conf['debug']) {
            echo $sql;
        }
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
	        case 'pc':
	            return ' LEFT JOIN tx_mrastp_canton as pc ON tx_mrastp_person.canton_id = pc.uid';
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
	        case 'pl':
	            return ' LEFT JOIN tx_mrastp_country as pl ON tx_mrastp_person.country_id = pl.uid';
	            break;
            case 'tx_mrastp_person.salutation_id':
            case 'tx_mrastp_salutation':
                return ' LEFT JOIN tx_mrastp_salutation ON tx_mrastp_person.salutation_id = tx_mrastp_salutation.uid';
                break;
            case 'tx_mrastp_person.language_id':
            case 'tx_mrastp_language':
                return ' LEFT JOIN tx_mrastp_language ON tx_mrastp_person.language_id = tx_mrastp_language.uid';
                break;
            case 'tx_mrastp_workaddress.country_id':
            case 'tx_mrastp_workaddress.canton_id':
            case 'tx_mrastp_workaddress':
            case 'wc':
                return ' LEFT JOIN tx_mrastp_workaddress ON tx_mrastp_workaddress.parentuid = tx_mrastp_person.uid LEFT JOIN tx_mrastp_canton as wc ON tx_mrastp_workaddress.canton_id = wc.uid LEFT JOIN tx_mrastp_country as wl ON tx_mrastp_workaddress.country_id = wl.uid';
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
	
    private function helperCantonsAlphabet() {
        global $TYPO3_DB;
        
        $result = $TYPO3_DB->exec_SELECTquery('uid, abbrevation',  'tx_mrastp_canton', '1=1', 'abbrevation ASC');
        $links = array();

        $links[] = '<a href="/' . PATH_typo3_mod . '?canton_id=-1">alle</a>';
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
            $links[] = '<a href="/' . PATH_typo3_mod . '?canton_id=' . $row['uid'] . '">' . $row['abbrevation'] . '</a>';
        }
        return '<div style="margin: 10px 5px">' . implode(' | ', $links) . '</div>';
    }

	function getDbLL($lang, $table, $column=false) {
        global $LANG;

	    if($lang === 'en') {
	        $lang = 'default';
	    }
	    if(!$column) {
	        $llPointer = $this->db['tca'][$table]['ctrl']['title'];
            if(empty($llPointer)) {
                $llPointer = 'LL:EXT:mr_astp/locallang_db.xml:gitsnid.' . $table;
            }
	    } else {
            if(isset($this->db['tca'][$table]['columns'][$column]['label'])) {
	            $llPointer = $this->db['tca'][$table]['columns'][$column]['label'];
            } else {
                $llPointer = 'LLL:EXT:mr_astp/locallang_db.xml:' . $table . '.' . $column;
            }
	    }
	    $llParts = explode(':', $llPointer);
	    $label = $llParts[3];
        $label_value = $this->db['locallang_db'][$lang][$label];
        if(empty($label_value)) {
            list(,$label) = explode('.', $label);
            $label_value = $LANG->getLL($label);
        } 
	    return $label_value;
	}

	function sendFile($content, $headers, $filename=null) {
        $filename = ($filename == null) ? 'astp-Adressliste_' . date('Y-m-d_H-m-s') . '.xls' : $filename;
        header('Pragma: public');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Pragma: no-cache');
        foreach ($headers as $header) {
            header($header);
        }
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
	    echo $content;
	    exit;
	}

    function fkEncode($string) {
        return str_replace('.', '|', $string);
    }

    function fkDecode($string) {
        return str_replace('|', '.', $string);
    }

    function grayout($value, $do = 1) {
        if ($do == 1) {
            return '<span style="color: #aaaaaa">' . $value . '</span>'; 
        } else {
            return $value;
        }
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mr_astp/mod1/index.php']);
}



// Make instance:
$SOBE = t3lib_div::makeInstance('mr_astp_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) {
    require_once($INC_FILE);
}

$SOBE->main();
$SOBE->printContent();
