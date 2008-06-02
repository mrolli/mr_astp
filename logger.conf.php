<?php

/**
 * Prios:
 * - EMERG (0)
 * - ALERT (1)
 * - CRIT (2)
 * - ERR (3)
 * - WARN (4)
 * - NOTICE (5)
 * - INFO (6)
 * - DEBUG (7)
 */
Zend_Loader::loadClass('Zend_Config_Ini');
$logger_conf = new Zend_Config_Ini(t3lib_extMgm::extPath('mr_astp') . '/config.ini', 'logging');

Zend_Loader::loadClass('Zend_Log_Filter_Priority');
Zend_Loader::loadClass('Zend_Log_Writer_Stream');
$fileWriter = new Zend_Log_Writer_Stream($logger_conf->filename);
$fileWriter->addFilter(new Zend_Log_Filter_Priority((int) $logger_conf->minPrioFile));
$this->_logger->addWriter($fileWriter);

Zend_Loader::loadClass('Zend_Mail');
$mail = new Zend_Mail('utf-8');
$mail->addTo($logger_conf->mailto)
     ->setFrom($logger_conf->mailFromEmail, $logger_conf->mailFromText)
     ->setSubject($logger_conf->mailSubject);
     
Zend_Loader::loadClass('Mrastp_Log_Writer_Mail');
$mailWriter = new Mrastp_Log_Writer_Mail($mail);
$mailWriter->addFilter(new Zend_Log_Filter_Priority((int) $logger_conf->minPrioMail));
$this->_logger->addWriter($mailWriter);
