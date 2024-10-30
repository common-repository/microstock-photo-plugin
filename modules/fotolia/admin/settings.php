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
  <a href="https://us.fotolia.com/stockphotosecrets" target="_blank"><?php _e('Sign up at Fotolia today', self::ld); ?></a>
  <?php _e('and get 3 free credits plus 20% bonus credits on your next purchase.', self::ld); ?>
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
      <input class="<?php echo $loginClass; ?>" id="<?php $this->fieldID('mpp_password'); ?>" size="50" maxlength="255" type="password" name="mpp_password" value="<?php echo esc_attr((isset($settings['mpp_password'])?$settings['mpp_password']:'')); ?>" />
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

  <?php
  if (isset($data['subscription']) && is_array($data['subscription']) && $data['subscription']['type'] == 1)
    $show_custom_api = true;
  else
    $show_custom_api = false;
  ?>
  <tr class="mpp_custom_api_key_tr<?php echo $show_custom_api?'':' mpp_hidden'; ?>">
    <th scope="row"><label for="<?php $this->fieldID('mpp_custom_api_key'); ?>"><?php _e('API Key', self::ld); ?></label></th>
    <td>
      <?php
      $classes = '';
      if (isset($settings) && is_array($settings) && isset($settings['customAPI']))
      {
        if ($settings['customAPI'] == 1)
          $classes = 'mpp_login_incorrect';
        else
        if ($settings['customAPI'] == 2)
          $classes = 'mpp_login_correct';
      }
      ?>
      <input size="50" id="<?php $this->fieldID('mpp_custom_api_key'); ?>" maxlength="255" type="text" class="<?php echo $classes; ?>" name="mpp_custom_api_key" value="<?php echo esc_attr(isset($settings['mpp_custom_api_key'])?$settings['mpp_custom_api_key']:''); ?>" />
      <br /><i>
      <?php _e('Please enter your API key if you want to use daily subscription.', self::ld); ?>
      <?php echo sprintf(__("If you don't have one, please request an API key %shere%s.", self::ld),
                  '<a href="http://en.fotolia.com/Member/API/CreateKey?utm_source=7757&utm_medium=affiliation&utm_content=7757" target="_blank">', '</a>'); ?>
      </i>
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
    <th scope="row"><label for="<?php $this->fieldID('mpp_affiliate_id'); ?>"><?php _e('Alternative Affiliate ID:', self::ld); ?></label></th>
    <td>
      <input id="<?php $this->fieldID('mpp_affiliate_id'); ?>" title="<?php esc_attr_e('Leave blank if you want to use affiliate ID of logged user.', self::ld); ?>" size="50" maxlength="255" type="text" name="mpp_affiliate_id" value="<?php echo esc_attr((isset($settings['mpp_affiliate_id'])?$settings['mpp_affiliate_id']:'')); ?>" />
    </td>
  </tr>
</table>
