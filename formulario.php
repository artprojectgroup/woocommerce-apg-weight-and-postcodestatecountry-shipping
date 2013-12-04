<style type="text/css">
div.donacion {
	background: #FFFFE0;
	border: 1px solid #E6DB55;
	float: right;
	margin: 10px 0px;
	padding: 10px;
	width: 220px;
	text-align: center;
}
div.donacion div {
	padding: 10px;
	margin: 10px auto 0px;
	width: 190px;
	border-top: 1px solid #E6DB55;
}
.cabecera img {
	border: 4px solid #888888;
}
form, .enlace {
	padding-left: 25px;
}
label {
	font-weight: bold;
}
.submit {
	margin-top: 10px;
}
th.titledesc {
    font-weight: bold;
}
.chosen_select {
	width: 280px!important;
}
.chzn-choices, chzn-container-active {
	border: none!important;
	box-shadow: none!important;
	background: none!important;
}
.search-field {
	text-align: right;
	width: 99%;
}
.chzn-container, input[type="text"], textarea, select {
	background-color: #FCFCFC;
	border: 1px solid #E0E0E0;
	color: #696868;
	font-weight: 300;
	min-width: 188px;
	padding: 8px 10px!important;
}
input[type="text"], textarea, select {
	height: 30px!important;
}
input[type="text"], select {
	max-width: 98%;
	width: 300px!important;
}
textarea {
	float: none;
	height: 150px!important;
	width: 25%;
	min-width: 582px;
}
input[type="submit"] {
	background: #fcfcfc!important;
	-webkit-box-shadow: 0 0 3px rgba(255,255,255,1) inset!important;
	-moz-box-shadow: 0 0 3px rgba(255,255,255,1) inset!important;
	box-shadow: 0 0 3px rgba(255,255,255,1) inset!important;
	background: -webkit-gradient(linear, left top, left bottom, from(#fcfcfc), to(#e2e2e2))!important; /* Webkit */
	background: -moz-linear-gradient(top, #fcfcfc, #e2e2e2)!important; /* Firefox */
 filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfcfc', endColorstr='#e2e2e2')!important; /* Internet Explorer */
	border: 1px solid #D9D9D9!important;
	border-radius: 3px!important;
	color: #3B3B39 !important;
	padding: 7px 1.7em !important;
	text-shadow: 0 1px 0 white!important;
	height: auto!important;
}
input:focus, textarea:focus, select:focus, input:hover, textarea:hover, select:hover {
	border-color: #D9001D!important;
	outline: medium none!important;
	-webkit-box-shadow: 0 0 5px rgba(217, 0, 29,0.75)!important;
	-moz-box-shadow: 0 0 5px rgba(217, 0, 29,0.75)!important;
	box-shadow: 0 0 5px rgba(217, 0, 29,0.75)!important;
}
input[type="submit"]:focus, input[type="submit"]:hover {
	cursor: pointer;
	text-decoration: none;
}
</style>

<h3>
  <?php _e('Weight and Postcode/State/Country based shipping', 'apg_shipping'); ?>
</h3>
<p>
  <?php _e('Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands. For more help and know how to use the plugin visit <a href="http://www.artprojectgroup.es/plugins-para-wordpress/woocommerce-apg-weight-and-postcodestatecountry-shipping" target="_blank">WooCoomerce - APG Weight and Postcode/State/Country Shipping</a>.', 'apg_shipping'); ?>
</p>
<div class="donacion">
  <?php _e('If you enjoyed and find helpful this plugin, please make a donation.', 'apg_shipping'); ?>
  <div><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LB54JTPQGW9ZW" target="_blank" title="PayPal"><img alt="WooCoomerce - APG Weight and Postcode/State/Country Shipping" border="0" src="<?php _e('https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif', 'apg_shipping'); ?>" width="92" height="26"></a></div>
</div>
<div class="cabecera"> <a href="http://www.artprojectgroup.es/plugins-para-wordpress/woocommerce-apg-weight-and-postcodestatecountry-shipping" title="WooCommerce - APG Weight and Postcode/State/Country Shipping"><img src="http://www.artprojectgroup.es/wp-content/artprojectgroup/woocommerce-apg-weight-and-postcodestatecountry-shipping-582x139.jpg" width="582" height="139" /></a> </div>
<table class="form-table">
  <?php $this->generate_settings_html(); ?>
</table>
<!--/.form-table--> 
