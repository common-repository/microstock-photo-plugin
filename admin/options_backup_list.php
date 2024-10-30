<?php
$upload_dir = wp_upload_dir();

global $wpdb;
$backups = $wpdb->get_results("
  SELECT *
  FROM ".$wpdb->prefix.self::tb_backups."
  WHERE module = '".$wpdb->escape($module->getName())."'
  ORDER BY creation_date DESC
", ARRAY_A);
foreach($backups as $backup)
{                  
?>
<div class="mpp_backup_list_item">
  <div class="mpp_download_text"><?php echo $backup['filename']; ?> - <?php echo date('Y/m/d H:i:s', $backup['creation_date']); ?></div>
  <a href="<?php echo $upload_dir['baseurl']; ?>/<?php echo $backup['filename']; ?>" class="button-secondary mpp_download_backup"><?php _e('Download', self::ld); ?></a>
  <br class="mpp_clear" />
</div>
<?php
}
?>