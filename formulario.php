<?php global $apg_shipping; ?>

<h3><a href="<?php echo $apg_shipping['plugin_url']; ?>" title="Art Project Group"><?php echo $apg_shipping['plugin']; ?></a></h3>
<p>
  <?php _e('Lets you calculate shipping cost based on Postcode/State/Country and weight of the cart. Lets you set an unlimited weight bands on per postcode/state/country basis and group the groups that that share same delivery cost/bands.', 'apg_shipping'); ?>
</p>
<?php include('cuadro-donacion.php'); ?>
<div class="cabecera"> <a href="<?php echo $apg_shipping['plugin_url']; ?>" title="<?php echo $apg_shipping['plugin']; ?>" target="_blank"><span class="imagen"></span></a> </div>
<table class="form-table">
  <?php $this->generate_settings_html(); ?>
</table>
<!--/.form-table--> 
