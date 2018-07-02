<div class="delivery">
	{if $page_name == 'product'}<p class="delivery-label">{l s='Delivery time' mod='productdeliverytabs'}</p>{/if}
	<p class="js-delivery-time{if $delivery.label} text-success{/if}">{$delivery.name}</p>
</div>