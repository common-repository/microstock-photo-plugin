<?php      
$display_name = '<b class="mpp_display_name">'.(isset($data['display_name']) && $data['display_name']?$data['display_name']:'').'</b>';
$credits = '<b class="mpp_credits">'.(isset($data['credits'])?$data['credits']:'0').'</b>';
echo sprintf(__('You are logged as %s. You have %s credit(s).', self::ld), $display_name, $credits);      
echo ' <span class="mpp_subscription'.(isset($data['subscription']) && is_array($data['subscription'])?'':' mpp_hidden').'">'.sprintf(__('Subscription balance %s credit(s).', self::ld), '<b class="mpp_subscription_balance">'.$data['subscription']['credits'].'</b>').'</span>';

if (isset($data['credits_link']))
  echo ' <a href="'.$data['credits_link'].'" target="_blank">'.__('Buy more credits', self::ld).'</a>';
?>
