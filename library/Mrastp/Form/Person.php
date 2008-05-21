<?php

require_once 'Zend/Form.php';
require_once 'Zend/Translate.php';
require_once 'Zend/View.php';

class Mrastp_Form_Person extends Zend_Form 
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
        
        $this->setName('personform')
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
             ->setAttrib('size', 45)
             ->addPrefixPath('Mrastp_Validate', 'Mrastp/Validate', 'validate')
             ->addValidator('DuplicateMemberCheck', true);
             
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
              ->addPrefixPath('Mrastp_Validate', 'Mrastp/Validate', 'validate')
              ->addValidator('EmailAddress', true)
              ->addValidator('DuplicateEmailAddressCheck', true);

        $language = $this->createElement('select', 'language_id');
        $language->setRequired(true)
               ->setLabel($this->_plugin->pi_getLL('language_id'))
               ->addMultiOptions($this->_plugin->getSelectLanguage());

        $submitButton = $this->createElement('submit', 'submitButton');
        $submitButton->setLabel($this->_plugin->pi_getLL('save_changes'))
                     ->setIgnore(true);
        
        $this->addElements(array($salutation, $firstname, $name, $street, $compl, $zip, $city, $canton, $country, $phone, 
                                 $mobile, $fax, $email, $language, $username));
        $this->addDisplayGroup(array('salutation_id', 'firstname', 'name', 'street', 'compl', 'zip', 'city', 'canton_id', 'country_id',
                                     'phone', 'mobile', 'fax', 'email', 'language_id'),
                               'personal', array('legend' => $this->_plugin->pi_getLL('personal_data')));
        $this->addElement($submitButton);
    }
}