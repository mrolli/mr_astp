<?php

require_once 'Zend/Form.php';
require_once 'Zend/View.php';

class Form_Massmail extends Zend_Form 
{

    public function __construct($options=array())
    {
        global $LANG;
        parent::__construct($options);
        $this->setView(new Zend_View());
        
        $subject = $this->createElement('text', 'subject');
        $subject->setLabel($LANG->getLL('email_subject'))
                ->setRequired(true);
                
        $bodyText = $this->createElement('textarea', 'bodytext');
        $bodyText->setLabel($LANG->getLL('email_bodytext'))
                 ->setRequired(true);
                 
        $fromText = $this->createElement('text', 'formtext');
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
                  
        $reallySend = $this->createElement('checkbox', 'reallysend');
        $reallySend->setLabel($LANG->getLL('email_reallysend'));
        
        $submit = $this->createElement('submit', 'submitButton');
        $submit->setLabel('Email abschicken.');
        
        $this->addElements(array($subject, $bodyText, $fromText, $fromEmail, $testEmail, $reallySend, $submit));
    }
}