<?php
// Depositphotos module for Microstock Photo Plugin

require_once 'api/DepositClient.php';

class MicrostockPhotoPlugin_depositphotos extends MicrostockPhotoPlugin_module
{
  const API_key = 'ff22c47fc3e3206456c5c06eb887b109ba097149';

  protected $api;

  protected $licenses;

  public function __construct()
  {
    parent::__construct();
    $this->_order = 10;
    $this->_path = dirname(__FILE__);
    $this->_url = plugins_url('', __FILE__);

    $this->icon = $this->_url.'/admin/images/depositphotos.png';
    $this->logo = $this->_url.'/admin/images/depositphotos-logo-footer.png';
    $this->name = __class__;
    $this->title = __('Depositphotos', self::ld);
    $this->credits_link = 'http://www.depositphotos.com/credits.html?ref=1003223';
    $this->subscribe_link = 'http://www.depositphotos.com/subscribe.html?ref=1003223';
    $this->register_link = 'http://depositphotos.com/wordpress-plugin.html';
    $this->detail_offer = '<a href="http://depositphotos.com/wordpress-plugin.html" target="_blank">'.__('Sign up at Depositphotos today', self::ld).'</a> '.__('and get 5 free credits plus 15% discount on your first purchase.', self::ld);
    $this->new = true;

    // definition of default settings
    $this->default_settings = array(
      'mpp_enabled' => true,
      'mpp_login' => '',
      'mpp_password' => '',
      'data' => false
    );

    // init API
    $this->api = new DepositClient(DEPOSIT_API_URL, self::API_key);

    // get session id if is valid
    if ($s = get_transient('DEPOSITAPI_session_id'))
      $this->api->setSessionId($s);

    // definitions for licenses
    $this->licenses = array(
      'xs' => array('name' => 'XSmall', 'mp' => 0.12),
      's' => array('name' => 'Small', 'mp' => 0.5),
      'm' => array('name' => 'Medium', 'mp' => 2),
      'l' => array('name' => 'Large', 'mp' => 4),
      'l+' => array('name' => 'Large+', 'mp' => 4.68),
      'xl' => array('name' => 'XLarge', 'mp' => 8),
      //'xxl' => array('name' => 'XXLarge', 'mp' => 15),
      'xxl_max' => array('name' => 'XXLarge', 'mp' => 0),
      'vect' => array('name' => __('Vector Image', self::ld), 'mp' => 0, 'dimensions' => __('No limits. Fits any dimension.', self::ld)),
      'el' => array('name' => 'Extended License', 'mp' => 0),
      'el0' => array('name' => 'Extended License', 'mp' => 0)
    );

  }

  public function getCopyright($author)
  {
    return '© '.$this->title.'.com / '.$author;
  }

  public function isLogged()
  {
    try
    {
      $this->api->checkLoggedIn();
      return true;
    }
    catch(Exception $e)
    {
      return false;
    }
  }


  public function login($data)
  {
    if (!isset($data['mpp_login']) || !isset($data['mpp_password']))
    {
      delete_transient('DEPOSITAPI_session_id');
      return false;
    }

    try
    {
      $r = $this->api->loginAsUser($data['mpp_login'], $data['mpp_password']);
      set_transient('DEPOSITAPI_session_id', $r->sessionid, 1800);

      return $r->userid;
    }
    catch(Exception $e) { }

    delete_transient('DEPOSITAPI_session_id');
    return false;
  }

  public function getUserData()
  {
    try
    {
      $r = $this->api->availableFunds();

      if (isset($r->activeSubscriptions) && count($r->activeSubscriptions) > 0)
      {
        $subscriptions = array();
        foreach($r->activeSubscriptions as $sub)
          $subscriptions[] = array(
            'limit' => $sub->dayLimit,
            'date' => $sub->tillDate
          );

        $subscription = array(
          'downloads' => $r->subscriptionDownloadsTodayAvailable,
          'subscriptions' => $subscriptions,
          'type' => 3
        );
      }
      else
        $subscription = false;

      $settings = $this->getSettings();

      $data = array(
        'display_name' => $settings['mpp_login'],
        'credits' => $r->creditsAvailable,
        'subscription' => $subscription,
        'credits_link' => $this->credits_link,
        'subscribe_link' => $this->subscribe_link
      );

      return $data;
    }
    catch(Exception $e)
    {
      return false;
    }
  }

  public function search($data)
  {
    try
    {
      $search_photo = 1;
      $search_vector = 0;

      if (isset($data['filters']) && $data['filters'] && is_array($data['filters']))
      {
        $search_photo = in_array('photo', $data['filters'])?1:0;
        $search_vector = in_array('vector', $data['filters'])?1:0;
      }
      else
        $search_photo = 1;


      if (isset($data['sort']) && $data['sort'] && $data['sort'] > 0 && $data['sort'] < 7)
        $order = $data['sort'];
      else
        $order = 1;

      $r = $this->api->search(array(
        'dp_search_query' => $data['text'],
        'dp_search_limit' => $data['limit'],
        'dp_search_offset' => ($data['page']-1) * $data['limit'],
        'dp_search_photo' => $search_photo,
        'dp_search_vector' => $search_vector,
        'dp_search_sort' => $order
      ));

      $images = array();
      foreach($r->result as $image)
      {
        $images[] = array(
          'id' => $image->id,
          'title' => $image->title,
          'thumbnail_url' => $image->thumbnail,
          'thumbnail_width' => false,
          'thumbnail_height' => false,
          'licenses' => false,
          'image_url' => $image->url2,
          'image_width' => false,
          'image_height' => false
        );
      }

      return array(
        'nb' => $r->count,
        'images' => $images
      );
    }
    catch(Exception $e) { }

    return false;
  }

  public function getPopularImages()
  {
    return $this->search(array(
      'limit' => 24,
      'order' => 3,
      'page' => mt_rand(0, 100),
      'text' => ''
    ));
  }

  protected function calculateSize($width, $height, $mp)
  {
    $new_height = sqrt($mp * 1000000 * $height / $width);
    $new_width = $new_height * $width / $height;
    return array('width' => round($new_width), 'height' => round($new_height));
  }

  public function detail($id)
  {
    try
    {
      $r = $this->api->getMediaData($id);

      $licenses = array();
      foreach($r->available_sizes as $name=>$price)
      {
        if (isset($this->licenses[$name]))
          $license = $this->licenses[$name];
        else
          $license = array(
            'name' => $name,
            'mp' => 0
          );

        if ($r->itype == 'image' && $name == 'vect')
          continue;

        if ($r->itype == 'vector' && $name == 'xxl_max')
          $license['mp'] = $r->mp;

        /*
        if (!$r->isextended && in_array($name, array('el', 'el0')))
          continue;
        */

        if ($license['mp'] == 0 && $r->itype == 'image')
          $dimensions = $r->width.' x '.$r->height.' ('.$r->mp.' MP)';
        else
        if ($license['mp'] == 0 && $r->itype == 'vector')
          $dimensions = $this->licenses['vect']['dimensions'];
        else
        if ($license['mp'] > 0)
        {
          $ca = $this->calculateSize($r->width, $r->height, $license['mp']);
          $dimensions = $ca['width'].' x '.$ca['height'].' ('.$license['mp'].' MP)';
        }

        $licenses[] = array(
          'name' => $name,
          'title' => $license['name'],
          'price' => $price,
          'dimensions' => $dimensions
        );

        // generate subscription licenses
        if ($name != 'el0')
        {
          $name = 'subscription_'.$name;
          $licenses_subscription[] = array(
            'name' => $name,
            'title' => __('Subscription', self::ld).' '.$license['name'],
            'price' => 1,
            'dimensions' => $dimensions
          );
        }
      }

      return array(
        'id' => $r->id,
        'title' => $r->title,
        'creator_name' => $r->username,
        'creation_date' => $r->published,
        'thumbnail_url' => $r->url2,
        'thumbnail_width' => 400,
        'thumbnail_height' => 0,
        'width' => $r->width,
        'height' => $r->height,
        'for_subscription' => false,
        'licenses' => $licenses,
        'licenses_subscription' => $licenses_subscription,
        'image_page' => $r->itemurl
      );
    }
    catch(Exception $e) { }

    return false;
  }

  public function buy($data, $test = false)
  {
    if ($test)
    {
      try
      {
        $r = $this->api->getMediaData($data['id']);

        $ext = pathinfo(basename($r->url_big), PATHINFO_EXTENSION);
        $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$ext);
        $filename = $this->getValidFilename($filename, $ext);

        if ($this->downloadFile($r->url_big, $filename))
          return $filename;
      }
      catch(Exception $e) {
      }
    }
    else
    {
      $license = str_replace('subscription_', '', $data['license']);
      try
      {
        $r = $this->api->getMedia($data['id'],
          $license == 'el0'?RpcParams::LICENSE_EXTENDED:RpcParams::LICENSE_STANDART,
          $license, null,
          strpos($data['license'], 'subscription_') === false?RpcParams::CURRENCY_CREDITS:RpcParams::CURRENCY_SUBSCRIPTION
        );

        $downloadUrl = $this->getRedir($r);

        $ext = 'temp';
        $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$ext);
        $old_filename = $this->getValidFilename($filename, $ext);

        if ($this->downloadFile($downloadUrl, $old_filename) && $this->downloadFilename)
        {
          $ext = pathinfo(basename($this->downloadFilename), PATHINFO_EXTENSION);
          $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$ext);
          $filename = $this->getValidFilename($filename, $ext);
          rename($old_filename, $filename);

          return $filename;
        }
      }
      catch(Exception $e)
      {
        $r = $this->api->getLastResponse();

        if (isset($r->error))
        {
          switch($r->error)
          {
            case 'No available payment methods':
              $message = __("You don't have enough credits to buy this image.", self::ld).' ';
              $message.= '<a href="'.$this->credits_link.'" target="_blank">'.__('Buy more credits here!', self::ld).'</a>';
              break;

            case 'e_no_credits':
              $message = __("You don't have enough credits to buy this image.", self::ld).' ';
              $message.= '<a href="'.$this->credits_link.'" target="_blank">'.__('Buy more credits here!', self::ld).'</a>';
              break;

            case 'e_subscription_dec':
              $message = __("You don't have an active subscription.", self::ld).' ';
              $message.= '<a href="'.$this->subscribe_link.'" target="_blank">'.__('Buy or extend your subscription here!', self::ld).'</a>';
              break;

            default:
              $message = __('Unknown error. Please try again later', self::ld);
          }
        }

        return array(
          'message' => $message
        );
      }
    }

    return false;
  }

  public function getSearchFilters()
  {
    return array(
      'photo' => __('Stock Photos', self::ld),
      'vector' => __('Vectors', self::ld)
    );
  }

  public function getAffiliateLink($image_page, $aff_id)
  {
    return $image_page.($aff_id?'?ref='.$aff_id:'');
  }

  public function getSortOptions()
  {
    return array(
      '1' => __('The best match', self::ld),
      '2' => __('Download quantity', self::ld),
      '3' => __('Popularity', self::ld),
      '4' => __('The best sales', self::ld),
      '5' => __('Publishing date ASC', self::ld),
      '6' => __('Publishing date DESC', self::ld)
    );
  }
}
