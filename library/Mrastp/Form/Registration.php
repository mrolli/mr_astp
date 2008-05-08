<?php

require_once 'Zend/Form.php';
require_once 'Zend/Translate.php';
require_once 'Zend/View.php';

class Mrastp_Form_Registration extends Zend_Form 
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

        $salutation = $this->createElement('select', 'salutation_id');
        $salutation->setRequired(true)
                   ->setLabel($this->_plugin->pi_getLL('salutation_id'))
                   ->addMultiOptions($this->_plugin->getSelectSalutation());
                   
        $firstname = $this->createElement('text', 'firstname');
        $firstname->setRequired(true)
                  ->setLabel($this->_plugin->pi_getLL('firstname'))
                  ->setAttrib('size', 45);

        $name = $this->createElement('text', 'name');
        $name->setRequired(true)
             ->setLabel($this->_plugin->pi_getLL('name'))
             ->setAttrib('size', 45);
             
        $street = $this->createElement('text', 'street');
        $street->setRequired(true)
               ->setLabel($this->_plugin->pi_getLL('street'))
               ->setAttrib('size', 45);

        $compl = $this->createElement('text', 'compl');
        $compl->setLabel($this->_plugin->pi_getLL('compl'))
              ->setAttrib('size', 45);

        $zip = $this->createElement('text', 'zip');
        $zip->setRequired(true)
            ->setLabel($this->_plugin->pi_getLL('zip'))
            ->setAttrib('size', 5);
            
        $city = $this->createElement('text', 'city');
        $city->setRequired(true)
             ->setLabel($this->_plugin->pi_getLL('city'))
             ->setAttrib('size', 45);
             
        $canton = $this->createElement('select', 'canton_id');
        $canton->setRequired(true)
               ->setLabel($this->_plugin->pi_getLL('canton_id'))
               ->addMultiOptions($this->_plugin->getSelectCanton());

        $country = $this->createElement('select', 'country_id');
        $country->setRequired(true)
                ->setLabel($this->_plugin->pi_getLL('country_id'))
                ->addMultiOptions($this->_plugin->getSelectCountry());
                
        $phone = $this->createElement('text', 'phone');
        $phone->setRequired(true)
              ->setLabel($this->_plugin->pi_getLL('phone'))
              ->setAttrib('size', 45);
              
        $mobile = $this->createElement('text', 'mobile');
        $mobile->setRequired(false)
              ->setLabel($this->_plugin->pi_getLL('mobile'))
              ->setAttrib('size', 45);
              
        $fax = $this->createElement('text', 'fax');
        $fax->setRequired(false)
              ->setLabel($this->_plugin->pi_getLL('fax'))
              ->setAttrib('size', 45);
              
        $email = $this->createElement('text', 'email');
        $email->setRequired(true)
              ->setLabel($this->_plugin->pi_getLL('email'))
              ->setAttrib('size', 45)
              ->addValidator('EmailAddress');

        $language = $this->createElement('select', 'language_id');
        $language->setRequired(true)
               ->setLabel($this->_plugin->pi_getLL('language_id'))
               ->addMultiOptions($this->_plugin->getSelectLanguage());
               
        $section = $this->createElement('select', 'section_id');
        $section->setRequired(true)
               ->setLabel($this->_plugin->pi_getLL('section_id'))
               ->addMultiOptions($this->_plugin->getSelectSection());
               
        $status = $this->createElement('select', 'status');
        $status->setRequired(true)
               ->setLabel($this->_plugin->pi_getLL('status'))
               ->addMultiOptions($this->_plugin->getSelectStatus());
        
        $username = $this->createElement('text', 'username');
        $username->setRequired(true)
                 ->setLabel($this->_plugin->pi_getLL('username'));
                 
        $password = $this->createElement('password', 'password');
        $password->setRequired(true)
                 ->setLabel($this->_plugin->pi_getLL('password'))
                 ->addPrefixPath('Mrastp_Validate', 'Mrastp/Validate', 'validate')
                 ->addValidator('PasswordConfirmation');
                  
        $password_confirm = $this->createElement('password', 'password_confirm');
        $password_confirm->setRequired(true)
                         ->setLabel($this->_plugin->pi_getLL('password-repeat'));

        $submitButton = $this->createElement('submit', 'submitButton');
        $submitButton->setLabel($this->_plugin->pi_getLL('button_register'));
        
        $this->addElements(array($salutation, $firstname, $name, $street, $compl, $zip, $city, $canton, $country, $phone, 
                                 $mobile, $fax, $email, $language, $section, $status, $username, $password, $password_confirm));
        $this->addDisplayGroup(array('salutation_id', 'firstname', 'name', 'street', 'compl', 'zip', 'city', 'canton_id', 'country_id',
                                     'phone', 'mobile', 'fax', 'email', 'language_id', 'section_id', 'status'),
                               'personal', array('legend' => $this->_plugin->pi_getLL('personal_data')));
        $this->addDisplayGroup(array('username', 'password', 'password_confirm'), 'account', array('legend' => $this->_plugin->pi_getLL('online_account')));
        $this->addElement($submitButton);
    }
}