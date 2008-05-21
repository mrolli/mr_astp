<?php

require_once 'Zend/Form.php';
require_once 'Zend/Translate.php';
require_once 'Zend/View.php';

class Mrastp_Form_Account extends Zend_Form 
{
    protected $_plugin;

    public function __construct($plugin, $options=array())
    {
        parent::__construct($options);
        $this->_plugin = $plugin;
        $translator = new Zend_Translate(
                                'csv', 
                                t3lib_extMgm::extPath('mr_astp') . '/lang', 
                                $this->_plugin->getFeUserLang(),
                                array('scan' => Zend_Translate::LOCALE_FILENAME)
                                );
        $translator->setLocale($this->_plugin->getFeUserLang());
        $this->setDefaultTranslator($translator);
        $this->setView(new Zend_View());
        
        $this->setName('registrationform')
             ->setMethod('post')
             ->setAction($this->_plugin->pi_getPageLink($GLOBALS['TSFE']->id));

        $username = $this->createElement('text', 'username');
        $username->setRequired(true)
                 ->setLabel($this->_plugin->pi_getLL('username'))
                 ->addPrefixPath('Mrastp_Validate', 'Mrastp/Validate', 'validate')
                 ->addValidator('StringLength', true, array(4, 12))
                 ->addValidator('DuplicateFeuserCheck', true);
                 
        $password = $this->createElement('password', 'password');
        $password->setRequired(false)
                 ->setAllowEmpty(true)
                 ->setLabel($this->_plugin->pi_getLL('password'))
                 ->addPrefixPath('Mrastp_Validate', 'Mrastp/Validate', 'validate')
                 ->addValidator('PasswordConfirmation')
                 ->addValidator('StringLength', false, array(6, 8));
                  
        $password_confirm = $this->createElement('password', 'password_confirm');
        $password_confirm->setRequired(false)
                         ->setAllowEmpty(true)
                         ->setLabel($this->_plugin->pi_getLL('password-repeat'));

        $submitButton = $this->createElement('submit', 'submitButton');
        $submitButton->setLabel($this->_plugin->pi_getLL('save_changes'))
                     ->setIgnore(true);
        
        $this->addElements(array($username, $password, $password_confirm));
        $this->addDisplayGroup(array('username', 'password', 'password_confirm'), 'account', array('legend' => $this->_plugin->pi_getLL('online_account')));
        $this->addElement($submitButton);
    }
}