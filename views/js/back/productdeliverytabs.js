
$(document).on('click', '#submitDeliveryTimes', function(e){
		e.preventDefault();

		var deliveryCombinations;
		var product_id_supplier = parseInt($('#product-id-supplier').val());

		$('.delivery-attribute').each(function(k, v){
			var id_attribute = parseInt($(this).attr('data-id-attribute'));
			var selected_delivery_time = parseInt($('#delivery_time_' + id_attribute).val());
			assignDeliveryTimes(id_attribute, selected_delivery_time);
		});

});

$(document).on('click', '#resetDelivery', function(e){
		e.preventDefault();


		alert($(this).attr('data-alert'));

		$.ajax({
			url: location.href,
			data: {
				id_product: id_product,
				ajax: true,
				action: 'resetDeliveryTime'
			},
			dataType: "json",
			success: function(response) {
				location.reload();
			}
		})

});


function assignDeliveryTimes(id_attribute, selected_delivery_time) {

	$.ajax({
		url: location.href,
		data: {
			id_product_attribute: id_attribute,
			id_delivery_time: selected_delivery_time,
			id_product: id_product,
			ajax: true,
			action: 'assignDeliveryTimes'
		},
		dataType: "json",
		success: function(response) {
				if(response.status)
					showSuccessMessage(response.message);
		}
	})
}
