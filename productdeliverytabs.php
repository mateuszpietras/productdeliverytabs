<?php
/**
*  @author    Rafał Woźniak <rafal.wozniak@mirjan24.com>
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Productdeliverytabs extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'productdeliverytabs';
        $this->tab = 'content_management';
        $this->version = '1.0.0';
        $this->author = 'Rafał Woźniak';
        $this->need_instance = 0;
        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Product Delivery Time');
        $this->description = $this->l('Display a dalivery time on product page');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall my module?'); 

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }
    
    public function install()
    {

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayBackOfficeTop') &&
            $this->registerHook('displayDeliveryTime') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayAdminProductsExtra');
    }

    public function uninstall()
    {

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    public function getContent()
    {

        if (((bool)Tools::isSubmit('submitSuppliersLabels')) == true) {
            $this->postProcess();
        }

        $suppliers = Supplier::getSuppliers();

        foreach ($suppliers as &$supplier) {
           $supplier['label'] = (int)$this->getLabelByIdSupplier($supplier['id_supplier']);
           $supplier['color'] = $this->getLabelColorByIdSupplier($supplier['id_supplier']);
        }

        $this->context->smarty->assign(
            array(
                'suppliers' => $suppliers,
                'form_link' => $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules')
            )
        );

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
    }

    public function postProcess() {

        $this->resetSuppliersLabels();
        $colors = Tools::getValue('color');
        $css = '#attributes [class*="label_"]::before {
            content: "sd";
            display: none;
            width: 22px;
            height: 22px;
            background: #fff;
            position: absolute;
            border: 2px solid;
            border-radius: 2px;
            line-height: 1.6;
            text-align: center;
            font-weight: 500;
            transform: translate(30px,-3px);
            box-shadow: 0 3px 8px rgba(0,0,0,.2);
            transition: .2s ease;
        }';
        if($labels = Tools::getValue('label'))
        foreach ($labels as $id_supplier => $label) {
            $this->updateSupplierLabels($id_supplier, true, $colors[$id_supplier]);
            $css .= '.label_'.$id_supplier.'{color: '.$colors[$id_supplier].';}.label_'.$id_supplier.'::before { display: block !important; color: '.$colors[$id_supplier].';}';
        }

        file_put_contents(_PS_MODULE_DIR_.'productdeliverytabs/views/css/labels.css', $css);

    }

    public function updateSupplierLabels($id_supplier, $label, $color = '#333') {

        $supplier_exists = Db::getInstance()->getValue('SELECT id_supplier FROM `'._DB_PREFIX_.'productdeliverytabs_labels` WHERE id_supplier ='.(int)$id_supplier);

        if($supplier_exists) 
            return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'productdeliverytabs_labels` SET label = 1, color = "'.pSQL($color).'" WHERE id_supplier = '.(int)$id_supplier);
         else
            return Db::getInstance()->execute('INSERT INTO `'._DB_PREFIX_.'productdeliverytabs_labels` (`id_supplier`, `label`, `color`) VALUES ('.(int)$id_supplier.','.(int)$label.',"'.pSQL($color).'")');

    
    }

    public static function getLabelByIdSupplier($id_supplier) {

        return Db::getInstance()->getValue('SELECT label FROM `'._DB_PREFIX_.'productdeliverytabs_labels` WHERE id_supplier ='.(int)$id_supplier);
    }
    
    public static function getLabelColorByIdSupplier($id_supplier) {

        return Db::getInstance()->getValue('SELECT color FROM `'._DB_PREFIX_.'productdeliverytabs_labels` WHERE id_supplier ='.(int)$id_supplier);
    }

    public function resetSuppliersLabels() {

        return Db::getInstance()->execute('UPDATE `'._DB_PREFIX_.'productdeliverytabs_labels` SET label = 0');
    }

    public function hookDisplayAdminProductsExtra() 
    {

        $product = new Product((int)Tools::getValue('id_product'), false, $this->context->language->id);
        $suppliers = Supplier::getSuppliers(false, $this->context->language->id);

        $this->context->smarty->assign('options', $suppliers);
        $this->context->smarty->assign('product_id_supplier', $product->id_supplier);
        $this->context->smarty->assign('productCombinations', $this->renderListAttributes($product));

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/tab.tpl');
   }

   public function hookDisplayDeliveryTime($params) {

        if(isset($params['id_product_attribute'])) {
            $id_supplier = $this->getIdSupplierByIdProductAttribute((int)$params['id_product_attribute']) ? $this->getIdSupplierByIdProductAttribute((int)$params['id_product_attribute']) : $params['id_supplier'];
        } else {
            $id_supplier = $params['id_supplier'];
        }

        $supplier = new Supplier((int)$id_supplier, $this->context->language->id);

        $delivery = array(
            'id_supplier' => $supplier->id_supplier,
            'name' => $supplier->name,
            'label' => $this->getLabelByIdSupplier((int)$id_supplier)
        );

        $this->context->smarty->assign('delivery', $delivery);

        return $this->context->smarty->fetch($this->local_path.'views/templates/product-delivery-time.tpl');

   }

   public function getIdSupplierByIdProductAttribute($id_product_attribute) {

        return Db::getInstance()->getValue('SELECT id_supplier FROM `'._DB_PREFIX_.'productdeliverytabs` WHERE id_product_attribute ='.(int)$id_product_attribute);
   }

    public function renderListAttributes($product)
    {

        if ($product->id) {
            
            $combinations = $product->getAttributeCombinations($this->context->language->id);
            $comb_array = array();

            if (is_array($combinations)) {

                foreach ($combinations as $k => $combination) {

                    $comb_array[$combination['id_product_attribute']]['id_product_attribute'] = $combination['id_product_attribute'];
                    $comb_array[$combination['id_product_attribute']]['attributes'][] = array($combination['group_name'], $combination['attribute_name'], $combination['id_attribute']);
                    $comb_array[$combination['id_product_attribute']]['selected'] = $this->getIdSupplierByIdProductAttribute($combination['id_product_attribute']) ? $this->getIdSupplierByIdProductAttribute($combination['id_product_attribute']) : $product->id_supplier;
                }
            }

            if (isset($comb_array)) {
                foreach ($comb_array as $id_product_attribute => $product_attribute) {
                    $list = '';

                    asort($product_attribute['attributes']);

                    foreach ($product_attribute['attributes'] as $attribute) {
                        $list .= $attribute[0].' - '.$attribute[1].', ';
                    }

                    $list = rtrim($list, ', ');
                    $comb_array[$id_product_attribute]['attributes'] = $list;
                    $comb_array[$id_product_attribute]['name'] = $list;
                }
            }
        }

        return $comb_array;
    }

    public function hookHeader()
    {
        $this->context->controller->addCSS($this->_path.'/views/css/labels.css');

        if ($this->context->controller->php_self == 'product')
            $this->context->controller->addJS($this->_path.'/views/js/front/productdeliverytabs.js');

    }

    public function hookBackOfficeHeader()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path.'/views/js/back/productdeliverytabs.js');

    }

    public static function getLabeledAttributesByIdProduct($id_product, $id_lang = 2){


        $combinations = Db::getInstance()->executeS('SELECT DISTINCT pac.id_product_attribute as id FROM `ps_attribute` a
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (a.id_attribute = pac.id_attribute)
                LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.id_product_attribute = pa.id_product_attribute)
                WHERE pa.id_product = '.(int)$id_product);


        $labeled = array();

        foreach ($combinations as $combination) {
                
            $attributes = Db::getInstance()->executeS('SELECT a.id_attribute, pdt.id_supplier, ag.group_type FROM `ps_attribute` a
                LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON (a.id_attribute_group = ag.id_attribute_group)
                LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON (a.id_attribute = pac.id_attribute)
                LEFT JOIN `'._DB_PREFIX_.'product_attribute` pa ON (pac.id_product_attribute = pa.id_product_attribute)
                LEFT JOIN `'._DB_PREFIX_.'productdeliverytabs` pdt ON (pac.id_product_attribute = pdt.id_product_attribute)
                WHERE pac.id_product_attribute = '.(int)$combination['id']);



           $labeled[(int)$combination['id']]['attributes'] = false;
           $labeled[(int)$combination['id']]['className'] = false;

            foreach ($attributes as &$attribute) {

                if($attribute['id_supplier'] == null) {

                     $product = new Product((int)$id_product);               
                     $attribute['id_supplier'] = $product->id_supplier;

                }
                $supplier = new Supplier((int)$attribute['id_supplier'], (int)$id_lang);
                $labeled[(int)$combination['id']]['deliveryName'] = $supplier->name;
                
                $labeled[(int)$combination['id']]['className'] = Productdeliverytabs::getLabelByIdSupplier($attribute['id_supplier']) ? 'label_'.$attribute['id_supplier'] : false;
                    
                switch($attribute['group_type']) {
                        case 'color':
                                $labeled[(int)$combination['id']]['attributes']['color'] = (int)$attribute['id_attribute'];
                            break;

                        case 'radio':
                                $labeled[(int)$combination['id']]['attributes']['radio'] = (int)$attribute['id_attribute'];
                            break;

                        case 'select':
                                $labeled[(int)$combination['id']]['attributes']['select'] = (int)$attribute['id_attribute'];
                            break;
                }
            

            }

        }

        return $labeled;

    }

}
 
