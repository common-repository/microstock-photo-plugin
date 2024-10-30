<div class="mpp_offer mpp_offer_detail" style="margin-bottom: 15px; display: none"><div class="mpp_offer_text">
</div><br class="mpp_clear" /></div>

<div class="mpp_detail_header">
  <b>#<?php echo $image['id']; ?> - <?php echo $image['title']; ?></b><br />
</div>
<div>
  <div class="mpp_detail_image" style="width: <?php echo $image['thumbnail_width']; ?>px">
    <img src="<?php echo $image['thumbnail_url']; ?>" width="<?php echo $image['thumbnail_width']; ?>" /><br />
    &copy; <?php echo $image['creator_name']; ?>
  </div>
  <div class="mpp_detail_licenses">
    <?php
    if (isset($image['licenses_subscription']) && $image['licenses_subscription'])
    {
    ?>
    <div class="mpp_buy_method">
      <label for="mpp_buy_credits">
        <input type="radio" id="mpp_buy_credits" checked name="mpp_buy_method" value="1" /> <?php _e('Credits', self::ld); ?>
      </label>
      <label for="mpp_buy_subscription">
        <input type="radio" id="mpp_buy_subscription" name="mpp_buy_method" value="2" /> <?php _e('Subscription', self::ld); ?>
      </label>
    </div>
    <?php
    }
    ?>
    <table>
      <thead>
        <tr>
          <th></th>
          <th align="left"><?php _e('Size', self::ld); ?></th>
          <th align="left"><?php _e('Pixels', self::ld); ?></th>
          <th><?php _e('Price', self::ld); ?></th>
        </tr>
      </thead>
      <tbody>
      <?php
      foreach($image['licenses'] as $id=>$license)
      {
      ?>
        <tr class="mpp_license_row mpp_license_credits">
          <td align="center"><input type="radio" id="mpp_license_<?php echo $id; ?>" name="mpp_license" value="<?php echo $license['name']; ?>" /></td>
          <td><?php echo $license['title']; ?></td>
          <td><?php echo $license['dimensions']; ?></td>
          <td align="center"><?php echo $license['price']; ?> <?php $license['price'] == 1?_e('credit', self::ld):_e('credits', self::ld); ?>
        </tr>
      <?php
      }
      $last_id = $id;
      ?>

      <?php
      if (isset($image['licenses_subscription']) && $image['licenses_subscription'])
      {
        foreach($image['licenses_subscription'] as $id=>$license)
        {
      ?>
        <tr class="mpp_license_row mpp_license_subscription">
          <td align="center"><input type="radio" id="mpp_license_<?php echo $last_id.'_'.$id; ?>" name="mpp_license" value="<?php echo $license['name']; ?>" /></td>
          <td><?php echo $license['title']; ?></td>
          <td><?php echo $license['dimensions']; ?></td>
          <td align="center"><?php echo $license['price']; ?> <?php $license['price'] == 1?_e('download', self::ld):_e('download', self::ld); ?>
        </tr>
      <?php
        }
      }
      ?>
      </tbody>
    </table>
    <p align="center">
      <input type="hidden" name="mpp_license_type" value="<?php echo isset($image['license_type'])?esc_attr($image['license_type']):''; ?>" />
      <input type="hidden" name="mpp_buy_id" value="<?php echo esc_attr($image['id']); ?>" />
      <input type="hidden" name="mpp_buy_title" value="<?php echo esc_attr($image['title']); ?>" />
      <input type="hidden" name="mpp_buy_author" value="<?php echo esc_attr($image['creator_name']); ?>" />
      <input type="hidden" name="mpp_image_page" value="<?php echo esc_attr($image['image_page']); ?>" />
      <input type="button" class="button-secondary mpp_orange_button" name="mpp_buy" disabled value="<?php _e('Please select license', self::ld); ?>" />
      <a href="" class="button button-secondary mpp_register" style="display: none;" target="_blank"><?php _e('Register now', self::ld); ?></a>
    </p>
    <p class="mpp_detail_error_message">
    </p>
  </div>
  <br class="mpp_clear" />
</div>