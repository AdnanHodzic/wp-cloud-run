(function($) {
	$('#basic-purchase').on('click', function (e) {
		var basicHandler = FS.Checkout.configure({
			plugin_id:  '15612',
			plan_id:    '26003',
			public_key: 'pk_03614134dfcb8c962b0c434fd53fa',
			image:      'https://lightpress.io/wp-content/uploads/2024/06/lightpress-icon-300px.png'
		});
		basicHandler.open({
			name     : 'LightPress Pro - Basic',
			licenses : $('#basic-licenses').val(),
			// You can consume the response for after purchase logic.
			purchaseCompleted  : function (response) {
				// The logic here will be executed immediately after the purchase confirmation.
				// alert(response.user.email);
			},
			success  : function (response) {
				// The logic here will be executed after the customer closes the checkout, after a successful purchase.                                
				// alert(response.user.email);
			}
		});
		e.preventDefault();
	});
	
	$('#pro-purchase').on('click', function (e) {
		var proHandler = FS.Checkout.configure({
			plugin_id:  '15612',
			plan_id:    '26004',
			public_key: 'pk_03614134dfcb8c962b0c434fd53fa',
			image:      'https://lightpress.io/wp-content/uploads/2024/06/lightpress-icon-300px.png'
		});
		proHandler.open({
			name     : 'LightPress Pro',
			licenses : $('#pro-licenses').val(),
			// You can consume the response for after purchase logic.
			purchaseCompleted  : function (response) {
				// The logic here will be executed immediately after the purchase confirmation.
				// alert(response.user.email);
			},
			success  : function (response) {
				// The logic here will be executed after the customer closes the checkout, after a successful purchase.                                
				// alert(response.user.email);
			}
		});
		e.preventDefault();
	});
	
	$('#enterprise-purchase').on('click', function (e) {
		var enterpriseHandler = FS.Checkout.configure({
			plugin_id:  '15612',
			plan_id:    '26005',
			public_key: 'pk_03614134dfcb8c962b0c434fd53fa',
			image:      'https://lightpress.io/wp-content/uploads/2024/06/lightpress-icon-300px.png'
		});
		enterpriseHandler.open({
			name     : 'LightPress Pro - Enterprise',
			licenses : $('#enterprise-licenses').val(),
			// You can consume the response for after purchase logic.
			purchaseCompleted  : function (response) {
				// The logic here will be executed immediately after the purchase confirmation.
				// alert(response.user.email);
			},
			success  : function (response) {
				// The logic here will be executed after the customer closes the checkout, after a successful purchase.                                
				// alert(response.user.email);
			}
		});
		e.preventDefault();
	});
})( jQuery );
