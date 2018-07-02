<?php

	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	require_once(dirname(__FILE__).'/../../init.php');

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

	  case 'getSpecialColors' :
	    	
		$sql = 'SELECT DISTINCT a.id_attribute FROM `ps_attribute` a
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (a.id_attribute_group = ag.id_attribute_group)
				LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (a.id_attribute = pac.id_attribute)
				LEFT JOIN `'._DB_PREFIX_.'productdeliverytabs` pdt ON (pac.id_product_attribute = pdt.id_product_attribute)
				LEFT JOIN `'._DB_PREFIX_.'productdeliverytabs_labels` pdtl ON (pdt.id_supplier = pdtl.id_supplier)
				WHERE pdt.id_product = '.(int)$id_product.' AND ag.group_type = "color" AND pdtl.label = 1';

		$attributes = Db::getInstance()->executeS($sql);
		$json = array();
		
		foreach ($attributes as $attribute) {
			$json[] = $attribute['id_attribute'];
		}

			die(Tools::jsonEncode($json));
	    break;


	  default:
	    exit;
	}

	exit;