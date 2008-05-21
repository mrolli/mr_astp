<?php

require_once 'Zend/Validate/Abstract.php';
require_once 'Mrastp/Db/Table/Feuser.php';

class Mrastp_Validate_DuplicateFeuserCheck extends Zend_Validate_Abstract
{
    const USERNAME_EXISTS = 'usernameExists';

    protected $_messageTemplates = array(
        self::USERNAME_EXISTS => 'A user with the given username already exists.'
    );

    public function isValid($value, $context = null)
    {
        $value = (string) $value;
        $this->_setValue($value);

        $feuserTable = new Mrastp_Db_Table_Feuser();
        $feusers = $feuserTable->fetchAll(array('username = ?' => $value));
        if (count($feusers) == 0) {
            return true;
        }
        if (isset($context['uid']) && $feusers->current()->uid == $context['uid']) {
            return true;
        }

        $this->_error(self::USERNAME_EXISTS);
        return false;
    }
}