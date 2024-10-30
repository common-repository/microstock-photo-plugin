  <div id="poststuff" class="metabox-holder has-right-sidebar">
    <div id="side-info-column" class="inner-sidebar meta-box-sortables">
      <div id="mpp_box_what_you_need" class="postbox<?php echo (isset($boxes['mpp_box_what_you_need']) && $boxes['mpp_box_what_you_need']?' mpp_closed closed':''); ?>">
        <div class="handlediv" title="<?php _e('Click to toggle', self::ld); ?>"><br></div>
        <h3 class="hndle"><span><?php _e('What you need!', self::ld); ?></span></h3>
        <div class="inside">
          <p><?php _e('To use the Microstock Photo Plugin you need to have accounts at the following stock agencies.', self::ld); ?></p>
          <p>
            <a href="http://depositphotos.com/stockphotosecrets.html"><img src="<?php echo $this->_url; ?>/admin/images/depositphotos-favicon.png" alt="Depositphotos" width="16" height="16" align="absmiddle" /></a> <a href="http://depositphotos.com/stockphotosecrets.html" target="_blank"><?php _e('Sign up at Depositphotos today', self::ld); ?></a> <?php _e('and get 5 free credits plus 15% discount on your first purchase.', self::ld); ?><br style="line-height: 2;" />
            <a href="https://us.fotolia.com/stockphotosecrets"><img src="<?php echo $this->_url; ?>/admin/images/fotolia-favicon.png" alt="Fotolia" width="16" height="16" align="absmiddle" /></a> <a href="https://us.fotolia.com/stockphotosecrets" target="_blank"><?php _e('Sign up at Fotolia today', self::ld); ?></a> <?php _e('and get 3 free credits plus 20% bonus credits on your next purchase.', self::ld); ?><br style="line-height: 2;" />
            <a href="http://www.linkconnector.com/traffic_affiliate.php?lc=046369051341004382&amp;atid=microstock-photo-plugin-register&amp;lcpf=0"><img src="<?php echo $this->_url; ?>/admin/images/istock-favicon.png" alt="iStock" width="16" height="16" align="absmiddle" /></a> <a href="http://www.linkconnector.com/traffic_affiliate.php?lc=046369051341004382&amp;atid=microstock-photo-plugin-register&amp;lcpf=0" target="_blank"><?php _e('Sign up at iStock today', self::ld); ?></a> <?php _e("and use coupon code '5UPERAM05' for 20% discount on any purchase of 50 credits and higher.", self::ld); ?>
          </p>
        </div>
      </div>

      <div id="mpp_box_help" class="postbox<?php echo (isset($boxes['mpp_box_help']) && $boxes['mpp_box_help']?' mpp_closed closed':''); ?>">
        <div class="handlediv" title="<?php _e('Click to toggle', self::ld); ?>"><br></div>
        <h3 class="hndle"><span><?php _e('Help', self::ld); ?></span></h3>
        <div class="inside">
          <p><?php _e('Do you need help, please check following resources:', self::ld); ?><br />
          </p>
          <ul>
            <li><a href="http://www.microstockplugin.com/installation/"><?php _e('Check the detailed installation instruction', self::ld); ?></a></li>
            <li><a href="http://www.microstockplugin.com/features/"><?php _e('See how the plugin works', self::ld); ?></a></li>
            <li><a href="http://www.microstockplugin.com/faq/"><?php _e('Find a solution in the FAQs', self::ld); ?></a></li>
            <li><a href="http://www.microstockplugin.com/contact/"><?php _e('Finally contact the support team', self::ld); ?></a></li>
          </ul>
        </div>
      </div>

    </div>

    <div id="post-body">
      <div id="post-body-content" class="meta-box-sortables">

        <?php
        // show settings boxes for each module
        foreach($this->modules as $module)
        {
          $className = $module->getName();
        ?>
        <div id="mpp_box_<?php echo $className; ?>" class="mpp_postbox postbox<?php echo (isset($boxes['mpp_box_'.$className]) && $boxes['mpp_box_'.$className]?' mpp_closed closed':''); ?>">
          <div class="handlediv" title="<?php _e('Click to toggle', self::ld); ?>"><br></div>
          <h3 class="hndle">
            <img src="<?php echo $module->getIcon(); ?>" width="16" height="16" />
            <span><?php echo $module->getTitle(); ?></span>
            <?php if ($module->isNew()) { ?>
            <span style="font-size: 9px;color: #ff0000;top: -4px;position: relative;font-weight: bold;margin-left: 0px;"><?php _e('NEW', self::ld); ?></span>
            <?php } ?>
          </h3>
          <div class="inside">
            <?php
            // show available offers
            if ($offers && is_array($offers) && count($offers) > 0)
            {
              foreach($offers as $offer)
              {
                if (in_array($module->getName(), $offer->modules) && in_array('settings_page', $offer->visibility) && !isset($hidden_offers[$offer->id]))
                  echo '<div class="mpp_offer"><div class="mpp_offer_text">'.$offer->content.'</div><div class="mpp_offer_close" data-id="'.$offer->id.'">X</div><br class="mpp_clear" /></div>';
              }
            }
            ?>
            <form id="mpp_form_<?php echo $className; ?>">
            <?php
              $module->settings();
            ?>
            </form>
            <br />
            <div class="mpp_save_area">
              <span class="mpp_settings_saved"><?php _e('Settings saved.', self::ld); ?></span>
              <span class="mpp_save_loader"><img id="mpp_save_loader_<?php echo $className; ?>" src="<?php echo $this->_url; ?>/admin/images/loader.gif" width="15" height="15" /></span>
              <input class="button-primary mpp_button_save" disabled id="mpp_save_<?php echo $className; ?>" type="submit" name="save" title="<?php _e('Save', self::ld); ?>" value="<?php _e('Save', self::ld); ?>" />
            </div>

            <?php if (!class_exists('ZipArchive')) { ?>
            <br class="mpp_clear" />
            <p>
              <b><?php _e('Backup feature is not available, please install/enable ZIP extension on server.', self::ld); ?></b>
            </p>
            <?php } else { ?>
            <a href="#" onclick="return false;" class="mpp_show_backup_options"><b><?php _e('Show backup options', self::ld); ?></b></a>
            <a href="#" onclick="return false;" class="mpp_hide_backup_options" style="display: none;"><b><?php _e('Hide backup options', self::ld); ?></b></a>
            <br class="mpp_clear" />
            <div class="mpp_backup_options" style="display: none;">
              <b><?php _e('Available backups', self::ld); ?></b><br />
              <div class="mpp_backup_list">
                <?php require 'options_backup_list.php'; ?>
              </div>
              <input class="button-primary" type="button" data-module="<?php echo $module->getName(); ?>" name="mpp_download_backup" value="<?php _e('Download a new backup', self::ld); ?>" />
              <span class="mpp_download_backup_loader"><img src="<?php echo $this->_url; ?>/admin/images/loader.gif" width="15" height="15" /></span>
            </div>
            <?php } ?>
          </div>
        </div>
        <?php
        }
        ?>

        <div id="mpp_box_settings" class="postbox<?php echo (isset($boxes['mpp_box_settings']) && $boxes['mpp_box_settings']?' mpp_closed closed':''); ?>">
          <div class="handlediv" title="<?php _e('Click to toggle', self::ld); ?>"><br></div>
          <h3 class="hndle"><span><?php _e('Settings', self::ld); ?></span></h3>
          <div class="inside">
            <form id="mpp_form_settings">
              <table class="form-table">
                <tr>
                  <th scope="row"><label for="mpp_default_language"><?php _e('Default language', self::ld); ?></label></th>
                  <td>
                    <select name="mpp_default_language" id="mpp_default_language" title="<?php esc_attr_e('Default language used for search result.', self::ld); ?>">
                      <?php
                      $current_lang = isset($settings['mpp_default_language'])?$settings['mpp_default_language']:$this->default_settings['mpp_default_language'];
                      foreach($this->languages as $language_id => $language)
                      {
                        echo '<option value="'.$language_id.'"'.($current_lang == $language_id?' selected':'').'>'.$language.'</option>';
                      }
                      ?>
                    </select>
                  </td>
                </tr>

                <tr>
                  <th scope="row"><label for="mpp_test_mode"><?php _e('Test mode', self::ld); ?></label></th>
                  <td>
                    <?php
                    $testmode = isset($settings['mpp_test_mode'])?$settings['mpp_test_mode']:$this->default_settings['mpp_test_mode'];
                    ?>
                    <select name="mpp_test_mode" id="mpp_test_mode" title="<?php esc_attr_e("If you want to test the plugin and you don't want to do real payments.", self::ld); ?>">
                      <option value="0"<?php echo ($testmode==0?' selected':''); ?>><?php _e('Disabled', self::ld); ?></option>
                      <option value="1"<?php echo ($testmode==1?' selected':''); ?>><?php _e('Enabled', self::ld); ?></option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <th scope="row"><label for="mpp_sync_offers"><?php _e('Show special offers', self::ld); ?></label></th>
                  <td>
                    <?php
                    $show_special_offers = isset($settings['mpp_sync_offers'])?$settings['mpp_sync_offers']:$this->default_settings['mpp_sync_offers'];
                    ?>
                    <select name="mpp_sync_offers" id="mpp_sync_offers" title="<?php esc_attr_e("Turn on if you want to receive special offers from us to save money. These offers will shown on the search and the settings page.", self::ld); ?>">
                      <option value="0"<?php echo ($show_special_offers==0?' selected':''); ?>><?php _e('Disabled', self::ld); ?></option>
                      <option value="1"<?php echo ($show_special_offers==1?' selected':''); ?>><?php _e('Enabled', self::ld); ?></option>
                    </select>
                  </td>
                </tr>

                <tr>
                  <th scope="row"><label for="mpp_image_caption"><?php _e('Image caption', self::ld); ?></label></th>
                  <td>
                    <?php $image_caption = isset($settings['mpp_image_caption'])?$settings['mpp_image_caption']:$this->default_settings['mpp_image_caption']; ?>
                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_image_caption" value="0" id="mpp_image_caption_0"<?php echo ($image_caption==0?' checked':''); ?> />
                      <label for="mpp_image_caption_0"><?php esc_html_e('Image title', self::ld); ?></label>
                    </div>

                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_image_caption" value="1" id="mpp_image_caption_1"<?php echo ($image_caption==1?' checked':''); ?> />
                      <label for="mpp_image_caption_1"><?php esc_html_e('Custom', self::ld); ?></label><br />
                      <input type="text" class="mpp_image_caption_label" size="50" name="mpp_image_caption_custom" value="<?php echo isset($settings['mpp_image_caption_custom'])?self::strip($settings['mpp_image_caption_custom']):$this->default_settings['mpp_image_caption_custom']; ?>" />
                    </div>

                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_image_caption" value="2" id="mpp_image_caption_2"<?php echo ($image_caption==2?' checked':''); ?> />
                      <label for="mpp_image_caption_2"><?php esc_html_e('Custom with copyright', self::ld); ?></label><br />
                      <input type="text" class="mpp_image_caption_label" size="50" name="mpp_image_caption_custom_copyright" value="<?php echo isset($settings['mpp_image_caption_custom_copyright'])?self::strip($settings['mpp_image_caption_custom_copyright']):$this->default_settings['mpp_image_caption_custom_copyright']; ?>" />
                    </div>

                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_image_caption" value="3" id="mpp_image_caption_3"<?php echo ($image_caption==3?' checked':''); ?> />
                      <label for="mpp_image_caption_3"><?php esc_html_e('Copyright notice (automatically generated)', self::ld); ?></label>
                    </div>

                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_image_caption" value="4" id="mpp_image_caption_4"<?php echo ($image_caption==4?' checked':''); ?> />
                      <label for="mpp_image_caption_4"><?php esc_html_e('None', self::ld); ?></label>
                    </div>
                  </td>
                </tr>

                <tr>
                  <th scope="row"><label for="mpp_alt_text"><?php _e('Image Alternative Text', self::ld); ?></label></th>
                  <td>
                    <?php $alt_text = isset($settings['mpp_alt_text'])?$settings['mpp_alt_text']:$this->default_settings['mpp_alt_text']; ?>
                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_alt_text" value="0" id="mpp_alt_text_0"<?php echo ($alt_text==0?' checked':''); ?> />
                      <label for="mpp_alt_text_0"><?php esc_html_e('Keyword', self::ld); ?></label>
                    </div>
                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_alt_text" value="1" id="mpp_alt_text_1"<?php echo ($alt_text==1?' checked':''); ?> />
                      <label for="mpp_alt_text_1"><?php esc_html_e('Image Title', self::ld); ?></label>
                    </div>
                    <div class="mpp_image_caption_group">
                      <input type="radio" name="mpp_alt_text" value="2" id="mpp_alt_text_2"<?php echo ($alt_text==2?' checked':''); ?> />
                      <label for="mpp_alt_text_2"><?php esc_html_e('None', self::ld); ?></label>
                    </div>
                  </td>
                </tr>

                <tr>
                  <th scope="row"><label for="mpp_show_add_button"><?php _e('Stock Photo Button', self::ld); ?></label></th>
                  <td>
                    <?php
                    $stock_photo_button = isset($settings['mpp_show_add_button'])?$settings['mpp_show_add_button']:$this->default_settings['mpp_show_add_button'];
                    ?>
                    <select name="mpp_show_add_button" id="mpp_show_add_button" title="<?php esc_attr_e("Show 'Add Stock Photo' next to 'Add Media' button?", self::ld); ?>">
                      <option value="0"<?php echo ($stock_photo_button==0?' selected':''); ?>><?php _e('No', self::ld); ?></option>
                      <option value="1"<?php echo ($stock_photo_button==1?' selected':''); ?>><?php _e('Yes', self::ld); ?></option>
                    </select>
                  </td>
                </tr>

              </table>
            </form>
            <div class="mpp_save_area">
              <span class="mpp_settings_saved"><?php _e('Settings saved.', self::ld); ?></span>
              <span class="mpp_save_loader"><img id="mpp_save_loader_settings" src="<?php echo $this->_url; ?>/admin/images/loader.gif" width="15" height="15" /></span>
              <input class="button-primary mpp_button_save" disabled id="mpp_save_settings" type="submit" name="save" title="<?php _e('Save', self::ld); ?>" value="<?php _e('Save', self::ld); ?>" />
            </div>
            <br class="clear" />
          </div>
        </div>
      </div>
    </div>
    <br class="clear">
  </div>
</div>
