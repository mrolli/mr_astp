<?php

require_once 'Zend/Form.php';
require_once 'Zend/Translate.php';
require_once 'Zend/View.php';

class Mrastp_Form_Workaddress extends Zend_Form 
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

        $employment = $this->createElement('select', 'employment');
        $employment->setRequired(true)
                   ->setLabel($this->_plugin->pi_getLL('employment'))
                   ->addMultiOptions(array(1 => $this->_plugin->pi_getLL('employment.I.1'),
                                           2 => $this->_plugin->pi_getLL('employment.I.2')));

        $name_practice = $this->createElement('text', 'name_practice');
        $name_practice->setRequired(true)
                      ->setLabel($this->_plugin->pi_getLL('name_practice'))
                      ->setAttrib('size', 45)
                      ->addFilter('StripTags');

        $name_supplement = $this->createElement('text', 'name_supplement');
        $name_supplement->setLabel($this->_plugin->pi_getLL('name_supplement'))
                        ->setAttrib('size', 45)
                        ->addFilter('StripTags');
             
        $address1 = $this->createElement('text', 'address1');
        $address1->setRequired(true)
                 ->setLabel($this->_plugin->pi_getLL('address1'))
                 ->setAttrib('size', 45)
                 ->addFilter('StripTags');

        $address2 = $this->createElement('text', 'address2');
        $address2->setLabel($this->_plugin->pi_getLL('address2'))
                 ->setAttrib('size', 45)
                 ->addFilter('StripTags');

        $zip = $this->createElement('text', 'zip');
        $zip->setRequired(true)
            ->setLabel($this->_plugin->pi_getLL('zip'))
            ->setAttrib('size', 5);
            
        $city = $this->createElement('text', 'city');
        $city->setRequired(true)
             ->setLabel($this->_plugin->pi_getLL('city'))
             ->setAttrib('size', 45)
             ->addFilter('StripTags');
             
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
              ->setAttrib('size', 45)
              ->addFilter('StripTags');
              
        $mobile = $this->createElement('text', 'mobile');
        $mobile->setRequired(false)
              ->setLabel($this->_plugin->pi_getLL('mobile'))
              ->setAttrib('size', 45)
              ->addFilter('StripTags');
              
        $fax = $this->createElement('text', 'fax');
        $fax->setRequired(false)
              ->setLabel($this->_plugin->pi_getLL('fax'))
              ->setAttrib('size', 45)
              ->addFilter('StripTags');
              
        $email = $this->createElement('text', 'email');
        $email->setRequired(false)
              ->setLabel($this->_plugin->pi_getLL('email'))
              ->setAttrib('size', 45)
              ->addValidator('EmailAddress');

        $audience = $this->createElement('textarea', 'audience');
        $audience->setRequired(false)
                 ->setLabel($this->_plugin->pi_getLL('audience'))
                 ->setAttribs(array('cols' => 45, 'rows' => 5))
                 ->addFilter('StripTags');

        $services = $this->createElement('textarea', 'services');
        $services->setRequired(false)
                 ->setLabel($this->_plugin->pi_getLL('services'))
                 ->setAttribs(array('cols' => 45, 'rows' => 5))
                 ->addFilter('StripTags');
                 
        $languages = $this->createElement('textarea', 'languages');
        $languages->setRequired(false)
                 ->setLabel($this->_plugin->pi_getLL('languages'))
                 ->setAttribs(array('cols' => 45, 'rows' => 5))
                 ->addFilter('StripTags');
                 
        $website = $this->createElement('text', 'website');
        $website->setRequired(false)
                ->setLabel($this->_plugin->pi_getLL('website'))
                ->setAttrib('size', 45)
                ->addFilter('StripTags');

        $startofwork = $this->createElement('text', 'startofwork');
        $startofwork->setRequired(true)
                    ->setLabel($this->_plugin->pi_getLL('startofwork'))
                    ->setAttrib('size', 15);

        $submitButton = $this->createElement('submit', 'submitButton');
        $submitButton->setLabel($this->_plugin->pi_getLL('save_changes'))
                     ->setIgnore(true);
        
        $this->addElements(array($employment, $name_practice, $name_supplement, $address1, $address2, $zip, $city, $canton, $country, $phone, 
                                 $mobile, $fax, $email, $audience, $services, $languages, $website, $startofwork));
        $this->addDisplayGroup(array('employment', 'name_practice', 'name_supplement', 'address1', 'address2', 'zip', 'city', 'canton_id', 'country_id',
                                     'phone', 'mobile', 'fax', 'email', 'audience', 'services', 'languages', 'website', 'startofwork'),
                               'workaddress', array('legend' => $this->_plugin->pi_getLL('workaddress')));
        $this->addElement($submitButton);
    }
}