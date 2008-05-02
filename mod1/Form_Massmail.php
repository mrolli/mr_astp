<?php

require_once 'Zend/Form.php';
require_once 'Form_Element_File.php';
require_once 'Zend/View.php';

class Form_Massmail extends Zend_Form 
{

    public function __construct($options=array())
    {
        global $LANG;
        $this->addElementPrefixPath('Form', 'mod1');
        parent::__construct($options);
        $this->setView(new Zend_View());
        $this->setName('massmail');
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');
        $this->setAction($_SERVER['REQUEST_URI']);
        
        $subject = $this->createElement('text', 'subject');
        $subject->setLabel($LANG->getLL('email_subject'))
                ->setRequired(true)
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
                  ->addValidator('Emailaddress');
                  
        $testEmail = $this->createElement('text', 'testemail');
        $testEmail->setLabel($LANG->getLL('email_testemail'))
                  ->setAllowEmpty(true)
                  ->setValue('benutzername@astp.ch')
                  ->addValidator('Emailaddress');
                  
        $lang_id = $this->createElement('select', 'language_id');
        $lang_id->setLabel('Sprachfilter')
                ->addMultiOptions(array(0 => 'alle', 1 => 'nur deutsch', 2 => 'nur franz'));
                  
        $reallySend = $this->createElement('checkbox', 'reallysend');
        $reallySend->setLabel($LANG->getLL('email_reallysend'));
        
        $submit = $this->createElement('submit', 'submitButton');
        $submit->setLabel('Email abschicken.');
        
        $this->addElements(array($lang_id, $subject, $bodyText, $file, $fromText, $fromEmail, $testEmail, $reallySend, $submit));
    }
}