<?php

class Product extends ProductCore
{

    public function deleteDeliveryTimeByIdProductAttribute($id_product_attribute) {

    	return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'productdeliverytabs` WHERE id_product_attribute ='.(int)$id_product_attribute);

    }

    public function deleteDeliveryTimeByIdProduct($id_product) {

        return Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'productdeliverytabs` WHERE id_product_attribute ='.(int)$id_product);

    }

    public static function getIdSupplierByIdProductAttribute($id_product_attribute) {

        return Db::getinstance()->getValue('SELECT id_supplier FROM `'._DB_PREFIX_.'productdeliverytabs` WHERE id_product_attribute ='.(int)$id_product_attribute);

    }
    
    public static function cleanProductEAN($id_product_new) {
        
        return Db::getInstance()->execute('UPDATE '._DB_PREFIX_.'product_attribute SET ean13 = "" WHERE `id_product` = '.$id_product_new);
    }

}
