$(document).ready(function(){
	var id_combination = parseInt($('#idCombination').val());
	delivery.refresh(id_combination);
	delivery.getSlecialColors();
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

var delivery = {
	refresh: function(id_combination) {

		this.getTime(id_combination).success(function(delivery){
			if(delivery.name != null)
				$('.js-delivery-time').text(delivery.name);
			if(delivery.label && delivery.label != null){
				$('.js-delivery-time').addClass('text-success');
			} else {
				$('.js-delivery-time').removeClass('text-success');
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
	getSlecialColors: function() {

		$.ajax({
		  type: 'POST',
		  url: baseDir + 'modules/productdeliverytabs/productdeliverytabs-ajax.php',
		  data: {
		  	'id_product': id_product,
		  	'method': 'getSpecialColors'
		  },
		  dataType: 'json',
		  success: function(attributtes) {
		  		$.each(attributtes, function(k, attributte){
		  			$('#color_' + attributte).addClass('color_pick_extra');
		  		});
		  }
		});

	}
}