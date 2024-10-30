<?php
if (!defined('ABSPATH')) die();

if (!isset($_GET['page']) || (isset($_GET['page']) && $_GET['page'] != 'MicrostockPhotoPlugin'))
{
?>
<div class="updated microstockphotoplugin_config_notice" style="padding: 10px;">
  <div style="float: left; padding-top: 5px;"><?php echo __('Please configure the Microstock Photo Plugin on', self::ld).' <a href="'.admin_url('options-general.php?page=MicrostockPhotoPlugin&dismiss=1').'">'.__('the options page', self::ld).'</a>.'; ?></div>
  <a class="button microstockphotoplugin_dismiss_config_notice" style="margin-left: 10px; margin-right: 5px; float: right;" href="#" onclick="return false;" title="<?php _e('Hide', self::ld); ?>"><?php _e('Hide', self::ld); ?></a>
  <script>
  jQuery(document).ready(function($)
  {
    $('.microstockphotoplugin_dismiss_config_notice').bind('click', function()
    {
      $.post('<?php echo admin_url('admin-ajax.php?action=MicrostockPhotoPlugin'); ?>', {'a': 'dismiss_config_notice'}, function(r)
      {
        $('.microstockphotoplugin_config_notice').fadeOut(400);
      });
    });
  });
  </script>
  <br class="clear" />
</div>
<?php
}
?>