export default {
	init($) {
		const select = $('.show_if_set #_price_type');
		hider(select[0].value);
		select.change(({target}) => hider(target.value));
	},
}

/**
 *
 * @param {'fixed' | 'dynamic-sum' | 'dynamic-percentage'} value
 */
function hider(value) {
	const percentageFee = jQuery('.show_if_set ._set_price_percentage_fee_field'),
		setPrice = jQuery('.show_if_set ._set_price_field');

	if (value === 'fixed') {
		percentageFee.hide();
		setPrice.show();
	}

	if (value === 'dynamic-sum') {
		percentageFee.hide();
		setPrice.hide();
	}

	if (value === 'dynamic-percentage') {
		console.log('showing')
		percentageFee.show();
		setPrice.hide();
	}
}
