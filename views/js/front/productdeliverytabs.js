var specialCombinations = false;

var delivery = {

	el: '.pb-right-column .js-delivery-time, .pb-center-column .js-delivery-time',

	init: function(id_combination) {

		var _this = this;

		if(!specialCombinations) {
			this.getCombinations().success(function(combinations){
				specialCombinations = combinations;
				_this.getSpecialColors(specialCombinations[parseInt(id_combination)]);
				_this.toggleTime(specialCombinations[parseInt(id_combination)]);
			});
		} else {
			_this.toggleTime(specialCombinations[parseInt(id_combination)]);
			_this.getSpecialColors(specialCombinations[parseInt(id_combination)]);
			

		}
	},

	getCombinations: function() {

		return $.ajax({
		  type: 'POST',
		  url: baseDir + 'modules/productdeliverytabs/productdeliverytabs-ajax.php',
		  data: {
		  	'id_product': id_product,
		  	'method': 'getSpecialAttributes'
		  },
		  dataType: 'json',
		});

	},

	toggleTime: function(combination) {

		$(this.el).text(combination.deliveryName)


		// Toggle a Delivery time
		if(typeof(combination.className) !== "undefined") {
			$(this.el).removeClass(function (index, className) {
			    return (className.match (/\blabel_\d/g) || []).join(' ');
			}).addClass(combination.className);
		} else {
			$(this.el).removeClass(function (index, className) {
			    return (className.match (/\blabel_\d/g) || []).join(' ');
			})
		}

	},

	removeClassFromColorAttribut: function() {

		$('[class*="color_"]').removeClass(function (index, className) {
			    return (className.match (/\blabel_\d/g) || []).join(' ');
		});

	},

	getSpecialColors: function(selectedCombination) {

		var id_radio_attribute = typeof(selectedCombination.attributes.radio) !== "undefined" ? selectedCombination.attributes.radio : false;
		var id_select_attribute = typeof(selectedCombination.attributes.select) !== "undefined" ? selectedCombination.attributes.select : false;

		this.removeClassFromColorAttribut();

		$.each(specialCombinations, function(id_combination, combination) {

			if(combination.attributes) {
				console.log(Boolean(combination.attributes.radio == id_radio_attribute || combination.attributes.select == id_select_attribute))
				if((combination.attributes.radio == id_radio_attribute || combination.attributes.select == id_select_attribute) && combination.className) {

					$('#color_' + combination.attributes.color).removeClass(function (index, className) {
						    return (className.match (/\blabel_\d/g) || []).join(' ');
					}).addClass(combination.className);

				}

			}

		});

	}

}
