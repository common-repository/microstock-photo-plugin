<?php
// iStock module for Microstock Photo Plugin

require_once 'xmlrpc/xmlrpc.inc';


class MicrostockPhotoPlugin_istock extends MicrostockPhotoPlugin_module
{
  const API_key = '23cd876601e77ec23930e793';
  const user_agent = 'Microstock Photo Plugin';
  protected $client, $session_id = false;

  public function __construct()
  {
    parent::__construct();
    $this->_order = 40;
    $this->_path = dirname(__FILE__);
    $this->_url = plugins_url('', __FILE__);

    $this->icon = $this->_url.'/admin/images/istock.png';
    $this->logo = $this->_url.'/admin/images/istock-logo-footer.png';
    $this->name = __class__;
    $this->title = __('iStock', self::ld);
    $this->credits_link = 'http://www.linkconnector.com/traffic_affiliate.php?lc=046369041270004382&atid=microstock-photo-plugin&lcpf=0';
    $this->register_link = 'http://www.linkconnector.com/traffic_affiliate.php?lc=046369051341004382&amp;atid=microstock-photo-plugin-register&amp;lcpf=0';

    // definition of default settings
    $this->default_settings = array(
      'mpp_enabled' => true,
      'mpp_login' => '',
      'mpp_password' => '',
      'mpp_affiliate' => false,
      'mpp_affiliate_id' => ''
    );

    // get session id if is valid
    if ($s = get_transient(__class__.'_session_id'))
    {
      $this->session_id = $s;
      $this->logged = true;
    }

    // init XMLRPC client
    $this->initXMLRPC();
  }

  protected function initXMLRPC()
  {
    $this->client = new xmlrpc_client('https://secure-api.istockphoto.com/webservices/xmlrpc'.($this->session_id?'?sessionID='.$this->session_id:''));
    $this->client->setSSLVerifyPeer(false);
  }

  public function login($data)
  {
    if (!isset($data['mpp_login']) || !isset($data['mpp_password']))
      return false;

    $m = new xmlrpcmsg('istockphoto.auth.login', array(new xmlrpcval(array(
      'apiKey' => new xmlrpcval(self::API_key, 'string'),
      'loginMembername' => new xmlrpcval($data['mpp_login'], 'string'),
      'loginPassword' => new xmlrpcval($data['mpp_password'], 'string')
    ), 'struct')));

    $r = $this->client->send($m);

    if ($r->faultCode() == 0)
    {
      $v = new SimpleXMLElement($r->value()->scalarVal());
      $this->session_id = (string)$v['sessionid'];
      set_transient(__class__.'_session_id', $this->session_id, 1800);
      $this->logged = true;
      $this->initXMLRPC();
      return true;
    }

    delete_transient(__class__.'_session_id');
    $this->logged = false;
    return false;
  }

  public function getUserData()
  {
    $m = new xmlrpcmsg('istockphoto.member.getAccountInfo', array(new xmlrpcval(array(
      'apiKey' => new xmlrpcval(self::API_key, 'string')
    ), 'struct')));

    $r = $this->client->send($m);

    if ($r->faultCode() == 0)
    {
      $v = new SimpleXMLElement($r->value()->scalarVal());

      if (isset($v->member->accountInfo->creditBalanceSubscription))
      {
        $subscription = array(
          'credits' => (string)$v->member->accountInfo->creditBalanceSubscription['balance']
        );
      }
      else
        $subscription = false;

      return array(
        'display_name' => (string)$v['membername'],
        'credits' => (string)$v->member->accountInfo['creditBalance'],
        'subscription' => $subscription,
        'credits_link' => $this->credits_link
      );
    }

    return false;
  }

  public function search($data)
  {
    $filters = array();
    if (isset($data['filters']) && $data['filters'] && is_array($data['filters']))
    {
      if (in_array('photo', $data['filters']))
        $filters[] =
          new xmlrpcval(array(
            'type' => new xmlrpcval('Image', 'string'),
            'size' => new xmlrpcval('All', 'string'),
            'priceOption' => new xmlrpcval('1', 'string')
          ), 'struct');


      if (in_array('vector', $data['filters']))
        $filters[] =
          new xmlrpcval(array(
            'type' => new xmlrpcval('Illustration [Vector]', 'string'),
            'size' => new xmlrpcval('Vector Image', 'string'),
            'priceOption' => new xmlrpcval('All', 'string')
          ), 'struct');
    }
    else
    {
      $filters[] =
        new xmlrpcval(array(
          'type' => new xmlrpcval('Image', 'string'),
          'size' => new xmlrpcval('All', 'string'),
          'priceOption' => new xmlrpcval('1', 'string')
        ), 'struct');
    }

    $sorts = array('BestMatch', 'Age', 'Contributor', 'Rating', 'Downloads', 'Title', 'Size');
    if (isset($data['sort']) && $data['sort'] && in_array($data['sort'], $sorts))
      $order = $data['sort'];
    else
      $order = 'BestMatch';


    $m = new xmlrpcmsg('istockphoto.search.search', array(new xmlrpcval(array(
      'apiKey' => new xmlrpcval(self::API_key, 'string'),
      'text' => new xmlrpcval($data['text'], 'string'),
      'page' => new xmlrpcval($data['page'], 'int'),
      'order' => new xmlrpcval($order, 'string'),
      'perPage' => new xmlrpcval($data['limit'], 'int'),
      'fileTypeSizePrice' => new xmlrpcval($filters, 'struct'),
      'imagePropertyOptions' => new xmlrpcval(array(
        new xmlrpcval(array(
          'name' => new xmlrpcval('title', 'string'),
          'value' => new xmlrpcval(true, 'boolean')
        ), 'struct'),
        new xmlrpcval(array(
          'name' => new xmlrpcval('largethumburl', 'string'),
          'value' => new xmlrpcval(true, 'boolean')
        ), 'struct'),
        new xmlrpcval(array(
          'name' => new xmlrpcval('mediumthumbsize', 'string'),
          'value' => new xmlrpcval(true, 'boolean')
        ), 'struct'),
        new xmlrpcval(array(
          'name' => new xmlrpcval('smallthumburl', 'string'),
          'value' => new xmlrpcval(true, 'boolean')
        ), 'struct'),
        new xmlrpcval(array(
          'name' => new xmlrpcval('smallthumbsize', 'string'),
          'value' => new xmlrpcval(true, 'boolean')
        ), 'struct')
      ), 'struct')
    ), 'struct')));

    $r = $this->client->send($m);

    if ($r->faultCode() == 0)
    {
      $v = new SimpleXMLElement($r->value()->scalarVal());

      $images = array();
      foreach($v->imageList->image as $image)
        $images[] = array(
          'id' => $image['fileid'],
          'title' => $image['title'],
          'thumbnail_url' => $image['smallthumburl'],
          'thumbnail_width' => $image['smallthumbw'],
          'thumbnail_height' => $image['smallthumbh'],
          'licenses' => false,
          'image_url' => $image['largethumburl'],
          'image_width' => $image['mediumthumbw'],
          'image_height' => $image['mediumthumbh']
        );

      return array(
        'nb' => (int)$v->imageList['totalitems'],
        'images' => $images
      );
    }

    return false;
  }

  public function detail($id)
  {
    $m = new xmlrpcmsg('istockphoto.image.getInfo', array(new xmlrpcval(array(
      'apiKey' => new xmlrpcval(self::API_key, 'string'),
      'fileID' => new xmlrpcval($id, 'int'),
      // 'language' => new xmlrpcval(, 'string') // TODO
    ), 'struct')));

    $r = $this->client->send($m);

    if ($r->faultCode() == 0)
    {
      $v = new SimpleXMLElement($r->value()->scalarVal());

      $licenses = array();
      foreach($v->image->fileList->file as $license)
        $licenses[] = array(
          'name' => $license['size'],
          'title' => $license['size'],
          'price' => $license['credits'],
          'dimensions' => $license['width'].' x '.$license['height'].' ('.$license['dpi'].' DPI)'
        );

      return array(
        'id' => $v->image['fileid'],
        'title' => $v->image['title'],
        'creator_name' => $v->image['membername'],
        'creation_date' => $v->image['creationdate'],
        'thumbnail_url' => $v->image['largethumburl'],
        'thumbnail_width' => 400,
        'thumbnail_height' => 0,
        'width' => 0,
        'height' => 0,
        'for_subscription' => false,
        'licenses' => $licenses,
        'license_type' => $v->image['type'],
        'image_page' => $v->image['imagepageurl']
      );
    }

    return false;
  }

  public function license($data = false)
  {
    if ($data)
    {
      $m = new xmlrpcmsg('istockphoto.download.getLicenseText', array(new xmlrpcval(array(
        'apiKey' => new xmlrpcval(self::API_key, 'string'),
        'fileType' => new xmlrpcval($data, 'string')
      ), 'struct')));

      $r = $this->client->send($m);

      if ($r->faultCode() == 0)
      {
        // SimpleXMLElement can't parse it so we will do it via regular expressions
        if (preg_match("/<contentLicenseAgreement type=\".[^\"]*?\"><!\[CDATA\[(.*?)\]\]><\/contentLicenseAgreement>/is", $r->value()->scalarVal(), $v))
          return html_entity_decode(str_replace('?', '"', utf8_encode($v[1])), ENT_COMPAT, 'UTF-8');

        return false;
      }
    }

    return false;
  }

  public function buy($data, $test = false)
  {
    if ($test)
    {
      $m = new xmlrpcmsg('istockphoto.image.getInfo', array(new xmlrpcval(array(
        'apiKey' => new xmlrpcval(self::API_key, 'string'),
        'fileID' => new xmlrpcval($data['id'], 'int'),
        // 'language' => new xmlrpcval(, 'string') // TODO
      ), 'struct')));

      $r = $this->client->send($m);

      if ($r->faultCode() == 0)
      {
        $v = new SimpleXMLElement($r->value()->scalarVal());

        $ext = pathinfo(basename($v->image['largethumburl']), PATHINFO_EXTENSION);
        $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$ext);
        $filename = $this->getValidFilename($filename, $ext);

        if ($this->downloadFile($v->image['largethumburl'], $filename))
          return $filename;
      }
    }
    else
    {
      $m = new xmlrpcmsg('istockphoto.download.downloadFile', array(new xmlrpcval(array(
        'apiKey' => new xmlrpcval(self::API_key, 'string'),
        'fileID' => new xmlrpcval($data['id'], 'int'),
        'fileSize' => new xmlrpcval($data['license'], 'string'),
        'agreeToLicense' => new xmlrpcval(true, 'boolean')
      ), 'struct')));

      $r = $this->client->send($m);

      if ($r->faultCode() == 0)
      {
        $v = new SimpleXMLElement($r->value()->scalarVal());

        $ext = pathinfo($v->imageFile['filename'], PATHINFO_EXTENSION);
        $filename = $data['path'].'/'.$this->format_uri($data['title'].'.'.$ext);
        $filename = $this->getValidFilename($filename, $ext);

        if ($f = fopen($filename, 'w'))
        {
          fwrite($f, base64_decode($v->imageFile));
          fclose($f);
          return $filename;
        }

        return false;
      }
      else
      if ($r->faultCode() == 6000)
        return array(
          'message' => __('Not enough credits.', self::ld)
        );
      else
      if ($r->faultCode() == 6001)
        return array(
          'message' => __('File attempting to download is not available.', self::ld)
        );
      else
      if ($r->faultCode() == 6002)
        return array(
          'message' => __('Size for the file attempting to download is not available.', self::ld)
        );
      else
      if ($r->faultCode() == 6003)
        return array(
          'message' => __('Error in reading file.', self::ld)
        );
      else
      if ($r->faultCode() == 6006)
        return array(
          'message' => __('Error in processing your purchase.', self::ld)
        );
      else
      if ($r->faultCode() == 6011)
        return array(
          'message' => __('Sorry, you are unable to download the requested file with an Extended License.', self::ld)
        );
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
    if ($aff_id)
      return $aff_id.($aff_id[count($aff_id)-1] == '/'?'':'/').urlencode($image_page);
    else
      return $image_page;
  }

  public function getSortOptions()
  {
    return array(
      'BestMatch' => __('The best match', self::ld),
      'Age' => __('Date', self::ld),
      'Contributor' => __('Contributor', self::ld),
      'Rating' => __('Rating', self::ld),
      'Downloads' => __('Downloads', self::ld),
      'Title' => __('Title', self::ld),
      'Size' => __('Size', self::ld)
    );
  }

  public function getStatusText()
  {
    return '<span style="color: #ff0000;">'.__('The iStock search is slow due slow API connection.', self::ld).'</span>';
  }
}
