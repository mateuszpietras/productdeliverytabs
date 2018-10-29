<?php

	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');
	require_once('productdeliverytabs.php');

	$id_product = Tools::getValue('id_product');

	switch (Tools::getValue('method')) {

	  case 'getSpecialAttributes' :
	    	
		$attributes = Productdeliverytabs::getLabeledAttributesByIdProduct((int)$id_product);

			die(Tools::jsonEncode($attributes));
	    break;


	  default:
	    exit;
	}

	exit;
