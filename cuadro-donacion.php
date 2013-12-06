<?php $plugin = apg_shipping_plugin($apg_shipping['plugin_uri']); ?>
<div class="donacion">
  <p>
    <?php _e('If you enjoyed and find helpful this plugin, please make a donation:', 'apg_shipping'); ?>
  </p>
  <p><a href="<?php echo $apg_shipping['paypal']; ?>" target="_blank" title="<?php _e('Make a donation by ', 'apg_shipping'); ?>PayPal"><span class="icon-paypal"></span></a></p>
  <div>
    <p>Art Project Group:</p>
    <p><a href="http://www.artprojectgroup.es" title="Art Project Group" target="_blank"><strong class="artprojectgroup">APG</strong></a></p>
  </div>
  <div>
    <p>
      <?php _e('Follow us:', 'apg_shipping'); ?>
    </p>
    <p><a href="https://www.facebook.com/artprojectgroup" title="<?php _e('Follow us on ', 'apg_shipping'); ?>Facebook" target="_blank"><span class="icon-facebook6"></span></a> <a href="https://twitter.com/artprojectgroup" title="<?php _e('Follow us on ', 'apg_shipping'); ?>Twitter" target="_blank"><span class="icon-social19"></span></a> <a href="https://plus.google.com/+ArtProjectGroupES" title="<?php _e('Follow us on ', 'apg_shipping'); ?>Google+" target="_blank"><span class="icon-google16"></span></a> <a href="http://es.linkedin.com/in/artprojectgroup" title="<?php _e('Follow us on ', 'apg_shipping'); ?>LinkedIn" target="_blank"><span class="icon-logo"></span></a></p>
  </div>
  <div>
    <p>
      <?php _e('More plugins:', 'apg_shipping'); ?>
    </p>
    <p><a href="http://profiles.wordpress.org/artprojectgroup/" title="<?php _e('More plugins on ', 'apg_shipping'); ?>WordPress" target="_blank"><span class="icon-wordpress2"></span></a></p>
  </div>
  <div>
    <p>
      <?php _e('Contact with us:', 'apg_shipping'); ?>
    </p>
    <p><a href="mailto:info@artprojectgroup.es" title="<?php _e('Contact with us by ', 'apg_shipping'); ?>e-mail"><span class="icon-open21"></span></a> <a href="skype:artprojectgroup" title="<?php _e('Contact with us by ', 'apg_shipping'); ?>Skype"><span class="icon-social6"></span></a></p>
  </div>
  <div>
    <p>
      <?php _e('Documentation and Support:', 'apg_shipping'); ?>
    </p>
    <p><a href="<?php echo $apg_shipping['plugin_url']; ?>" title="<?php echo $apg_shipping['plugin']; ?>"><span class="icon-work"></span></a></p>
  </div>
  <div>
    <p> <?php echo sprintf(__('Please, rate %s:', 'apg_shipping'), $apg_shipping['plugin']); ?> </p>
    <div class="star-holder rate">
      <div style="width: <?php echo esc_attr(str_replace(',', '.', $plugin['rating'])); ?>px;" class="star-rating"></div>
      <div class="star-rate"> <a title="<?php _e('***** Fantastic!', 'apg_shipping'); ?>" href="<?php echo $apg_shipping['puntuacion']; ?>?rate=5#postform"><span></span></a> <a title="<?php _e('**** Great', 'apg_shipping'); ?>" href="<?php echo $apg_shipping['puntuacion']; ?>?rate=4#postform"><span></span></a> <a title="<?php _e('*** Good', 'apg_shipping'); ?>" href="<?php echo $apg_shipping['puntuacion']; ?>?rate=3#postform"><span></span></a> <a title="<?php _e('** Works', 'apg_shipping'); ?>" href="<?php echo $apg_shipping['puntuacion']; ?>?rate=2#postform"><span></span></a> <a title="<?php _e('* Poor', 'apg_shipping'); ?>" href="<?php echo $apg_shipping['puntuacion']; ?>?rate=1#postform"><span></span></a> </div>
    </div>
  </div>
</div>