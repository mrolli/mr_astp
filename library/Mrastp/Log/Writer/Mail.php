<?php

/**
 * Zend_Log_Writer_Abstract
 */
Zend_Loader::loadClass('Zend_Log_Writer_Abstract');
Zend_Loader::loadClass('Zend_Log_Formatter_Simple');

class Mrastp_Log_Writer_Mail extends Zend_Log_Writer_Abstract
{
    /**
     * Holds a Zend_Mail object to write to.
     *
     * @var Zend_Mail
     */
    protected $_mail;

    /**
     * Holds the Zend_Log_Formatter object
     *
     * @var Zend_Log_Formatter_Abstract
     */
    protected $_formatter;

    /**
     * Class Constructor
     *
     * @param  Zend_Mail  Mail object
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __construct($mail)
    {
        if ($mail instanceof Zend_Mail) {
            $this->_mail = $mail;
            Zend_Loader::loadClass('Zend_Log_Formatter_Simple');
            $this->_formatter = new Zend_Log_Formatter_Simple();
        } else {
            throw new Zend_Log_Exception('First parameter must be an '
                                        .'instance of Zend_Mail, ' 
                                        . gettype($mail) . ' given');
        }
    }

    /**
     * Destroy the Zend_Mail object
     *
     * @return void
     */
    public function shutdown()
    {
        $this->_mail = null;
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     */
    protected function _write($event)
    {
        if ($this->_mail == null) {
            Zend_Loader::loadClass('Zend_Log_Exception');
            throw new Zend_Log_Exception('No mail object available to log to');
        }
        $line = $this->_formatter->format($event);

        $this->_mail->setBodyText($line);
        $this->_mail->send();
    }

}
