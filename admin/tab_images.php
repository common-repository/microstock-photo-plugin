<?php
// show available offers
if ($offers && is_array($offers) && count($offers) > 0 && count($result) > 0)
{  
  foreach($offers as $offer)
    if (in_array($module, $offer->modules) && in_array('search_page', $offer->visibility) && !isset($hidden_offers[$offer->id]))
      echo '<div class="mpp_offer"><div class="mpp_offer_text">'.$offer->content.'</div><div class="mpp_offer_close" data-id="'.$offer->id.'">X</div><br class="mpp_clear" /></div>';
}
?>

<?php
foreach($result as $image)
{
?>
<div class="mpp_image">
  <a data-tooltip-mpp="mpp_image_tooltip_<?php echo $image['id']; ?>" class="mpp_image_link" href="<?php echo $image['id']; ?>" onclick="return false;">
    <div class="mpp_image_div">
      <table width="100%" height="130" cellpadding="0" cellspacing="0">
        <tr>
          <td align="center">
            <img class="mpp_image_img" max-height="130" src="<?php echo $image['thumbnail_url']; ?>" title="<?php echo esc_attr($image['title']); ?>" />
          </td>
        </tr>
      </table>
    </div>
    <div class="mpp_image_title"><?php echo $image['id']; ?></div>
  </a>
</div>
<?php
}
?>
<br class="mpp_clear" />

<div id="mpp_tooltips" class="stickytooltip">
  <div style="padding:5px">
  <?php
  foreach($result as $image)
  {
  ?>
    <div id="mpp_image_tooltip_<?php echo $image['id']; ?>" class="atip">
      <div style="overflow: hidden; width: <?php echo $image['image_width']; ?>px; height: <?php echo $image['image_height']-15; ?>px;">
        <img src="<?php echo $image['image_url']; ?>" width="<?php echo $image['image_width']; ?>" height="<?php echo $image['image_height']; ?>" alt="<?php echo esc_attr($image['title']); ?>" /><br />
      </div>
      <div style="float: left; width: <?php echo $tooltip['image_width'] - 20; ?>px">
        <?php echo $image['title']; ?>
      </div>
      <br class="mpp_clear" />
    </div>
  <?php
  }
  ?>
  </div>
</div>