var specialCombinations = false;

var delivery = {

	el: '.box-product .js-delivery-time',

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
		if(combination.className) {
			$('#deliveryAlert').fadeOut();
			$(this.el).removeClass(function (index, className) {
			    return (className.match (/\blabel_\d*/g) || []).join(' ');
			}).addClass(combination.className);
		} else {
			$('#deliveryAlert').fadeIn();
			$(this.el).removeClass(function (index, className) {
			    return (className.match (/\blabel_\d*/g) || []).join(' ');
			})
		}

	},

	removeClassFromColorAttribut: function() {

		$('[class*="color_"]').removeClass(function (index, className) {
			    return (className.match (/\blabel_\d*/g) || []).join(' ');
		});

	},

	getSpecialColors: function(selectedCombination) {


		var selectedRadioAttribute = typeof(selectedCombination.attributes.radio) !== "undefined" ? selectedCombination.attributes.radio : false;
		var selectedSelectAttribute = typeof(selectedCombination.attributes.select) !== "undefined" ? selectedCombination.attributes.select : false;
		
		this.removeClassFromColorAttribut();

		var _this = this;

		$.each(specialCombinations, function(id_combination, combination) {

			if(typeof(selectedCombination.attributes.color) !== "undefined") {

				if((selectedRadioAttribute || selectedSelectAttribute) && combination.className) {

					if(_this.checkAttributes(combination.attributes.radio, selectedRadioAttribute) || _this.checkAttributes(combination.attributes.select, selectedSelectAttribute))
						$.each(combination.attributes.color, function(k, id_attribute) {

							$('#color_' + id_attribute).removeClass(function (index, className) {
								    return (className.match (/\blabel_\d*/g) || []).join(' ');
							}).addClass(combination.className);

						});

				} else if(!combination.attributes.radio && !combination.attributes.select && combination.className) {

						$.each(combination.attributes.color, function(k, id_attribute) {

							$('#color_' + id_attribute).removeClass(function (index, className) {
								    return (className.match (/\blabel_\d*/g) || []).join(' ');
							}).addClass(combination.className);

						});
				}

			}

		});

	},

	checkAttributes: function(attributes, selectAttributes) {


		if(!selectAttributes)
			return false;

		if(attributes.length == selectAttributes.length) {
			var output = true;

			$.each(attributes, function(k, id_attribute) {
				
				if(id_attribute != selectAttributes[k]){
					output = false;
				}	

			});

			return output;

		} else {
			return false;
		}

	}

}
