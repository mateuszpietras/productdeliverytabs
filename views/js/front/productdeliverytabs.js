$(document).ready(function(){
	var id_combination = parseInt($('#idCombination').val());
	delivery.refresh(id_combination);
	delivery.getSpecialAttributes();
});

$(document).on('click', '.color_pick', function(e){
	e.preventDefault();
	var id_combination = parseInt($('#idCombination').val());
	delivery.refresh(id_combination);
});

$(document).on('change', '.attribute_select', function(e){
	e.preventDefault();
	var id_combination = parseInt($('#idCombination').val());
	delivery.refresh(id_combination);
});

$(document).on('click', '.attribute_radio', function(e){
	e.preventDefault();
	var id_combination = parseInt($('#idCombination').val());
	delivery.refresh(id_combination);
});

var combination_supplier_id;

var delivery = {
	refresh: function(id_combination) {

		this.getTime(id_combination).success(function(delivery){

			var selector = $('.pb-right-column .js-delivery-time, .pb-center-column .js-delivery-time');

			if(delivery.name != null)
				selector.text(delivery.name);
			if(delivery.label && delivery.label != null){
				selector.removeClass(function (index, className) {
				    return (className.match (/\blabel_\d/g) || []).join(' ');
				}).addClass('label_' + delivery.id_supplier);

			} else {
				selector.removeClass(function (index, className) {
				    return (className.match (/\blabel_\d/g) || []).join(' ');
				});
			}
		});
	},
	getTime: function(id_combination) {
		return $.ajax({
		  type: 'POST',
		  url: baseDir + 'modules/productdeliverytabs/productdeliverytabs-ajax.php',
		  data: {
		  	'id_product_attribute': id_combination,
		  	'id_lang': id_lang,
		  	'id_product': id_product,
		  	'method': 'getDeliveryTime'
		  },
		  dataType: 'json',
		});

	},
	getSpecialAttributes: function() {

		$.ajax({
		  type: 'POST',
		  url: baseDir + 'modules/productdeliverytabs/productdeliverytabs-ajax.php',
		  data: {
		  	'id_product': id_product,
		  	'method': 'getSpecialAttributes'
		  },
		  dataType: 'json',
		  success: function(attributtes) {
		  	$.each(attributtes.color, function(k, attributte){
		  		$('#color_' + attributte).addClass('color_pick_extra');
		  	});
		  	
		  	$.each(attributtes.radio, function(k, attributte){
		  		$('#radio_' + attributte).parent().find('label').addClass('extra-label');
		  	});

		  	$.each(attributtes.select, function(k, attributte){
				$('option[value="' + attributte + '"]').addClass('extra-option');
			});

		  }
		});

	}
}
