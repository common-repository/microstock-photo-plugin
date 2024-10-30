<?php        
$display_name = '<b class="mpp_display_name">'.(isset($data['display_name']) && $data['display_name']?$data['display_name']:'').'</b>';
$credits = '<b class="mpp_credits">'.(isset($data['credits'])?$data['credits']:'0').'</b>';
echo sprintf(__('You are logged as %s. Credits: %s.', self::ld), $display_name, $credits);

echo '<span class="mpp_subscription'.(isset($data['subscription']) && is_array($data['subscription'])?'':' mpp_hidden').'">';
if (isset($data['subscription']) && is_array($data['subscription']))
{
  echo ' ';
  $type = '<b class="mpp_subscription_type">'.($data['subscription']['type'] == 1?__('Daily', self::ld):__('Monthly', self::ld)).'</b>';
  $downloads = '<b class="mpp_subscription_downloads">'.(isset($data['subscription']['downloads'])?$data['subscription']['downloads']:'0').'</b>';
  echo sprintf(__('Subscription type: %s. Downloads left: %s.', self::ld), $type, $downloads);  
}
echo '</span>';
if (isset($data['credits_link']))
{
  echo ' '.__('Buy more', self::ld).' <a href="'.$data['credits_link'].'" target="_blank">'.__('credits', self::ld).'</a>';
  echo ' '.__('or', self::ld).' <a href="'.$data['subscribe_link'].'" target="_blank">'.__('subscribe', self::ld).'</a>.';
}