<?php

	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');
	require_once('productdeliverytabs.php');

	$id_product = Tools::getValue('id_product');

	switch (Tools::getValue('method')) {
	  case 'getDeliveryTime' :
	    	
	    	$id_product_attribute = Tools::getValue('id_product_attribute');
			$id_lang = Tools::getValue('id_lang');
			$delivery = Db::getInstance()->getRow('SELECT pdt.id_supplier, pdtl.label FROM `'._DB_PREFIX_.'productdeliverytabs` pdt LEFT JOIN `'._DB_PREFIX_.'productdeliverytabs_labels` pdtl ON (pdt.id_supplier = pdtl.id_supplier) WHERE pdt.id_product_attribute ='.(int)$id_product_attribute);

			if($delivery['id_supplier']) {
				$supplier = new Supplier((int)$delivery['id_supplier'], $id_lang);
				$supplier->label = $delivery['label'];
			} else {
				$product = new Product((int)$id_product);
				$supplier = new Supplier((int)$product->id_supplier, $id_lang);
				$supplier->label = Db::getInstance()->getValue('SELECT label FROM `'._DB_PREFIX_.'productdeliverytabs_labels` WHERE id_supplier ='.(int)$product->id_supplier);
			}

			$json = array(
				'name' => $supplier->name,
				'label' => (bool)$supplier->label
			);

			die(Tools::jsonEncode($json));
	    break;

	  case 'getSpecialAttributes' :
	    	
		$attributes = Productdeliverytabs::getLabeledAttributesByIdProduct((int)$id_product);

			die(Tools::jsonEncode($attributes));
	    break;


	  default:
	    exit;
	}

	exit;
