<?php

require_once 'Zend/Form.php';
require_once 'Zend/Translate.php';
require_once 'Zend/View.php';

class Mrastp_Form_StoreItem extends Zend_Form 
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
        
        $this->setName('storeitem')
             ->setMethod('post');

        foreach ($this->_plugin->config['fields'] as $field) {
            $this->addElement('text', $field, array('required' => true, 'label' => $field, 'size' => 30));
        }

        $submitButton = $this->createElement('submit', 'submitButton');
        $submitButton->setLabel($this->_plugin->pi_getLL('save_changes'))
                     ->setIgnore(true);
        $this->addElement($submitButton);
    }
}