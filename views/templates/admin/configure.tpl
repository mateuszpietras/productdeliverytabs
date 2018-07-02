

<div class="panel">
<form method="post" action="{$form_link}" enctype="multipart/form-data" id="supplier-form">
	<div class="form-wrapper">
		<div class="table-responsive">
			<table class="table table-hover suppliers-table">
			<thead>
				
				<tr>
					<th>{l s='ID' mod='productdeliverytabs'}</th>
					<th>{l s='Supplier Name' mod='productdeliverytabs'}</th>
					<th class="text-center">{l s='Extra label' mod='productdeliverytabs'}</th>
				</tr>

			</thead>

			<tbody>
			{foreach from=$suppliers item=supplier}
				
				<tr>
					<td>{$supplier.id_supplier}</td>
					<td>{$supplier.name}</td>
					<td class="text-center">
						<input name="label[{$supplier.id_supplier}]" type="checkbox" {if $supplier.label} checked{/if}>
					</td>
				</tr>
				
			{/foreach}
			</tbody>
			</table>
		</div>
	</div>
	<div class="panel-footer">
	        <button type="submit" name="submitSuppliersLabels" class="btn btn-default pull-right"><i class="process-icon-save"></i>Speichern</button>
	</div>
</form>
</div>

