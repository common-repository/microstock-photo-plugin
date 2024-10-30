<?php
// Base class for Microstock Photo Plugin modules

class MicrostockPhotoPlugin_module
{
  // localization domain
  const ld = "microstock-photo-plugin";

  protected $_path, $_url;

  // ordering value
  public $_order = 0;

  // if user is logged
  protected $logged = false;

  // name, title and icon of the module
  protected $name, $title, $icon, $logo, $credits_link = '', $new = false, $register_link = '', $detail_offer = '';

  // default settings
  protected $default_settings = array();

  protected $downloadFilename = false;

  // main contructor
  public function __construct()
  {
    add_action('admin_enqueue_scripts', array(&$this, 'enqueue_scripts')); // enqueue scripts and styles
  }

  public function getPath()
  {
    return $this->_path;
  }

  public function getIcon()
  {
    return $this->icon;
  }

  public function getTitle()
  {
    return $this->title;
  }

  public function getName()
  {
    return $this->name;
  }

  public function getLogo()
  {
    return $this->logo;
  }

  public function getCreditsLink()
  {
    return $this->credits_link;
  }

  public function getRegisterLink()
  {
    return $this->register_link;
  }

  public function getDetailOffer()
  {
    return $this->detail_offer;
  }

  public function getCopyright($author)
  {
    return '© '.$author.' - '.$this->title.'.com';
  }

  // enqueue scripts and styles if necessary
  public function enqueue_scripts($hook) { }

  // activation hook
  public function activation()
  {
    add_option($this->name.'_settings', $this->default_settings);
  }

  // uninstall hook
  public function uninstall()
  {
    delete_option($this->name.'_settings');
  }

  // get unique ID for field
  public function getFieldID($id)
  {
    return $id.'_'.$this->name;
  }

  public function fieldID($id)
  {
    echo $this->getFieldID($id);
  }

  // set settings
  public function setSettings($data)
  {
    update_option($this->name.'_settings', $data);
  }

  // get settings
  public function getSettings()
  {
    return get_option($this->name.'_settings', $this->default_settings);
  }

  // set data
  public function setData($data)
  {
    update_option($this->name.'_data', $data);
  }

  // get data
  public function getData()
  {
    return get_option($this->name.'_data', false);
  }


  // render settings in metabox
  public function settings()
  {
    $settings = $this->getSettings();
    $data = $this->getData();
    $className = $this->name;
    require_once $this->_path.'/admin/settings.php';
  }

  public function isLogged()
  {
    return $this->logged;
  }

  protected function getRedir($url)
  {
    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_FRESH_CONNECT => true,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_HEADER => true,
      CURLOPT_RETURNTRANSFER => true
    ));

    $header = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code == 301 || $http_code == 302)
    {
      $matches = array();
      preg_match('/Location:(.*?)\n/', $header, $matches);
      $url = @parse_url(trim(array_pop($matches)));

      if ($url)
      {
        $last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));
        if (!$url['scheme']) $url['scheme'] = $last_url['scheme'];
        if (!$url['host']) $url['host'] = $last_url['host'];
        if (!$url['path']) $url['path'] = $last_url['path'];

        $new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . ($url['query']?'?'.$url['query']:'');

        return $new_url;
      }
    }

    curl_close($ch);
    return false;
  }

  protected function headerCallback($ch, $str)
  {
    // catch content disposition with name of the file
    if (preg_match('/Content-Disposition:.+?filename="(.[^"]+)/i', $str, $o))
      $this->downloadFilename = $o[1];

    return strlen($str);
  }

  protected function downloadFile($source_url, $dest_path)
  {
    if (!$file = fopen($dest_path, 'w'))
      return false;

    $ch = curl_init();
    curl_setopt_array($ch, array(
      CURLOPT_URL => $source_url,
      CURLOPT_FOLLOWLOCATION => false,
      CURLOPT_FILE => $file,
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_HEADERFUNCTION => array($this, 'headerCallback')
    ));
    curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    fclose($file);
    return $info['http_code'] == '200'?$info:false;
  }

  protected function format_uri($string, $separator = '-')
  {
    $accents_regex = '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i';
    $special_cases = array( '&' => 'and');
    $string = mb_strtolower( trim( $string ), 'UTF-8' );
    $string = str_replace(array_keys($special_cases), array_values( $special_cases), $string);
    $string = preg_replace($accents_regex, '$1', htmlentities( $string, ENT_QUOTES, 'UTF-8'));
    $string = preg_replace("/[^a-z0-9\.]/u", "$separator", $string);
    $string = preg_replace("/[$separator]+/u", "$separator", $string);
    return $string;
  }

  protected function getValidFilename($filename, $ext)
  {
    $tempname = $filename;
    $c = 0;
    while(file_exists($tempname))
    {
      $c++;
      $tempname = str_replace('.'.$ext, '-'.$c.'.'.$ext, $filename);
    }
    return $tempname;
  }

  public function isNew()
  {
    return $this->new;
  }

  // API calls
  public function login($data) { return false; }

  public function getUserData() { return false; }

  public function getCategories() { return false; }

  public function search($data) { return false; }

  public function detail($id) { return false; }

  public function license($data = false) { return false; }

  public function buy($data, $test = false) { return false; }

  public function getSearchFilters() { return false; }

  public function getAffiliateLink($image_page, $aff_id) { return false; }

  public function getSortOptions() { return false; }

  public function getStatusText() { return false; }

  public function setAPIKey($apiKey) { return false; }

  public function getPopularImages() { return false; }
}

function module_sort_cmp($a, $b)
{
  if ($a->_order == $b->_order) return 0;
  else return $a->_order > $b->_order?1:-1;
}

// look for modules in folders
$modules = array();
$path = dirname(__FILE__).'/';
$d = opendir($path);
while (false !== ($entry = readdir($d)))
{
  $filename = $path.$entry;
  if (is_dir($filename) && $entry != '.' && $entry != '..')
  {
    $module_filename = $filename.'/'.$entry.'.php';
    if (file_exists($module_filename))
    {
      require_once $module_filename;
      $class_name = 'MicrostockPhotoPlugin_'.$entry;
      if (class_exists($class_name))
        $modules[$class_name] = new $class_name;
    }
  }
}
closedir($d);

uasort($modules, 'module_sort_cmp');
$this->modules = $modules;


