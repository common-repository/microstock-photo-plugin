<div id="mpp_status" style="position: absolute; right: 50px; top: 15px;">
<?php
if ($data)
{
  echo sprintf(__('You are logged as %s.', self::ld),
                '<a href="'.admin_url('options-general.php?page=MicrostockPhotoPlugin').'"><b>'.(isset($data['display_name'])?$data['display_name']:$settings['mpp_login']).'</b></a>');

  if (isset($data['credits']))
    echo ' '.sprintf(__('You have %s credit(s).', self::ld),
                      '<a href="'.$data['credits_link'].'" target="_blank"><b>'.($data['credits']?$data['credits']:'0').'</b></a>');

  if (isset($data['subscription']) && $data['subscription'])
  {
    if (is_array($data['subscription']))
    {
      if (isset($data['subscription']['credits']))
        echo ' '.sprintf(__('Subscription balance %s credit(s).', self::ld), $data['subscription']['credits']);
      else
      if (isset($data['subscription']['type']))
      {
        if ($data['subscription']['type'] < 3)
          $type = '<a href="'.$data['subscribe_link'].'" target="_blank"><b>'.($data['subscription']['type'] == 1?__('daily', self::ld):__('monthly', self::ld)).'</b></a>';
        else
          $type = '';
        $downloads = '<a href="'.$data['subscribe_link'].'" target="_blank"><b>'.$data['subscription']['downloads'].'</b></a>';
        echo ' '.sprintf(__('You have active %s subscription with %s download(s) left.', self::ld), $type, $downloads);
      }
    }
    else
      echo ' '.__('You have active subscription.', self::ld);
  }
}
else
{
  echo sprintf(__('You are no longer logged into %s.', self::ld), $module->getTitle());
  echo ' ';
  echo '<a href="'.admin_url('options-general.php?page=MicrostockPhotoPlugin').'">'.__('Please log in now.', self::ld).'</a>';

  if ($l = $module->getRegisterLink())
    echo ' <a href="'.$l.'" target="_blank">'.__('Register now', self::ld).'</a>';
}

if ($t = $module->getStatusText())
  echo ' '.$t;
?>
</div>
