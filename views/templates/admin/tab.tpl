
   <div class="panel">

      <div class="panel-heading">
         <i class="icon-truck icon-fw"></i>{l s='Delivery time'}
      </div>

        <div class="form-wrapper">
          <input type="hidden" value="{$product_id_supplier}" id="product-id-supplier">
          <table class="table table-hover table-delivery-times">
            
            <thead>
                <tr class="nodrag nodrop">
                    <th class="left">{l s="Attribute - value pair"}</td>
                    <th class="left">{l s="Delivery time"}</th>
                </tr>
             </thead>

                <tbody>
                  {foreach from=$productCombinations item=combination}

                  <tr class="delivery-attribute" data-id-attribute="{$combination.id_product_attribute}">

                        <td class="left">{$combination.attributes}</td>
                        <td class="left">
                          <select name="delivery_time" id="delivery_time_{$combination.id_product_attribute}">
                            {foreach from=$options item=option}
                                <option value="{$option['id_supplier']}"{if $option['id_supplier'] == $combination.selected} selected{/if}>{$option['name']}</option>
                            {/foreach}
                          </select>
                        </td>

                  </tr>

                  {/foreach}
                </tbody>
          </table>

          </div>
          <!-- /.form-wrapper -->
    		  <div class="panel-footer">
                <button type="submit" id="submitDeliveryTimes" class="btn btn-default pull-right"><i class="process-icon-save"></i>{l s='Save'}</button>
          </div>

   </div>

