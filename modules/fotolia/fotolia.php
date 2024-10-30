<?php
// Fotolia module for Microstock Photo Plugin

if (!class_exists('Fotolia_Api'))
  require_once 'api/fotolia-api.php';

class MicrostockPhotoPlugin_fotolia extends MicrostockPhotoPlugin_module
{
  const API_key = 'CpRg7UC8fYuuUWs5d9BGotGnmTpbZ0Xc';
  protected $api;

  public function __construct()
  {
    parent::__construct();
    $this->_order = 20;
    $this->_path = dirname(__FILE__);
    $this->_url = plugins_url('', __FILE__);

    $this->icon = $this->_url.'/admin/images/fotolia.png';
    $this->logo = $this->_url.'/admin/images/fotolia-logo-footer.png';
    $this->name = __class__;
    $this->title = __('Fotolia', self::ld);
    $this->credits_link = 'https://www.fotolia.com/Member/BuyCreditsChooseAmount?utm_source=7757&utm_medium=affiliation&utm_content=7757';
    $this->subscribe_link = 'https://www.fotolia.com/Member/Subscription/Upgrade?utm_source=7757&utm_medium=affiliation&utm_content=7757';
    $this->register_link = 'https://us.fotolia.com/stockphotosecrets';

    // definition of default settings
    $this->default_settings = array(
      'mpp_enabled' => true,
      'mpp_login' => '',
      'mpp_password' => '',
      'mpp_affiliate' => false,
      'mpp_affiliate_id' => '',
      'data' => false
    );

    // init API
    $this->api = new Fotolia_Api(self::API_key);
  }

  public function setAPIKey($apiKey)
  {
    $this->api = new Fotolia_Api($apiKey);
    return true;
  }

  public function login($data)
  {
    if (!isset($data['mpp_login']) || !isset($data['mpp_password']))
      return false;

    try
    {
      $this->api->loginUser($data['mpp_login'], $data['mpp_password']);
      $this->logged = true;

      // get user's affiliate ID
      try
      {
        $r = $this->api->getSearchResults(array(
          'words' => 'test',
          'language_id' => 2,
          'limit' => 1,
          'detail_level' => 1,
          'offset' => 0
        ));
      }
      catch(Fotolia_Api_Exception $e) { $r = false; }

      if ($r)
      {
        $link = $r[0]['affiliation_link'];
        $aff = substr($link, strrpos($link, '/') + 1, strlen($link) - strrpos($link, '/') - 1);
      }
      else
        $aff = true;

      return $aff;
    }
    catch(Fotolia_Api_Exception $e)
    {
      // nothing here
    }
    $this->logged = false;
    return false;
  }

  public function getUserData()
  {
    try
    {
      $r = $this->api->getUserData();

      if ($r['has_subscription'])
        $subscription = array(
          'type' => $r['subscription_quota_type_id'],
          'downloads' => $r['subscription_nb_downloads_left']
        );
      else
        $subscription = false;

      return array(
        'display_name' => $r['display_name'],
        'credits' => $r['nb_credits_localized'],
        'subscription' => $subscription,
        'credits_link' => $this->credits_link,
        'subscribe_link' => $this->subscribe_link
      );
    }
    catch(Fotolia_Api_Exception $e)
    {
      return false;
    }
  }

  public function getCategories()
  {
    try
    {
      $r = $this->api->getCategories1(array(

      ));
    }
    catch(Fotolia_Api_Exception $e)
    {
      return false;
    }
  }

  public function search($data)
  {
    try
    {
      $filters = array('content_type:video' => 0);

      $filters['content_type:photo'] = $it_photos?1:0;
      $filters['content_type:illustration'] = $it_ilustrations?1:0;
      $filters['content_type:vector'] = $it_vectors?1:0;

      if (isset($data['filters']) && $data['filters'] && is_array($data['filters']))
      {
        $filters['content_type:photo'] = in_array('photo', $data['filters'])?1:0;
        $filters['content_type:illustration'] = in_array('illustration', $data['filters'])?1:0;
        $filters['content_type:vector'] = in_array('vector', $data['filters'])?1:0;
      }
      else
      {
        $filters['content_type:photo'] = 1;
        $filters['content_type:illustration'] = 1;
        $filters['content_type:vector'] = 1;
      }


      $orders = array('relevance', 'price_1', 'creation', 'nb_views', 'nb_downloads');
      if (isset($data['sort']) && $data['sort'] && in_array($data['sort'], $orders))
        $order = $data['sort'];
      else
        $order = 'relevance';


      $r = $this->api->getSearchResults(array(
        'words' => $data['text'],
        //'language_id' => , TODO
        'limit' => $data['limit'],
        //'order' => , TODO
        'thumbnail_size' => '110',
        'detail_level' => 1,
        'filters' => $filters,
        'order' => $order,
        'offset' => ($data['page']-1) * $data['limit']
      ));

      $nb = $r['nb_results'];
      next($r);
      $images = array();
      while(list(, $image) = @each($r))
      {
        $images[] = array(
          'id' => $image['id'],
          'title' => $image['title'],
          'thumbnail_url' => $image['thumbnail_url'],
          'thumbnail_width' => $image['thumbnail_width'],
          'thumbnail_height' => $image['thumbnail_height'],
          'licenses' => $image['licenses'],
          'image_url' => $image['thumbnail_400_url'],
          'image_width' => $image['thumbnail_400_width'],
          'image_height' => $image['thumbnail_400_height']
        );
      }

      return array(
        'nb' => $nb,
        'images' => $images
      );
    }
    catch(Fotolia_Api_Exception $e)
    {
    }

    return false;
  }

  public function detail($id)
  {
    try
    {
      $r = $this->api->getMediaData($id, 400); // TODO language?

      // stuff around subscription
      $data = $this->getData();
      $can_subcription = false;

      if (isset($data['subscription']))
      {
        // if daily subscription then check if there is API
        if ($data['subscription']['type'] == 1)
        {
          $settings = $this->getSettings();
          // and custom API key is valid
          if (isset($settings['customAPI']) && $settings['customAPI'] == 2 &&
              isset($settings['mpp_custom_api_key']) && $settings['mpp_custom_api_key'])
            $can_subscription = true;
        }
        else
        if ($data['subscription']['type'] == 2)
          $can_subscription = true;
      }


      $licenses = array();
      $licenses_subscription = $r['available_for_subscription']?array():false;
      foreach($r['licenses'] as $license)
      {
        $licenses[] = array(
          'name' => $license['name'],
          'title' => $license['name'],
          'price' => $license['price'],
          'dimensions' => $r['licenses_details'][$license['name']]['dimensions']
        );

        // generate subscription licenses
        if ($r['available_for_subscription'] && $can_subscription)
        {
          if (in_array($license['name'], array('XS', 'X')))
            continue;

          $name = 'Subscription_'.($data['subscription']['type']==2?'Monthly_':'').$license['name'];
          $licenses_subscription[] = array(
            'name' => $name,
            'title' => __('Subscription', self::ld).' '.$license['name'],
            'price' => 1,
            'dimensions' => $r['licenses_details'][$license['name']]['dimensions']
          );
        }
      }

      return array(
        'id' => $r['id'],
        'title' => $r['title'],
        'creator_name' => $r['creator_name'],
        'creation_date' => $r['creation_date'],
        'thumbnail_url' => $r['thumbnail_url'],
        'thumbnail_width' => $r['thumbnail_width'],
        'thumbnail_height' => $r['thumbnail_height'],
        'width' => $r['width'],
        'height' => $r['height'],
        'licenses' => $licenses,
        'licenses_subscription' => $licenses_subscription,
        'image_page' => 'http://en.fotolia.com/id/'.$r['id']
      );
    }
    catch(Fotolia_Api_Exception $e)
    {
    }

    return false;
  }

  public function buy($data, $test = false)
  {
    if ($test)
    {
      try
      {
        $r = $this->api->getMediaData($data['id'], 400);

        $ext = pathinfo(basename($r['thumbnail_url']), PATHINFO_EXTENSION);
        $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$ext);
        $filename = $this->getValidFilename($filename, $r['extension']);

        if ($this->downloadFile($r['thumbnail_url'], $filename))
          return $filename;
      }
      catch(Fotolia_Api_Exception $e) { }
    }
    else
    {
      try
      {
        $r = $this->api->getMedia($data['id'], $data['license']);

        $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$r['extension']);
        $filename = $this->getValidFilename($filename, $r['extension']);

        $this->api->downloadMedia($r['url'], $filename);

        return $filename;
      }
      catch(Fotolia_Api_Exception $e)
      {
        switch($e->getCode())
        {
          case 001:
            return array(
              'message' => __('Service currently unavailable.', self::ld)
            );
            break;

          case 101:
            return array(
              'message' => __('Invalid Media ID.', self::ld)
            );
            break;

          case 2201:
            return array(
              'message' => __('Invalid License Name.', self::ld)
            );
            break;

          case 3000:
            return array(
              'message' => __('Insufficent Credit Value.', self::ld)
            );
            break;

          case 3099:
            return array(
              'message' => __('Not enough download slots left.', self::ld)
            );
            break;
        }
      }
    }
    return false;
  }

  public function getSearchFilters()
  {
    return array(
      'photo' => __('Stock Photos', self::ld),
      'illustration' => __('Illustrations', self::ld),
      'vector' => __('Vectors', self::ld)
    );
  }

  public function getAffiliateLink($image_page, $aff_id)
  {
    return $image_page.($aff_id?'/partner/'.$aff_id:'');
  }

  public function getSortOptions()
  {
    return array(
      'relevance' => __('Relevance', self::ld),
      'price_1' => __('Price', self::ld),
      'creation' => __('Creation Date', self::ld),
      'nb_views' => __('Number of Views', self::ld),
      'nb_downloads' => __('Number of Downloads', self::ld)
    );
  }
}
