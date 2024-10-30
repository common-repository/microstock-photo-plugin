<?php
// handle login state
$loginClass = '';
if (isset($settings['checkLogin']))
  if ($settings['checkLogin'])
    $loginClass = ' mpp_login_correct';
  else
    $loginClass = ' mpp_login_incorrect';
?>

<div class="mpp_offer mpp_offer_signup<?php echo ($data?' mpp_hidden':''); ?>"><div class="mpp_offer_text">
  <a href="http://www.linkconnector.com/traffic_affiliate.php?lc=046369051341004382&amp;atid=microstock-photo-plugin-register&amp;lcpf=0" target="_blank"><?php _e('Sign up at iStock today', self::ld); ?></a>
  <?php _e("and use coupon code '5UPERAM05' for 20% discount on any purchase of 50 credits and higher.", self::ld); ?>
</div><br class="mpp_clear" /></div>

<table class="form-table">
  <tr>
    <td colspan="2">
      <input type="checkbox" id="<?php $this->fieldID('mpp_enabled'); ?>" name="mpp_enabled" value="1"<?php echo (isset($settings['mpp_enabled']) && $settings['mpp_enabled']?' checked':''); ?> />
      <label for="<?php $this->fieldID('mpp_enabled'); ?>"><?php _e('Enabled', self::ld); ?></label>
    </td>
  </tr>
  <tr>
    <th scope="row"><label for="<?php $this->fieldID('mpp_login'); ?>"><?php _e('Login', self::ld); ?></label></th>
    <td>
      <input class="<?php echo $loginClass; ?>" id="<?php $this->fieldID('mpp_login'); ?>" size="50" maxlength="255" type="text" name="mpp_login" value="<?php echo esc_attr((isset($settings['mpp_login'])?$settings['mpp_login']:'')); ?>" />
    </td>
  </tr>
  <tr>
    <th scope="row"><label for="<?php $this->fieldID('mpp_password'); ?>"><?php _e('Password', self::ld); ?></label></th>
    <td>
      <input class="<?php echo $loginClass; ?>" id="<?php $this->fieldID('mpp_password'); ?>" size="50" maxlength="255" type="password" name="mpp_password" value="<?php echo esc_attr(isset($settings['mpp_password'])?$settings['mpp_password']:''); ?>" />
    </td>
  </tr>

  <tr class="mpp_offer_signup<?php echo ($data?' mpp_hidden':''); ?>">
    <th scope="row"></th>
    <td><a href="<?php echo $this->getRegisterLink(); ?>" target="_blank"><?php _e("Don't have an account? Register now!", self::ld); ?></a></td>
  </tr>

  <tr>
    <th scope="row"></th>
    <td class="mpp_login_info<?php echo ($data?'':' mpp_hidden'); ?>">
      <div class="mpp_login_info_text">
      <?php
      if ($data)
        require_once $this->_path.'/admin/login_data.php';
      ?>
      </div>
      <input type="button" class="button-secondary mpp_logout_button" name="mpp_logout_button" value="<?php _e('Logout', self::ld); ?>" />
      <br class="mpp_clear" />
    </td>
  </tr>

  <tr>
    <th scope="row"><label for="<?php $this->fieldID('mpp_affiliate_text'); ?>"><?php _e('Affiliate', self::ld); ?></label></th>
    <td>
      <label for="<?php $this->fieldID('mpp_affiliate'); ?>" title="<?php esc_attr_e('Turn on affiliate links on the image and/or caption', self::ld); ?>">
        <input type="checkbox" name="mpp_affiliate" id="<?php $this->fieldID('mpp_affiliate'); ?>" value="1"<?php echo (isset($settings['mpp_affiliate']) && $settings['mpp_affiliate']?' checked':''); ?> />
        <?php esc_html_e('Earn revenue share from sales', self::ld); ?>
      </label>
    </td>
  </tr>
  <tr class="mpp_affiliate_input<?php echo (isset($settings['mpp_affiliate']) && $settings['mpp_affiliate']?'':' mpp_hidden'); ?>">
    <th scope="row"><label for="<?php $this->fieldID('mpp_affiliate_id'); ?>"><?php _e('Affiliate Tracking URL:', self::ld); ?></label></th>
    <td>
      <input id="<?php $this->fieldID('mpp_affiliate_id'); ?>" size="50" maxlength="255" type="text" name="mpp_affiliate_id" value="<?php echo esc_attr(isset($settings['mpp_affiliate_id'])?$settings['mpp_affiliate_id']:''); ?>" />
    </td>
  </tr>
</table>
