<div class="">
  <?php
  if ($page > 1)
    echo '<a class="mpp_page" href="'.($page-1).'" onclick="return false;">'.__('&laquo; Prev', self::ld).'</a> ';

  $ad = 2;
	if ($max_page < 7 + ($ad * 2))
  {
		for($i=1;$i<=$max_page;$i++)
			if ($i == $page)
        echo '<span class="mpp_page_selected">'.$i.'</span> ';
			else
        echo '<a class="mpp_page" href="'.$i.'" onclick="return false;">'.$i.'</a> ';				
	}
	else
  if ($max_page > 5 + ($ad * 2))
  {
    if ($page < 1 + ($ad * 2))
    {
      for ($i=1;$i<4+($ad * 2); $i++)
			  if ($i == $page)
          echo '<span class="mpp_page_selected">'.$i.'</span> ';
		   	else
          echo '<a class="mpp_page" href="'.$i.'" onclick="return false;">'.$i.'</a> ';

			echo '<span class="mpp_page_delimiter">...</span>';				
      echo '<a class="mpp_page" href="'.($max_page-1).'" onclick="return false;">'.($max_page-1).'</a> ';				
      echo '<a class="mpp_page" href="'.($max_page).'" onclick="return false;">'.($max_page).'</a> ';				
		}
		else
    if (($max_page - ($ad * 2) > $page) && ($page > ($ad * 2)))
    {
      echo '<a class="mpp_page" href="1" onclick="return false;">1</a> ';				
      echo '<a class="mpp_page" href="2" onclick="return false;">2</a> ';				
			echo '<span class="mpp_page_delimiter">...</span>';				
			for($i = $page - $ad; $i <= $page + $ad; $i++)
			  if ($i == $page)
          echo '<span class="mpp_page_selected">'.$i.'</span> ';
		   	else
          echo '<a class="mpp_page" href="'.$i.'" onclick="return false;">'.$i.'</a> ';				
			echo '<span class="mpp_page_delimiter">...</span>';				
      echo '<a class="mpp_page" href="'.($max_page-1).'" onclick="return false;">'.($max_page-1).'</a> ';				
      echo '<a class="mpp_page" href="'.($max_page).'" onclick="return false;">'.($max_page).'</a> ';																	
		}
		else
    {
      echo '<a class="mpp_page" href="1" onclick="return false;">1</a> ';				
      echo '<a class="mpp_page" href="2" onclick="return false;">2</a> ';				
			echo '<span class="mpp_page_delimiter">...</span>';				
      for($i = $max_page - (2 + ($ad * 2)); $i <= $max_page; $i++)
			  if ($i == $page)
          echo '<span class="mpp_page_selected">'.$i.'</span> ';
		   	else
          echo '<a class="mpp_page" href="'.$i.'" onclick="return false;">'.$i.'</a> ';				
		}
	}
	
  if ($page < $max_page)
    echo '<a class="mpp_page" href="'.($page+1).'" onclick="return false;">'.__('Next &raquo;', self::ld).'</a> ';
  ?>
</div>