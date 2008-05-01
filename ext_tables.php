<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE == 'BE') {
        t3lib_extMgm::addModule('web','mrastpM1','',t3lib_extMgm::extPath($_EXTKEY).'mod1/');
}

$TCA["tx_mrastp_person"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person',
        'label'     => 'name',
        'label_alt' => 'firstname',
        'label_alt_force' => 1,
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'ORDER BY name, firstname ASC',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_person.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, salutation_id, firstname, name, street, compl, zip, city, canton_id, country_id, phone, mobile, fax, email, lang, section_id, status, entry_date, workaddress",
	)
);

$TCA["tx_mrastp_canton"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_canton',
		'label'     => 'abbrevation',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'abbrevation',
        'adminOnly'       => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_canton.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "abbrevation, label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_salutation"] = array (
	"ctrl" => array (
		'title'           => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_salutation',
		'label'           => 'label_de',
		'label_alt'       => 'label_fr',
		'label_alt_force' => 1,
		'cruser_id'       => 'cruser_id',
        'adminOnly'       => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_salutation.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "abbrevation, label_de, label_fr, label_en",
	)
);

$TCA['tx_mrastp_country'] = array(
    'ctrl' => array(
        'label' => 'cn_short_en',
        'label_alt' => 'cn_short_en,cn_iso_2',
        'cruser_id' => 'cruser_id',
        'adminOnly' => 1,
        'default_sortby' => 'ORDER BY cn_short_en',
        'title' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_country',
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_country.gif'
    ),
    'interface' => array(
        'showRecordFieldList' => 'cn_iso_2,cn_iso_3,cn_short_en,cn_short_de,cn_short_fr'
    )
);

$TCA["tx_mrastp_section"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_section',
		'label'     => 'abbrevation',
		'cruser_id' => 'cruser_id',
		'sortby'    => 'sorting',
        'adminOnly' => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_section.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "abbrevation, label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_language"] = array (
	"ctrl" => array (
		'title'           => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_language',
        'label'           => 'label_de',
        'label_alt'       => 'label_fr',
        'label_alt_force' => 1,
		'cruser_id'       => 'cruser_id',
		'sortby'          => 'sorting',
        'adminOnly'       => 1,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_language.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_state"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_state',
		'label'     => 'abbrevation',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_state.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "abbrevation, label_de, label_fr, label_en",
	)
);

$TCA["tx_mrastp_workaddress"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress',
		'label'     => 'name_practice',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'default_sortby' => 'city',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_workaddress.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "hidden, starttime, endtime, employment, name_practice, name_supplement, address1, address2, zip, city, country_id, canton_id, phone, fax, email, audience, services, languages, website, startofwork",
	)
);

$TCA["tx_mrastp_group_cat"] = array (
        "ctrl" => array (
                'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_group_cat',
                'label'     => 'label_de',
                'tstamp'    => 'tstamp',
                'crdate'    => 'crdate',
                'cruser_id' => 'cruser_id',
                'default_sortby' => 'label_de',
                'delete' => 'deleted',
                'enablecolumns' => array (
                        'disabled' => 'hidden',
                        'starttime' => 'starttime',
                        'endtime' => 'endtime',
                ),
                'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
                'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_group_cat.gif',
        ),
        "feInterface" => array (
                "fe_admin_fieldList" => "hidden, starttime, endtime, label_de, label_fr, label_en",
        )
);

$TCA["tx_mrastp_group"] = array (
        "ctrl" => array (
                'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_group',
                'label'     => 'label_de',
                'tstamp'    => 'tstamp',
                'crdate'    => 'crdate',
                'cruser_id' => 'cruser_id',
                'default_sortby' => 'label_de',
                'delete' => 'deleted',
                'enablecolumns' => array (
                        'disabled' => 'hidden',
                        'starttime' => 'starttime',
                        'endtime' => 'endtime',
                ),
                'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
                'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_group.gif',
        ),
        "feInterface" => array (
                "fe_admin_fieldList" => "hidden, starttime, endtime, label_de, label_fr, label_en, cat_id",
        )
);

$TCA["tx_mrastp_persons_groups_rel"] = array(
        "ctrl" => array (
                'title'     => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_persons_groups_rel',
                'label'     => 'groupid',
		'label_alt' => 'personid',
                'label_alt_force' => 1,
                'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
                'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_mrastp_group.gif',
        ),
);

// plugin1
// add FlexForm field to tt_content
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
// add flexform definition
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:mr_astp/pi1/flexform_ds.xml');
// add frontend plugins
t3lib_extMgm::addPlugin(array('LLL:EXT:' . $_EXTKEY . '/pi1/locallang.xml:plugin_name.pi1', $_EXTKEY.'_pi1'));

// plugin2
// add FlexForm field to tt_content
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
// add flexform definition
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:mr_astp/pi2/flexform_ds.xml');
// add frontend plugins
t3lib_extMgm::addPlugin(array('LLL:EXT:' . $_EXTKEY . '/pi2/locallang.xml:plugin_name.pi2', $_EXTKEY.'_pi2'));

t3lib_extMgm::addStaticFile($_EXTKEY,'static/', 'astp Database');

?>
