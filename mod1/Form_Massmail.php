<?php

require_once 'Zend/Form.php';
require_once 'Form_Element_File.php';
require_once 'Zend/View.php';

class Form_Massmail extends Zend_Form 
{
    protected $_module = null;

    public function __construct($module, $options=array())
    {
        global $LANG, $TYPO3_DB, $BE_USER;
        $this->addElementPrefixPath('Form', 'mod1');
        parent::__construct($options);
        $this->_module = $module;
        $this->setView(new Zend_View());
        $this->setName('massmail');
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setAction($_SERVER['REQUEST_URI']);
        
        $subject = $this->createElement('text', 'subject');
        $subject->setLabel($LANG->getLL('email_subject'))
                ->setRequired(true)
                ->addValidator('StringLength', true, array('min' => 5, 'max' => 60))
                ->setAttrib('size', 80);
                
        $bodyText = $this->createElement('textarea', 'bodytext');
        $bodyText->setLabel($LANG->getLL('email_bodytext'))
                 ->setRequired(true);
                 
        $file = new Form_Element_File('userfile');
        $file->setLabel($LANG->getLL('email_attachment'))
             ->setRequired(false)
             ->setAllowEmpty(true)
             ->addValidator('NotEmpty');
                 
        $fromText = $this->createElement('text', 'fromtext');
        $fromText->setLabel($LANG->getLL('email_fromtext'))
                 ->setRequired(true)
                 ->setValue('astp Verband');
                 
        $fromEmail = $this->createElement('text', 'fromemail');
        $fromEmail->setLabel($LANG->getLL('email_fromemail'))
                  ->setRequired(true)
                  ->setValue('info@astp.ch')
                  ->addValidator('EmailAddress');
                  
        $testEmail = $this->createElement('text', 'testemail');
        $testEmail->setLabel($LANG->getLL('email_testemail'))
                  ->setAllowEmpty(true)
                  ->setValue('benutzername@astp.ch')
                  ->addValidator('Emailaddress');
                  
        $lang_id = $this->createElement('select', 'language_id');
        $lang_id->setLabel($LANG->getLL('group_language'))
                ->addMultiOptions(array(0 => 'alle', 1 => 'nur deutsch', 2 => 'nur franz'));
                
        $canton_id = $this->createElement('multiselect', 'canton_id');
        $canton_id->setRequired(false)
                  ->setAttrib('size', 7)
                  ->setLabel($LANG->getLL('cantons'));

        $canton_options = array();
        $label_suffix = $BE_USER->uc['lang'];
        $result = $TYPO3_DB->exec_SELECTquery('uid, abbrevation, CONCAT(abbrevation, " - ",  label_' . $label_suffix . ') as label', 'tx_mrastp_canton', '1=1', 'abbrevation ASC');
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
               $canton_options[$row['uid']] = $row['label'];
        }
        $canton_id->addMultiOptions($canton_options);
        
        $section_id = $this->createElement('multiselect', 'section_id');
        $section_id->setRequired(false)
                   ->setLabel($LANG->getLL('group_section'));

        $section_options = array();
        $label_suffix = $BE_USER->uc['lang'];
        $result = $TYPO3_DB->exec_SELECTquery('uid, abbrevation, CONCAT(abbrevation, " - ",  label_' . $label_suffix . ') as label', 'tx_mrastp_section', '1=1', 'abbrevation ASC');
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
               $section_options[$row['uid']] = $row['label'];
        }
        $section_id->addMultiOptions($section_options);
        
        $group_id = $this->createElement('multiselect', 'group_id');
        $group_id->setRequired(false)
                 ->setAttrib('size', 7)
                 ->setLabel($LANG->getLL('groups'));

        $group_options = array();
        $label_suffix = $BE_USER->uc['lang'];
        $result = $TYPO3_DB->exec_SELECTquery('uid, label_' . $label_suffix . ' as label', 'tx_mrastp_group', '1=1');
        while($row = $TYPO3_DB->sql_fetch_assoc($result)) {
               $group_options[$row['uid']] = $row['label'];
        }
        $group_id->addMultiOptions($group_options);
                  
        $reallySend = $this->createElement('checkbox', 'reallysend');
        $reallySend->setLabel($LANG->getLL('email_reallysend'));
        
        $submit = $this->createElement('submit', 'submitButton');
        $submit->setLabel('Email abschicken.');
        
        $this->addElements(array($lang_id, $group_id, $section_id, $canton_id, $subject, $bodyText, $file, $fromText, $fromEmail, $testEmail, $reallySend, $submit));
        $this->addDisplayGroup(array('language_id', 'group_id', 'section_id', 'canton_id'), 'filters', array('legend' => 'Filters'));
        $this->addDisplayGroup(array('subject', 'bodytext', 'userfile', 'fromtext', 'fromemail', 'testemail'), 'email', array('legend' => 'Email'));
        $this->addDisplayGroup(array('reallysend', 'submitButton'), 'therest');
    }
}
