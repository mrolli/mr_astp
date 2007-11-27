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
 * Module 'ASTP Database' for the 'mr_astp' extension.
 *
 * @author	 <>
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
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

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
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('members'),
							'2' => $LANG->getLL('reports'),
							'3' => $LANG->getLL('backups'),
						)
					);
					parent::menuConfig();
				}

				/**
				 * Main function of the module. Write the content to $this->content
				 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
				 *
				 * @return	[type]		...
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
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);


						// Render content:
						$this->moduleContent();


						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('mediumDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				}

				/**
				 * Prints out the module HTML
				 *
				 * @return	void
				 */
				function printContent()	{

					$this->content.=$this->doc->endPage();
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
							$this->content.=$this->doc->section($LANG->getLL('members_view') . ':', $this->createMembresView(), 0, 1);
						break;
						case 2:
							$content='<div align=center><strong>Menu item #2...</strong></div>';
							$this->content.=$this->doc->section('Message #2:',$content,0,1);
						break;
						case 3:
							$content='<div align=center><strong>Menu item #3...</strong></div>';
							$this->content.=$this->doc->section('Message #3:',$content,0,1);
						break;
					}
/*
					$this->content.= '<hr />
                                                                <br />This is the GET/POST vars sent to the script:<br />'.
                                                                'GET:'.t3lib_div::view_array($_GET).'<br />'.
                                                                'POST:'.t3lib_div::view_array($_POST) . '<br />' . t3lib_div::debug($this->conf);
*/
				}

				/**
				 * Generates Members View
				 */
				function createMembresView() {
					global $LANG, $TYPO3_DB, $BE_USER, $TCA, $BACK_PATH;
					$content = $this->helperMembersAlphabet();

					$where = (isset($_GET['show']) && t3lib_div::_GET('show') != 'alle') ? ' name like \'' . t3lib_div::_GET('show') . '%\'' : '1=1';
					$where.= ' ' . t3lib_BEfunc::deleteClause('tx_mrastp_person');

                        		$result = $TYPO3_DB->exec_SELECTquery(
                                		'uid, firstname, name, zip, city',
                                		'tx_mrastp_person',
                                		$where,
                                		'name'
                        			);
					$content.= '<table style="border-collapse: collapse; margin: 10px 5px;"><td></td><td><b>Name, Vorname</b></td><td><b>PLZ Ort</b></td>';
					while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
						$params='&edit[tx_mrastp_person]['.$row['uid'].']=edit'; 
						$content.= '<tr><td><a href="#" onclick="'. 
            						htmlspecialchars(t3lib_BEfunc::editOnClick($params, '/' . TYPO3_mainDir, '')).'">'; 
            					$content.='<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('edit',1).'" class="absmiddle" alt="" /></a></td>';
						$content.= '<td>' . $row['name'] . ', ' . $row['firstname'] . '</td>';
						$content.='<td>' . $row['zip'] . ' ' . $row['city'] . '</td></tr>';
					}
					$content.= '</table>';
					return '<p>' . $content . '</p>';
				}

				function helperMembersAlphabet() {
					$items = array('alle', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
					$links = array();
					foreach ($items as $item) {
					    $links[] = '<a href="' . substr(PATH_thisScript, strlen(PATH_typo3_mod)) . '?show=' . $item . '">' . $item . '</a>';
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
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
