<?php
add_thickbox();
?>
<div id="mpp_license_dialog" style="display: none;">
  <div class="mpp_license_text">
  </div>
  <div class="mpp_license_buttons">
    <input type="button" name="mpp_license_accept" class="button-primary mpp_license_button" value="<?php _e('Accept', self::ld); ?>" />
    <input type="button" name="mpp_license_reject" class="button-secondary mpp_license_button" value="<?php _e('Reject', self::ld); ?>" />
  </div>
</div>

<div class="mpp_ui_search">
  <div>
    <div class="mpp_search_form">
      <input type="hidden" name="mpp_search_input_copy" value="" />
      <input type="text" placeholder="<?php esc_attr_e('Search for Photos, Vectors and Illustrations at', self::ld); ?> <?php echo $module->getTitle(); ?>" class="mpp_search_input" name="mpp_search_input" value="" />
      <input type="button" name="mpp_search_button" class="button-primary mpp_search_button" value="<?php _e('Search', self::ld); ?>" />
      <input type="checkbox" id="mpp_search_sync" name="mpp_search_sync" checked class="mpp_search_sync" value="1" />
      <label for="mpp_search_sync" class="mpp_search_sync_label"><?php _e('Sync between agencies', self::ld); ?></label>

      <div>
        <?php
        $sorts = $module->getSortOptions();
        if ($sorts)
        {
        ?>
        <div class="mpp_sort_block">
          <label for="mpp_sort"><?php _e('Sort by', self::ld); ?></label>
          <select name="mpp_sort" id="mpp_sort">
            <?php
            foreach($sorts as $id=>$sort)
              echo '<option value="'.$id.'">'.$sort.'</option>';
            ?>
          </select>
        </div>
        <?php
        }
        ?>

        <?php
        $filters = $module->getSearchFilters();
        if ($filters)
        {
        ?>
        <div class="mpp_search_filter">
          <?php
          $c = 0;
          foreach($filters as $id=>$filter)
          {          
          ?>
            <label for="mpp_search_filter_<?php echo $id; ?>"><input class="mpp_search_filter_checkbox" value="<?php echo $id; ?>" type="checkbox"<?php echo $c==0?' checked':''; ?> name="mpp_search_filter_<?php echo $id; ?>" id="mpp_search_filter_<?php echo $id; ?>" /> <?php echo $filter; ?></label>
          <?php
            $c++;
          }
          ?>
        </div>
        <?php
        }
        ?>
        <br class="mpp_clear" />
      </div>

      <div class="mpp_nb_images"></div>
    </div>

    <div class="mpp_paging">
    </div>
    <br class="mpp_clear" />
  </div>  

  <div class="mpp_images"></div>
<!--
  <h3 class="mpp_categories_title"><?php _e('Browse Categories', self::ld); ?></h3>
  <div class="mpp_categories">
    <ul class="mpp_categories_main">
      <li><a href="">Category name</a></li>
    </ul>

    <br class="mpp_clear" />
  </div>

-->

  <div class="mpp_paging_bottom">
    <div class="mpp_paging">
    </div>
    <br class="mpp_clear" />
  </div>
</div>

<div class="mpp_ui_detail">
  <input type="button" class="button-secondary" name="mpp_button_back" value="<?php echo _e('Back', self::ld); ?>" />
  <div class="mpp_detail"></div>
</div>

<div class="mpp_ui_image">
</div>