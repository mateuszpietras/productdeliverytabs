<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @property Product $object
 */
class AdminProductsController extends AdminProductsControllerCore
{
    public function processDuplicate() 
    {    
        if (Validate::isLoadedObject($product = new Product((int)Tools::getValue('id_product'))))
        {
            $id_product_old = $product->id;
            if (empty($product->price) && Shop::getContext() == Shop::CONTEXT_GROUP)
            {
                $shops = ShopGroup::getShopsFromGroup(Shop::getContextShopGroupID());
                foreach ($shops as $shop)
                    if ($product->isAssociatedToShop($shop['id_shop']))
                    {
                        $product_price = new Product($id_product_old, false, null, $shop['id_shop']);
                        $product->price = $product_price->price;
                    }
            }
            unset($product->id);
            unset($product->id_product);
            unset($product->ean13);
            $product->indexed = 0;
            $product->active = 0;

            if ($product->add()
            && Category::duplicateProductCategories($id_product_old, $product->id)
            && Product::duplicateSuppliers($id_product_old, $product->id)
            && ($combination_images = Product::duplicateAttributes($id_product_old, $product->id)) !== false
            && GroupReduction::duplicateReduction($id_product_old, $product->id)
            && Product::duplicateAccessories($id_product_old, $product->id)
            && Product::duplicateFeatures($id_product_old, $product->id)
            && Product::duplicateSpecificPrices($id_product_old, $product->id)
            && Pack::duplicate($id_product_old, $product->id)
            && Product::duplicateCustomizationFields($id_product_old, $product->id)
            && Product::duplicateTags($id_product_old, $product->id)
            && Product::duplicateDownload($id_product_old, $product->id))
            {
                
                if ($product->hasAttributes()) {
                    Product::updateDefaultAttribute($product->id);
                    Product::cleanProductEAN($product->id);
                }

                if (!Tools::getValue('noimage') && !Image::duplicateProductImages($id_product_old, $product->id, $combination_images))
                    $this->errors[] = Tools::displayError('An error occurred while copying images.');
                else
                {
                    Hook::exec('actionProductAdd', array('id_product' => (int)$product->id, 'product' => $product));
                    if (in_array($product->visibility, array('both', 'search')) && Configuration::get('PS_SEARCH_INDEXATION'))
                        Search::indexation(false, $product->id);
                    $this->redirect_after = self::$currentIndex.(Tools::getIsset('id_category') ? '&id_category='.(int)Tools::getValue('id_category') : '').'&conf=19&token='.$this->token;
                }
            }
            else
                $this->errors[] = Tools::displayError('An error occurred while creating an object.');
        }
    }
    public function ajaxProcessDeleteProductAttribute()
    {
        if (!Combination::isFeatureActive()) {
            return;
        }

        if ($this->tabAccess['delete'] === '1') {
            $id_product = (int)Tools::getValue('id_product');
            $id_product_attribute = (int)Tools::getValue('id_product_attribute');

            if ($id_product && Validate::isUnsignedId($id_product) && Validate::isLoadedObject($product = new Product($id_product))) {
                if (($depends_on_stock = StockAvailable::dependsOnStock($id_product)) && StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute)) {
                    $json = array(
                        'status' => 'error',
                        'message'=> $this->l('It is not possible to delete a combination while it still has some quantities in the Advanced Stock Management. You must delete its stock first.')
                    );
                } else {
                    $product->deleteAttributeCombination((int)$id_product_attribute);
                    $product->deleteDeliveryTimeByIdProductAttribute((int)$id_product_attribute);
                    $product->checkDefaultAttributes();
                    Tools::clearColorListCache((int)$product->id);
                    if (!$product->hasAttributes()) {
                        $product->cache_default_attribute = 0;
                        $product->update();
                    } else {
                        Product::updateDefaultAttribute($id_product);
                    }

                    if ($depends_on_stock && !Stock::deleteStockByIds($id_product, $id_product_attribute)) {
                        $json = array(
                            'status' => 'error',
                            'message'=> $this->l('Error while deleting the stock')
                        );
                    } else {
                        $json = array(
                            'status' => 'ok',
                            'message'=> $this->_conf[1],
                            'id_product_attribute' => (int)$id_product_attribute
                        );
                    }
                }
            } else {
                $json = array(
                    'status' => 'error',
                    'message'=> $this->l('You cannot delete this attribute.')
                );
            }
        } else {
            $json = array(
                'status' => 'error',
                'message'=> $this->l('You do not have permission to delete this.')
            );
        }

        die(Tools::jsonEncode($json));
    }
    
    public function ajaxProcessAssignDeliveryTimes()
    {

        $id_product = (int)Tools::getValue('id_product');
        $id_product_attribute = (int)Tools::getValue('id_product_attribute');
        $id_supplier = (int)Tools::getValue('id_delivery_time');
        $product = new Product($id_product);

        if($product->id_supplier != $id_supplier) {

            if(Product::getIdSupplierByIdProductAttribute($id_product_attribute)) {

                $sql = 'UPDATE `'._DB_PREFIX_.'productdeliverytabs` SET id_supplier ='.(int)$id_supplier.' WHERE id_product_attribute ='.(int)$id_product_attribute;

            } else {

                $sql = 'INSERT INTO `'._DB_PREFIX_.'productdeliverytabs` (id_product, id_product_attribute, id_supplier) VALUES ('.$id_product.', '.$id_product_attribute.', '.$id_supplier.')';
            }

            $json = array(
                'status' => Db::getInstance()->execute($sql),
                'message' => $this->l('Attribute has been updated')
            );

        } else {
            
            if(Product::getIdSupplierByIdProductAttribute($id_product_attribute)) {

                $sql = Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'productdeliverytabs` WHERE id_product_attribute = '.(int)$id_product_attribute);
                $json = array(
                        'status' => $sql,
                        'message' => $this->l('Attribute has been updated')
                );

            } else {
                $json = array(
                    'status' => false,
                );  
            }       

        }

        die(Tools::jsonEncode($json));
        
    }
    
    public function ajaxProcessResetDeliveryTime()
    {
        $id_product = (int)Tools::getValue('id_product');
        die(Tools::jsonEncode(Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'productdeliverytabs` WHERE id_product = '.(int)$id_product)));
    }

}
