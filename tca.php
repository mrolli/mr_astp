<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_mrastp_person"] = array (
	"ctrl" => $TCA["tx_mrastp_person"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,salutation,firstname,name,street,compl,zip,city,canton_id,country_id,phone,mobile,fax,email,lang,section_id,status,entry_date,workaddress,groups,feuser_id"
	),
	"feInterface" => $TCA["tx_mrastp_person"]["feInterface"],
	"columns" => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		"salutation" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.salutation",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array('', 0),
					Array('LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.salutation.I.1', 1),
					Array('LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.salutation.I.2', 2),
				),
                                "minitems" => 0,
                                "maxitems" => 1,
			)
		),
		"firstname" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.firstname",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"name" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.name",
			"config" => Array (
				"type" => "input",
				"size" => "30",
                                "eval" => "required,trim",
			)
		),
		"street" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.street",
			"config" => Array (
				"type" => "input",
				"size" => "30",
                                "eval" => "required,trim",
			)
		),
		"compl" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.compl",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"zip" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.zip",
			"config" => Array (
				"type" => "input",
				"size" => "6",
				"max" => "6",
				"eval" => "required,trim",
			)
		),
		"city" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.city",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"canton_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.canton_id",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_mrastp_canton",
				"foreign_table_where" => "AND tx_mrastp_canton.pid=###CURRENT_PID### ORDER BY tx_mrastp_canton.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"country_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.country_id",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_mrastp_country",
				"foreign_table_where" => "AND tx_mrastp_country.pid=###CURRENT_PID### ORDER BY tx_mrastp_country.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"phone" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.phone",
			"config" => Array (
				"type" => "input",
				"size" => "20",
				"max" => "20",
				"eval" => "required,trim",
			)
		),
		"mobile" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.mobile",
			"config" => Array (
				"type" => "input",
				"size" => "20",
				"max" => "20",
				"eval" => "trim",
			)
		),
		"fax" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.fax",
			"config" => Array (
				"type" => "input",
				"size" => "20",
				"max" => "20",
				"eval" => "trim",
			)
		),
		"email" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.email",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => " trim",
			)
		),
		"lang" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.lang",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.lang.I.0", "0", t3lib_extMgm::extRelPath("mr_astp")."icons/de.gif"),
					Array("LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.lang.I.1", "1", t3lib_extMgm::extRelPath("mr_astp")."icons/fr.gif"),
					Array("LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.lang.I.2", "2", t3lib_extMgm::extRelPath("mr_astp")."icons/it.gif"),
					Array("LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.lang.I.3", "3", t3lib_extMgm::extRelPath("mr_astp")."icons/gb.gif"),
				),
				"size" => 1,
				"maxitems" => 1,
			)
		),
		"section_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.section_id",
			"config" => Array (
				"type" => "select",
				"foreign_table" => "tx_mrastp_section",
				"foreign_table_where" => "AND tx_mrastp_section.pid=###CURRENT_PID### ORDER BY tx_mrastp_section.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"status" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.status",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "tx_mrastp_state",
				"foreign_table_where" => "AND tx_mrastp_state.pid=###CURRENT_PID### ORDER BY tx_mrastp_state.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"entry_date" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.entry_date",
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"workaddress" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.workaddress",
			"config" => Array (
				"type" => "inline",
				"foreign_table" => "tx_mrastp_workaddress",
                                "foreign_field" => "parentuid",
                                "foreign_table_field" => "parenttable",
				"minitems" => 0,
				"maxitems" => 10,
				"appearance" => Array (
                                        "expandSingle" => 1,
					"useSortable" => 1,
				),
			)
		),
                "groups" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.groups",
                        "config" => Array (
                                "type" => "inline",
                                "foreign_table" => "tx_mrastp_persons_groups_rel",
                                "foreign_field" => "personid",
				"foreign_unique" => "groupid",
                                "foreign_label" => "groupid",
                                "minitems" => 0,
                                "maxitems" => 20,
                                "appearance" => Array (
					"expandSingle" => 1,
                                        "useSortable" => 1,
					"newRecordLinkAddTitle" => 1,
					"newRecordLinkPosition" => "top",
                                ),
                        )
                ),
                "feuser_id" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:cms/locallang_tca.php:fe_users",
                        "config" => Array (
                                "type" => "inline",
                                "foreign_table" => "fe_users",
                                "minitems" => 0,
                                "maxitems" => 1,
                                "appearance" => Array (
                                        "expandSingle" => 1,
                                        "useSortable" => 0,
                                ),
                        )
                ),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, salutation, firstname, name, street, compl, zip, city, canton_id, country_id, phone, mobile, fax, email, lang, section_id, status, entry_date, workaddress, groups, feuser_id")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);



$TCA["tx_mrastp_canton"] = array (
	"ctrl" => $TCA["tx_mrastp_canton"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "abbrevation,label_de,label_fr,label_en"
	),
	"feInterface" => $TCA["tx_mrastp_canton"]["feInterface"],
	"columns" => array (
		"abbrevation" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_canton.abbrevation",
			"config" => Array (
				"type" => "input",
				"size" => "5",
				"max" => "2",
				"eval" => "required,trim",
			)
		),
		"label_de" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_de",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"label_fr" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_fr",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"label_en" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_en",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "abbrevation, label_de, label_fr, label_en")
	),
);



$TCA["tx_mrastp_salutation"] = array (
	"ctrl" => $TCA["tx_mrastp_salutation"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "label_de,label_fr"
	),
	"feInterface" => $TCA["tx_mrastp_canton"]["feInterface"],
	"columns" => array (
		"label_de" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_de",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"label_fr" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_fr",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "abbrevation, label_de, label_fr")
	),
);



$TCA['tx_mrastp_country'] = array(
        'ctrl' => $TCA['tx_mrastp_country']['ctrl'],
        'interface' => array(
                'showRecordFieldList' => 'cn_iso_2,cn_iso_3,cn_short_en,cn_short_de,cn_short_fr'
        ),
        'columns' => array(
                'cn_iso_2' => array(
                        'label' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_country.cn_iso_2',
                        'exclude' => '0',
                        'config' => array(
                                'type' => 'input',
                                'size' => '4',
                                'max' => '2',
                                'eval' => '',
                                'default' => ''
                        )
                ),
                'cn_iso_3' => array(
                        'label' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_country.cn_iso_3',
                        'exclude' => '0',
                        'config' => array(
                                'type' => 'input',
                                'size' => '5',
                                'max' => '3',
                                'eval' => '',
                                'default' => ''
                        )
                ),
                'cn_short_en' => array(
                        'label' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_country.cn_short_en',
                        'exclude' => '0',
                        'config' => array(
                                'type' => 'input',
                                'size' => '25',
                                'max' => '50',
                                'eval' => 'trim',
                                'default' => '',
                                '_is_string' => '1'
                        )
                ),
                'cn_short_de' => array(
                        'label' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_country.cn_short_de',
                        'exclude' => '0',
                        'config' => array(
                                'type' => 'input',
                                'size' => '25',
                                'max' => '50',
                                'eval' => 'trim',
                                'default' => '',
                                '_is_string' => '1'
                        )
                ),
                'cn_short_fr' => array(
                        'label' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_country.cn_short_fr',
                        'exclude' => '0',
                        'config' => array(
                                'type' => 'input',
                                'size' => '25',
                                'max' => '50',
                                'eval' => 'trim',
                                'default' => '',
                                '_is_string' => '1'
                        )
                ),
	),
        'types' => array(
                '1' => array(
                        'showitem' => 'cn_short_en,cn_short_de,cn_short_fr,--palette--;;1;;'
                )
        ),
        'palettes'      => array(
                '1' => array(
                        'showitem' => 'cn_iso_2,cn_iso_3', 'canNotCollapse' => '1'
                ),
        )
);



$TCA["tx_mrastp_section"] = array (
	"ctrl" => $TCA["tx_mrastp_section"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "abbrevation,label_de,label_fr"
	),
	"feInterface" => $TCA["tx_mrastp_section"]["feInterface"],
	"columns" => array (
		"abbrevation" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_section.abbrevation",
			"config" => Array (
				"type" => "input",
				"size" => "5",
				"max" => "5",
				"eval" => "required,trim",
			)
		),
		"label_de" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_de",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"label_fr" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_fr",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "abbrevation, label_de, label_fr")
	),
);



$TCA["tx_mrastp_state"] = array (
	"ctrl" => $TCA["tx_mrastp_state"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "abbrevation,label_de,label_fr"
	),
	"feInterface" => $TCA["tx_mrastp_state"]["feInterface"],
	"columns" => array (
		"abbrevation" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_state.abbrevation",
			"config" => Array (
				"type" => "input",
				"size" => "10",
				"max" => "10",
				"eval" => "required,trim",
			)
		),
		"label_de" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_de",
			"config" => Array (
				"type" => "input",
				"size" => "30",
                                "eval" => "required,trim",
			)
		),
		"label_fr" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_fr",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "abbrevation, label_de, label_fr")
	),
);



$TCA["tx_mrastp_workaddress"] = array (
	"ctrl" => $TCA["tx_mrastp_workaddress"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,starttime,endtime,name_practice,name_supplement,address1,address2,zip,city,country_id,canton_id,phone,fax,email,audience,services,languages,website,startofwork"
	),
	"feInterface" => $TCA["tx_mrastp_workaddress"]["feInterface"],
	"columns" => array (
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'employment' => array (
                        'exclude' => 1,
			'label' => 'LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.employment',
			'config' => Array (
				'type' => 'radio',
				'items' => Array (
                                        Array('LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.employment.I.1', 1),
                                        Array('LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.employment.I.2', 2),
                                ),
			'default' => 1,
			),
		),
		"name_practice" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.name_practice",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"name_supplement" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.name_supplement",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"address1" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.address1",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"address2" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.address2",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"zip" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.zip",
			"config" => Array (
				"type" => "input",
				"size" => "6",
				"max" => "6",
				"eval" => "required,trim",
			)
		),
		"city" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.city",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required,trim",
			)
		),
		"country_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.country_id",
			"config" => Array (
				"type" => "select",
				"items" => Array (
					Array("",0),
				),
				"foreign_table" => "static_countries",
				"foreign_table_where" => "AND static_countries.pid=###SITEROOT### ORDER BY static_countries.uid",
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"canton_id" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.canton_id",
                        "config" => Array (
                                "type" => "select",
                                "items" => Array (
                                        Array("",0),
                                ),
                                "foreign_table" => "tx_mrastp_canton",
                                "foreign_table_where" => "AND tx_mrastp_canton.pid=###CURRENT_PID### ORDER BY tx_mrastp_canton.uid",
                                "size" => 1,
                                "minitems" => 0,
                                "maxitems" => 1,
                        )		),
		"phone" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.phone",
			"config" => Array (
				"type" => "input",
				"size" => "20",
				"max" => "20",
				"eval" => "required,trim",
			)
		),
		"fax" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.fax",
			"config" => Array (
				"type" => "input",
				"size" => "20",
				"max" => "20",
				"eval" => "trim",
			)
		),
		"email" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.email",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"audience" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.audience",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"services" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.services",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"languages" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.languages",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"website" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.website",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "trim",
			)
		),
		"startofwork" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_workaddress.startofwork",
			"config" => Array (
				"type"     => "input",
				"size"     => "8",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, employment, name_practice, name_supplement, address1, address2, zip, city, country_id, canton_id, phone, fax, email, audience, services, languages, website, startofwork")
	),
	"palettes" => array (
		"1" => array("showitem" => "starttime, endtime")
	)
);

$TCA["tx_mrastp_group_cat"] = array (
        "ctrl" => $TCA["tx_mrastp_group_cat"]["ctrl"],
        "interface" => array (
                "showRecordFieldList" => "hidden,starttime,endtime,label_de, label_fr"
        ),
        "feInterface" => $TCA["tx_mrastp_group_cat"]["feInterface"],
        "columns" => array (
                'hidden' => array (
                        'exclude' => 1,
                        'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
                        'config'  => array (
                                'type'    => 'check',
                                'default' => '0'
                        )
                ),
                'starttime' => array (
                        'exclude' => 1,
                        'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
                        'config'  => array (
                                'type'     => 'input',
                                'size'     => '8',
                                'max'      => '20',
                                'eval'     => 'date',
                                'default'  => '0',
                                'checkbox' => '0'
                        )
                ),
                'endtime' => array (
                        'exclude' => 1,
                        'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
                        'config'  => array (
                                'type'     => 'input',
                                'size'     => '8',
                                'max'      => '20',
                                'eval'     => 'date',
                                'checkbox' => '0',
                                'default'  => '0',
                                'range'    => array (
                                        'upper' => mktime(0, 0, 0, 12, 31, 2020),
                                        'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
                                )
                        )
                ),
                "label_de" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_cmmon.label_de",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                                "max" => "50",
                                "eval" => "required,trim",
                        ),
                ),
                "label_fr" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_fr",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                                "max" => "50",
                                "eval" => "required,trim",
                        ),
                ),
        ),
        "types" => array (
                "0" => array("showitem" => "hidden;;1;;1-1-1, label_de, label_fr")
        ),
        "palettes" => array (
                "1" => array("showitem" => "starttime, endtime")
        )
);

$TCA["tx_mrastp_group"] = array (
        "ctrl" => $TCA["tx_mrastp_group"]["ctrl"],
        "interface" => array (
                "showRecordFieldList" => "hidden,starttime,endtime,label_de, label_fr,cat_id"
        ),
        "feInterface" => $TCA["tx_mrastp_group"]["feInterface"],
        "columns" => array (
                'hidden' => array (
                        'exclude' => 1,
                        'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
                        'config'  => array (
                                'type'    => 'check',
                                'default' => '0'
                        )
                ),
                'starttime' => array (
                        'exclude' => 1,
                        'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
                        'config'  => array (
                                'type'     => 'input',
                                'size'     => '8',
                                'max'      => '20',
                                'eval'     => 'date',
                                'default'  => '0',
                                'checkbox' => '0'
                        )
                ),
                'endtime' => array (
                        'exclude' => 1,
                        'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
                        "config" => Array (
                                'type'     => 'input',
                                'size'     => '8',
                                'max'      => '20',
                                'eval'     => 'date',
                                'checkbox' => '0',
                                'default'  => '0',
                                'range'    => array (
                                        'upper' => mktime(0, 0, 0, 12, 31, 2020),
                                        'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
                                )
                        )
                ),
                "label_de" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_de",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                                "max" => "50",
                                "eval" => "required,trim",
                        )
                ),
                "label_fr" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_common.label_fr",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                                "max" => "50",
                                "eval" => "required,trim",
                        )
                ),
                "cat_id" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_group.cat_id",
                        "config" => Array (
                                "type" => "select",
                                "foreign_table" => "tx_mrastp_group_cat",
                                "foreign_table_where" => "AND tx_mrastp_group_cat.pid=###CURRENT_PID### ORDER BY tx_mrastp_group_cat.uid",
                                "size" => 1,
                                "minitems" => 0,
                                "maxitems" => 1,
                        )
                ),
                "persons" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_group.persons",
                        "config" => Array (
                                "type" => "inline",
                                "foreign_table" => "tx_mrastp_persons_groups_rel",
                                "foreign_field" => "groupid",
                                "foreign_sortby" => "groupsort",
                                "foreign_label" => "personid",
                                "minitems" => 0,
                                "maxitems" => 20,
                                "appearance" => Array (
					"expandSingle" => 1,
                                        "useSortable" => 1,
                                ),
                        )
                ),
        ),
        "types" => array (
                "0" => array("showitem" => "hidden;;1;;1-1-1, label_de, label_fr, cat_id, persons")
        ),
        "palettes" => array (
                "1" => array("showitem" => "starttime, endtime")
        )
);

$TCA["tx_mrastp_persons_groups_rel"] = array (
        "ctrl" => $TCA["tx_mrastp_persons_groups_rel"]["ctrl"],
	"columns" => Array(
		"personid" => Array(
			"label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_persons_groups_rel.personid",
			"config" => Array(
				"type" => "select",
				"foreign_table" => "tx_mrastp_person",
				"maxitems" => 1,
			)
		),
                "groupid" => Array(
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_persons_groups_rel.groupid",
                        "config" => Array(
                                "type" => "select",
                                "foreign_table" => "tx_mrastp_group",
                                "maxitems" => 1,
                        )
                ),
		"personsort" => Array(
			"config" => Array(
				"type" => "passthrough",
			)
		),
                "groupsort" => Array(
                        "config" => Array(
                                "type" => "passthrough",
                        )
                ),
                "funktion_de" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_persons_groups_rel.funktion_de",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                                "eval" => "trim",
                        )
                ),
                "funktion_fr" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_persons_groups_rel.funktion_fr",
                        "config" => Array (
                                "type" => "input",
                                "size" => "30",
                                "eval" => "trim",
                        )
                ),
                "canton_id" => Array (
                        "exclude" => 1,
                        "label" => "LLL:EXT:mr_astp/locallang_db.xml:tx_mrastp_person.canton_id",
                        "config" => Array (
                                "type" => "select",
                                "items" => Array (
                                        Array("",0),
                                ),
                                "foreign_table" => "tx_mrastp_canton",
                                "foreign_table_where" => "AND tx_mrastp_canton.pid=###CURRENT_PID### ORDER BY tx_mrastp_canton.uid",
                                "size" => 1,
                                "minitems" => 0,
                                "maxitems" => 1,
                        )
                ),
	),
        "types" => array (
                "0" => array("showitem" => "hidden;;1;;1-1-1, personid, groupid, personsort, groupsort, funktion_de, funktion_fr, canton_id")
        ),
        "palettes" => array (
                "1" => array("showitem" => "starttime, endtime")
        )
);

?>
