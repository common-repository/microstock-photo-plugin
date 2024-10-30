<?php
/*
Plugin Name: Microstock Photo Plugin
Plugin URI: http://www.microstockplugin.com
Description: Easily add stock photos to your blog posts without leaving Wordpress. Choose from more than 50 million (!) stock photos from Depositphotos, Fotolia and iStock. Enable the affiliate options to earn money from affiliate links automatically. Simply select the photo you want to use, purchase a license to use it from $1, and the plugin will automatically add it to your blog post. Edit the photo online with our powerful integrated photo editor. Choose from a custom caption or an automatically generated copyright notice.
Author: Idenio GmbH
Version: 3.1.1
Author URI: http://www.microstockplugin.com
Text Domain: microstock-photo-plugin
*/

class MicrostockPhotoPlugin
{
  // localization domain
  const ld = "microstock-photo-plugin";

  // version of the plugin
  const version = '3.1.1';

  // test buy for debug purpose
  const debug = true;

  // aviary editor API key and secret
  const aviary_api_key = 'ybua2dju28it829n';
  const aviary_api_secret = 'h889vbmhqnn1gkt5';

  // synchronization URL to offers manager
  const sync_offers_url = 'http://www.stockphotosecrets.com/wp-admin/admin-ajax.php?action=MPPOffersManager_mpp&a=offers';

  // shedule time, can be twicedaily, daily, hourly
  // it's necessary to deactivate and activate the plugin to take effect
  const sync_offers_schedule = 'twicedaily';

  // table to store backups
  const tb_backups = 'mpp_backups';

  protected $_url, $_path;

  // list of available languages
  protected $languages;

  // default settings
  protected $default_settings;

  // stock photo modules
  protected $modules;

  public function __construct()
  {
    // paths
    $this->_url = plugins_url('', __FILE__);
    $this->_path = dirname(__FILE__);

    // list of available languages
    $this->languages = array(
      'English',
      'German',
      'Spanish',
      'Italian'
    );

    $this->default_settings = array(
      'mpp_default_language' => 0,
      'mpp_image_caption' => 3,
      'mpp_image_caption_custom' => __('Buy this photo', self::ld),
      'mpp_image_caption_custom_copyright' => __('Buy this photo', self::ld),
      'mpp_alt_text' => 0,
      'mpp_test_mode' => 0,
      'mpp_sync_offers' => 1,
      'mpp_show_add_button' => 1
    );

    $main_settings = get_option(__class__.'_settings', $this->default_settings);

    // load domain language on plugins_loaded action
    add_action('plugins_loaded', array(&$this, 'plugins_loaded'));

    // stuff in the admin backend
    if (is_admin())
    {
      // load modules
      require_once 'modules/load.php';
      $this->modules = $modules;

      // add controls only if there are some modules
      if (is_array($this->modules) && count($this->modules) > 0)
      {
        // show notice about configuration on the first time installation
        if (!get_option(__class__.'_config_notice_dismissed', false))
          add_action('admin_notices', array($this, 'config_notice'));

        add_action('admin_menu', array(&$this, 'admin_menu')); // add option page
        add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts')); // enqueue scripts and styles
        add_action('wp_ajax_'.__class__, array(&$this, 'ajax_admin')); // admin ajax actions
        add_action('wp_ajax_nopriv_'.__class__.'_aviary', array(&$this, 'ajax_aviary')); // aviary ajax action

        add_filter('media_upload_tabs', array(&$this, 'upload_tabs')); // add tabs for each module
        add_action('print_media_templates', array(&$this, 'print_media_templates'));

        // create page for each module
        foreach($this->modules as $module)
          add_action('media_upload_'.$module->getName(), array(&$this, 'upload_tab'));


        // extend attachment edit page
        add_filter('attachment_fields_to_edit', array(&$this, 'attachment_fields_to_edit'), 10, 2);
        add_action('edit_attachment', array(&$this, 'edit_attachment'));

        // modify media when should be inserted into editor
        add_filter('media_send_to_editor', array(&$this, 'media_send_to_editor'), 10, 3);

        // add a new button next to the "Add Media"
        if (isset($main_settings['mpp_show_add_button']) && $main_settings['mpp_show_add_button'])
          add_filter('media_buttons_context', array($this, 'media_buttons_context'));
      }
    }

    // sync offers hook
    $sync_offers = isset($main_settings['mpp_sync_offers'])?$main_settings['mpp_sync_offers']:$this->default_settings['mpp_sync_offers'];
    if ($sync_offers)
      add_action('mpp_sync_offers', array($this, 'sync_offers'));

    // on activation/uninstallation hooks
    register_activation_hook(__FILE__, array(&$this, 'activation'));
    register_deactivation_hook(__FILE__, array($this, 'deactivation'));
    register_uninstall_hook(__FILE__, array(__class__, 'uninstall'));
  }

  // shows config notice for the first time
  function config_notice()
  {
    require_once $this->_path.'/admin/config_notice.php';
  }


  // add a new button next to the "Add Media"
  public function media_buttons_context($context)
  {
    $context.= '<a href="#" class="button add-stock-photo" style="background-image: linear-gradient(to bottom,#fefefe,#EDC9AF);" title="'.esc_attr__('Add Stock Photo', self::ld).'"><img src="'.$this->_url.'/admin/images/icon.png" style="position: relative;top: -1px;left: -3px;padding-right: 2px;" width="16" height="16" /> '.__('Add Stock Photo', self::ld).'</a>';
    return $context;
  }

  // modify html output for affiliate program
  public function media_send_to_editor($html, $id, $attachment)
  {
    $data = get_metadata('post', $id, '_mpp_image_data', true);

    // add affiliate link
    if ($data && isset($data['affiliate_link']) && $data['affiliate_link'] &&
        isset($data['module']) && isset($this->modules[$data['module']]))
    {
      $module = $this->modules[$data['module']];
      $settings = $module->getSettings();

      // get affiliate id
      if (isset($settings['mpp_affiliate_id']) && $settings['mpp_affiliate_id'])
        $aff_id = $settings['mpp_affiliate_id'];
      else
        $aff_id = isset($settings['mpp_affiliate_id_user']) && $settings['mpp_affiliate_id_user'] !== true?$settings['mpp_affiliate_id_user']:'';

      $aff_link = $module->getAffiliateLink($data['page'], $aff_id);
      $html = strip_tags($html, '<img>');

      if (!preg_match('/\[caption.*?\](<img.*?>)\s*(.*?)\[\/caption\]/is', $html, $o))
        if (!preg_match('/(<img.*?>)\s*(.*?)/is', $html, $o))
          return $html;

      switch($data['affiliate_link'])
      {
        case 1:
          $html = str_replace($o[1], '<a href="'.$aff_link.'"'.(!$data['affiliate_window']?' target="_blank"':'').'>'.$o[1].'</a>', $html);
          break;
        case 2:
          $html = str_replace($o[2].'[/caption]', '<a href="'.$aff_link.'"'.(!$data['affiliate_window']?' target="_blank"':'').'>'.$o[2].'</a>[/caption]', $html);
          break;
        case 3:
          $html = str_replace(array($o[1], $o[2].'[/caption]'), array(
              '<a href="'.$aff_link.'"'.(!$data['affiliate_window']?' target="_blank"':'').'>'.$o[1].'</a>',
              '<a href="'.$aff_link.'"'.(!$data['affiliate_window']?' target="_blank"':'').'>'.$o[2].'</a>[/caption]'
            ), $html);
          break;
      }
    }

    return $html;
  }

  // add extra fields to attachment edit page
  public function attachment_fields_to_edit($fields, $post)
  {
    $data = get_metadata('post', $post->ID, '_mpp_image_data', true);

    $screen = get_current_screen();
    if (isset($screen->post_type) && $screen->post_type == 'attachment')
      $padding = 'padding-top: 3px';
    else
      $padding = 'padding-top: 8px';

    // get attachment URL
    $url = wp_get_attachment_url($post->ID);
    if ($url && (!isset($screen->post_type) || $screen->post_type != 'attachment'))
    {
      $fields['mpp_edit_button'] = array(
        'label' => ' ',
        'input' => 'html',
        'html' => '<br />
        <input type="button" style="width: auto;" data-id="'.$post->ID.'" data-url="'.esc_attr($url).'" class="button-primary" name="mpp_edit_button" value="'.esc_attr__('Edit Image', self::ld).'" />
        <span class="mpp_edit_button_loader" style="padding-top: 7px; padding-left: 8px; position: absolute; display: none;"><img src="'.$this->_url.'/admin/images/loader.gif" width="15" height="15" /></span>
        '
      );
    }

    if ($data)
    {
      $fields['mpp_title'] = array(
        'label' => '<b>'.__('Microstock Photo Plugin', self::ld).'</b>',
        'input' => 'html',
        'html' => ' '
      );

      $fields['mpp_source_name'] = array(
        'label' => __('Agency', self::ld),
        'input' => 'html',
        'html' => '<div style="'.$padding.'">'.$data['name'].'</div>'
      );

      $fields['mpp_source_link'] = array(
        'label' => __('Source URL', self::ld),
        'input' => 'html',
        'html' => '<div style="'.$padding.'"><a href="'.$data['page'].'" target="_blank">'.__('Click to Open', self::ld).'</a></div>'
      );

      $al = $data['affiliate_link'];
      $fields['mpp_affiliate_link'] = array(
        'label' => __('Affiliate Link', self::ld),
        'input' => 'html',
        'html' => '<select name="attachments['.$post->ID.'][mpp_affiliate_link]" id="attachments-'.$post->ID.'-mpp_affiliate_link">
            <option value="1"'.($al==1?' selected':'').'>'.__('Add on image', self::ld).'</option>
            <option value="2"'.($al==2?' selected':'').'>'.__('Add on caption', self::ld).'</option>
            <option value="3"'.($al==3?' selected':'').'>'.__('Image and caption', self::ld).'</option>
            <option value="0"'.($al==0?' selected':'').'>'.__('Disabled', self::ld).'</option>
          </select>'
      );

      $aw = $data['affiliate_window'];
      $fields['mpp_affiliate_window'] = array(
        'label' => ' ',
        'input' => 'html',
        'html' => '<select name="attachments['.$post->ID.'][mpp_affiliate_window]">
            <option value="0"'.($aw==0?' selected':'').'>'.__('Open in a new window', self::ld).'</option>
            <option value="1"'.($aw==1?' selected':'').'>'.__('Open in same window', self::ld).'</option>
          </select>'
      );

    }

    return $fields;
  }

  // save our data belong to attachment
  public function edit_attachment($attach_id)
  {
    if (isset($_REQUEST['attachments'][$attach_id]['mpp_affiliate_link']) &&
        isset($_REQUEST['attachments'][$attach_id]['mpp_affiliate_window']))
    {
      $data = get_metadata('post', $attach_id, '_mpp_image_data', true);

      if ($data)
      {
        $data['affiliate_link'] = $_REQUEST['attachments'][$attach_id]['mpp_affiliate_link'];
        $data['affiliate_window'] = $_REQUEST['attachments'][$attach_id]['mpp_affiliate_window'];
        update_metadata('post', $attach_id, '_mpp_image_data', $data);
      }
    }
  }

  // add module tabs
  public function upload_tabs($tabs)
  {
    foreach($this->modules as $module)
    {
      $settings = $module->getSettings();
      if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'])
        $tabs[$module->getName()] = $module->getTitle();
    }

    return $tabs;
  }

  // render content for module tab
  public function upload_tab()
  {
    wp_iframe(array(&$this, 'upload_tab_content'));
  }

  // content of the upload tab for modules
  public function upload_tab_content()
  {
    $tab = isset($_GET['tab'])?$_GET['tab']:false;

    if (isset($this->modules[$tab]))
    {
      $module = $this->modules[$tab];
      require_once $this->_path.'/admin/tab.php';
    }
  }

  // load localization text domain
  public function plugins_loaded()
  {
    load_plugin_textdomain(self::ld, false, dirname(plugin_basename(__FILE__)).'/languages/');
  }

  // on activation
  public function activation()
  {
    // set default options
    add_option(__class__.'_boxes', array());
    add_option(__class__.'_settings', $this->default_settings);
    add_option(__class__.'_offers', array());

    // schedule synchronization of offers if enabled
    if (!wp_next_scheduled('mpp_sync_offers'))
      wp_schedule_event(time(), self::sync_offers_schedule, 'mpp_sync_offers');

    // create/update table
    global $wpdb;
    $sql = 'CREATE TABLE '.$wpdb->prefix.self::tb_backups.' (
      id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
      creation_date BIGINT(20) unsigned NOT NULL,
      module VARCHAR(255) NOT NULL,
      filename VARCHAR(255) NOT NULL,
      PRIMARY KEY  (id)
    );';

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // run activation method for each module
    foreach($this->modules as $module)
      $module->activation();
  }

  // on deactiavation
  public function deactivation()
  {
    wp_clear_scheduled_hook('mpp_sync_offers');
  }

  // on uninstallation
  static function uninstall()
  {
    // remove options
    delete_option(__class__.'_boxes');
    delete_option(__class__.'_settings');
    delete_option(__class__.'_config_notice_dismissed');

    // remove table
    global $wpdb;
    $wpdb->query("DROP TABLE ".$wpdb->prefix.self::tb_backups);
  }

  // add options page
  public function admin_menu()
  {
    add_options_page(__('Microstock Photo', self::ld), __('Microstock Photo', self::ld), 'manage_options', __class__, array(&$this, 'options_page'));
    add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'filter_plugin_actions'), 10, 2);
  }

  // extend menu on the plugin listing page
  public function filter_plugin_actions($l, $file)
  {
    $settings_link = '<a href="options-general.php?page='.__class__.'">'.__('Settings').'</a>';
    array_unshift($l, $settings_link);
    return $l;
  }

  // enqueue scripts and styles in the admin
  public function admin_enqueue_scripts($hook)
  {
    // our options page
    if ($hook == 'settings_page_MicrostockPhotoPlugin')
    {
      wp_enqueue_style(__class__.'_styles', $this->_url.'/admin/options.css', array(), self::version, 'all');

      wp_enqueue_script('jquery');
      wp_enqueue_script(__class__, $this->_url.'/admin/options.js', array('jquery'), self::version);


      // create list of modules for javascript
      $modules = array();
      foreach($this->modules as $module)
        $modules[] = $module->getName();

      wp_localize_script(__class__, __class__, array(
        'ajax_url' => admin_url('admin-ajax.php?action='.__class__),
        'modules' => $modules,
        'text' => array(
          'ajax_error' => __('Cannot send ajax request.', self::ld),
          'backup_error' => __('Unable to generate a backup', self::ld),
          'backup_nofiles' => __('There are not files from which to create a backup.', self::ld)
        )
      ));
    }
    else
    if ($hook == 'media-upload-popup')
    {
      $tab = isset($_GET['tab'])?$_GET['tab']:false;

      // insert only on tab if it's our module tab
      if (isset($this->modules[$tab]))
      {
        wp_enqueue_style(__class__.'_tab', $this->_url.'/admin/tab.css', array(), self::version, 'all');
        wp_enqueue_style(__class__.'_tab_stickytooltips', $this->_url.'/3rdparty/stickytooltip/stickytooltip.css', array(), self::version, 'all');

        wp_enqueue_script('jquery');
        wp_enqueue_script(__class__.'_tab', $this->_url.'/admin/tab.js', array('jquery'), self::version);
        wp_enqueue_script(__class__.'_tab_stickytooltips', $this->_url.'/3rdparty/stickytooltip/stickytooltip.js', array('jquery'), self::version);

        wp_localize_script(__class__.'_tab', __class__, array(
          'ajax_url' => admin_url('admin-ajax.php?action='.__class__),
          'options_url' => admin_url('options-general.php?page=MicrostockPhotoPlugin'),
          'module' => $tab,
          'module_register_link' => $this->modules[$tab]->getRegisterLink(),
          'module_detail_offer' => $this->modules[$tab]->getDetailOffer(),
          'text' => array(
            'ajax_error' => __('Cannot send ajax request.', self::ld),
            'buy' => __('Buy', self::ld),
            'confirm' => __('Confirm', self::ld),
            'please_login' => __('Please login', self::ld),
            'license_agreement' => __('License agreement', self::ld),
            'error_purchase' => __('Error occured during purchase process.', self::ld),
            'daily' => __('Daily', self::ld),
            'monthly' => __('Monthly', self::ld)
          )
        ));
      }
    }
    else
    {
      wp_enqueue_script('jquery');
      wp_enqueue_script(__class__.'_tab_media', $this->_url.'/admin/tab_media.js', array('jquery'), self::version);

      $modules = array();
      foreach($this->modules as $module)
      {
        $settings = $module->getSettings();
        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'])

        $modules[] = array(
          'name' => $module->getName(),
          'title' => $module->getTitle(),
          'src' => admin_url('media-upload.php?chromeless=1&tab='.$module->getName()),
          'logo' => $module->getLogo(),
          'icon' => $module->getIcon(),
          'new' => $module->isNew()
        );
      }

      wp_localize_script(__class__.'_tab_media', __class__, array(
        'ajax_url' => admin_url('admin-ajax.php?action='.__class__),
        'modules' => $modules,
        'text' => array(
          'ajax_error' => __('Cannot send ajax request.', self::ld),
          'new' => __('NEW', self::ld)
        )
      ));

      // add aviary editor - footer
      wp_enqueue_script(__class__.'_aviary_feather', 'https://dme0ih8comzn4.cloudfront.net/js/feather.js', array(), self::version, true);
      wp_enqueue_script(__class__.'_aviary_script', $this->_url.'/admin/aviary_script.js', array(), self::version, true);
      wp_localize_script(__class__.'_aviary_script', __class__.'_aviary_script', array(
        'ajax_url' => admin_url('admin-ajax.php?action='.__class__.'_aviary'),
        'api_key' => self::aviary_api_key
      ));

      wp_enqueue_style(__class__.'_aviary_script', $this->_url.'/admin/aviary_script.css', array(), self::version, 'all');

    }
  }

  public function options_page()
  {
    $options_url = admin_url('options-general.php?page='.__class__);

    // save dismiss option for config notice
    if (isset($_GET['dismiss']) && $_GET['dismiss'])
      update_option(__class__.'_config_notice_dismissed', true);

    // get settings
    $boxes = get_option(__class__.'_boxes', array());
    $settings = get_option(__class__.'_settings', array());


    $sync_offers = isset($settings['mpp_sync_offers'])?$settings['mpp_sync_offers']:$this->default_settings['mpp_sync_offers'];

    // get saved offers
    if ($sync_offers)
      $offers = get_option(__class__.'_offers', false);
    else
      $offers = false;

    $hidden_offers = get_option(__class__.'_hidden_offers', array());
    if (!is_array($hidden_offers)) $hidden_offers = array();

    require_once $this->_path.'/admin/top.php';
    require_once $this->_path.'/admin/options.php';
  }


  public function print_media_templates()
  {
    require_once $this->_path.'/admin/tab_media.php';
  }

  protected function getValidFilename($filename, $ext)
  {
    if (is_dir($filenme)) return $filename;

    $tempname = $filename;
    $c = 0;
    while(file_exists($tempname))
    {
      $c++;
      $tempname = str_replace('.'.$ext, '-'.$c.'.'.$ext, $filename);
    }
    return $tempname;
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
      CURLOPT_SSL_VERIFYPEER => false
    ));
    curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    fclose($file);
    return $info['http_code'] == '200'?$info:false;
  }

  // handle aviary post action
  public function ajax_aviary()
  {
    if (!isset($_REQUEST['postdata']) || !$_REQUEST['postdata'] ||
        !isset($_REQUEST['url']) || !$_REQUEST['url'])
      exit;

    if (!$data = get_transient('mpp_'.$_REQUEST['postdata']))
      exit;

    global $wpdb;
    $orig_id = $data[0];

    // get original data about attachment
    $r = $wpdb->get_row("
      SELECT p.post_title, p.post_excerpt, p.post_parent
      FROM ".$wpdb->prefix."posts as p
      WHERE p.ID = ".(int)$orig_id
    , ARRAY_A);

    // if attachment doesn't exist
    if (!$r)
    {
      delete_transient('mpp_'.$_REQUEST['postdata']);
      return;
    }

    $upload_dir = wp_upload_dir();
    $orig_data = wp_get_attachment_metadata($orig_id);

    $ext = pathinfo($orig_data['file'], PATHINFO_EXTENSION);
    $filename = $this->getValidFilename($upload_dir['basedir'].'/'.$orig_data['file'], $ext);

    // download image from aviary
    if (!$this->downloadFile($_REQUEST['url'], $filename))
    {
      delete_transient('mpp_'.$_REQUEST['postdata']);
      return;
    }

    // insert a new attachment
    $wp_filetype = wp_check_filetype(basename($filename), null);
    $attachment = array(
      'guid' => $upload_dir['url'] . '/' . basename($filename),
      'post_mime_type' => $wp_filetype['type'],
      'post_title' => $r['post_title'],
      'post_content' => '',
      'post_excerpt' => $r['post_excerpt'],
      'post_status' => 'inherit'
    );

    $attach_id = wp_insert_attachment($attachment, $filename, $r['post_parent']);

    require_once(ABSPATH . 'wp-admin/includes/image.php');
    $attach_data = wp_generate_attachment_metadata($attach_id, $filename);

    wp_update_attachment_metadata($attach_id, $attach_data);

    // update attachment image alt text
    update_metadata('post', $attach_id, '_wp_attachment_image_alt', get_metadata('post', $orig_id, '_wp_attachment_image_alt', true));

    // update agency info about image if available
    $mpp_data = get_metadata('post', $orig_id, '_mpp_image_data', true);
    if ($mpp_data)
    {
      $mpp_data['aviary_edited'] = true;
      update_metadata('post', $attach_id, '_mpp_image_data', $mpp_data);
    }

    // update transient data
    $data[1] = $attach_id;
    set_transient('mpp_'.$_REQUEST['postdata'], $data, 3600);

    exit;
  }

  // handle ajax actions
  public function ajax_admin()
  {
    header("Content-Type: application/json");
    if (!isset($_POST['a'])) exit();

    // get module if exists
    $module = isset($_POST['module'])?$_POST['module']:false;
    if ($module && isset($this->modules[$module]))
      $module = $this->modules[$module];

    switch($_POST['a'])
    {
      // dismiss config notice
      case 'dismiss_config_notice':
        update_option(__class__.'_config_notice_dismissed', true);
        echo json_encode(array());
        exit;
        break;

      // save open/close state of boxes
      case 'saveBoxes':
        update_option(__class__.'_boxes', $_POST['boxes']);
        echo json_encode(array('status' => 1));
        break;

      // save module setting
      case 'saveModule':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $data = isset($_POST['data'])?$_POST['data']:array();

        $settings = array();
        foreach($data as $data_item)
          $settings[$data_item['name']] = $data_item['value'];

        if (array_key_exists($module->getName(), $this->modules))
        {
          $customAPI = false;
          if ($checkLogin = $module->login($settings))
          {
            $module->setSettings($settings);
            $userdata = $data = $module->getUserData();
            $module->setData($data);

            // if there is custom API key, check and save it!
            if (isset($settings['mpp_custom_api_key']) && $settings['mpp_custom_api_key'])
            {
              $module->setAPIKey($settings['mpp_custom_api_key']);
              if ($module->login($settings))
                $customAPI = 2;
              else
                $customAPI = 1;

              $settings['customAPI'] = $customAPI;
            }

            if ($data)
              $data = $this->getContent($module->getPath().'/admin/login_data.php', array(
                'data' => $data,
                'settings' => $settings
              ));

            echo json_encode(array('status' => 1, 'customAPI' => $customAPI, 'data' => $data, 'userdata' => $userdata));
          }
          else
          {
            $module->setData(false);
            echo json_encode(array('status' => 2));
          }

          // set user's affiliate iD
          $settings['mpp_affiliate_id_user'] = $checkLogin;

          $settings['checkLogin'] = $checkLogin != false;
          $settings['customAPI'] = $customAPI;
          $module->setSettings($settings);
        }
        break;


      // save main settings
      case 'saveSettings':
        $data = isset($_POST['data'])?$_POST['data']:array();

        $settings = array();
        foreach($data as $data_item)
          $settings[$data_item['name']] = $data_item['value'];

        update_option(__class__.'_settings', $settings);

        echo json_encode(array('status' => 1));
        break;

      // login and get user info for options page
      case 'getUserDataOptions':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $settings = $module->getSettings();

        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'])
        {
          if (!$module->isLogged())
            $module->login($settings);

          $data = $module->getUserData();
          $module->setData($data);

          $d = $this->getContent($module->getPath().'/admin/login_data.php', array(
            'data' => $data,
            'settings' => $settings
          ));

          echo json_encode(array(
            'status' => 1,
            'isLogged' => $module->isLogged()?1:0,
            'data' => $d
          ));
        }
        else
          echo json_encode(array('status' => 0));
        break;


      // login and get user info
      case 'getUserData':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $settings = $module->getSettings();

        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'])
        {
          if (!$module->isLogged())
            $module->login($settings);

          $data = $module->getUserData();
          $module->setData($data);

          $d = $this->getContent($this->_path.'/admin/tab_status.php', array(
            'data' => $data,
            'settings' => $settings,
            'module' => $module
          ));

          echo json_encode(array(
            'status' => 1,
            'isLogged' => $module->isLogged()?1:0,
            'data' => $d
          ));
        }
        else
          echo json_encode(array('status' => 0));
        break;

      // search for images
      case 'search':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $settings = $module->getSettings();

        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'])
        {
          $search = isset($_POST['search'])?$_POST['search']:'';
          $filters = isset($_POST['filters'])?$_POST['filters']:false;
          $sort = isset($_POST['sort'])?$_POST['sort']:false;
          $page = isset($_POST['page'])?$_POST['page']:1;
          if ($page < 1) $page = 1;

          $per_page = 24; // TODO: set as an option?

          $r = $module->search(array(
            'text' => $search,
            'page' => $page,
            'limit' => $per_page,
            'filters' => $filters,
            'sort' => $sort
          ));

          // get main settings of the plugin
          $main_settings = get_option(__class__.'_settings', $this->default_settings);
          $sync_offers = isset($main_settings['mpp_sync_offers'])?$main_settings['mpp_sync_offers']:$this->default_settings['mpp_sync_offers'];

          // get offers if are enabled
          if ($sync_offers)
            $offers = get_option(__class__.'_offers', false);
          else
            $offers = false;

          $hidden_offers = get_option(__class__.'_hidden_offers', array());
          if (!is_array($hidden_offers)) $hidden_offers = array();

          if ($r)
            $images = $this->getContent($this->_path.'/admin/tab_images.php', array(
              'result' => $r['images'],
              'offers' => $offers,
              'hidden_offers' => $hidden_offers,
              'module' => $module->getName()
            ));
          else
            $images = '';

          // paging
          $n_items = is_array($r) && isset($r['nb'])?$r['nb']:0;
          $max_page = ceil($n_items / $per_page);
          if ($page > $max_page) $page = 1;

          $paging = $this->getContent($this->_path.'/admin/tab_paging.php', array(
            'page' => $page,
            'max_page' => $max_page
          ));

          echo json_encode(array(
            'status' => 1,
            'images' => $images,
            'paging' => $paging,
            'nb_images' => sprintf(__("We found %s images.", self::ld), number_format($n_items, 0, '', ','))
          ));
        }
        else
          echo json_encode(array('status' => 0));

        break;


      // get popular images
      case 'getPopularImages':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $settings = $module->getSettings();

        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'])
        {
          $r = $module->getPopularImages();

          if ($r)
          {
            $images = $this->getContent($this->_path.'/admin/tab_popular_images.php', array(
              'result' => $r['images'],
              'module' => $module->getName()
            ));

            echo json_encode(array('status' => 1, 'images' => $images));
          }
          else
            echo json_encode(array('status' => 0));
        }
        else
          echo json_encode(array('status' => 0));
        break;

      // get image detail
      case 'detail':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $settings = $module->getSettings();

        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'] &&
            isset($_POST['id']) && $_POST['id'])
        {
          $image = $module->detail($_POST['id']);

          if ($image)
            $data = $this->getContent($this->_path.'/admin/tab_detail.php', array(
              'image' => $image,
            ));
          else
            $data = '';

          echo json_encode(array(
            'status' => 1,
            'data' => $data
          ));
        }
        else
          echo json_encode(array('status' => 0));

        break;

      // buy item
      case 'buy':
        if (!$module)
        {
          echo json_encode(array('status' => 0, 'message' => __('Invalid module.', self::ld)));
          exit;
        }

        $settings = $module->getSettings();

        if (isset($settings['mpp_enabled']) && $settings['mpp_enabled'] &&
            isset($_POST['id']) && $_POST['id'] && isset($_POST['post_id']) && $_POST['post_id'] &&
            isset($_POST['title']) && $_POST['title'] && isset($_POST['license']) &&
            isset($_POST['author']))
        {
          $id = $_POST['id'];
          $post_id = $_POST['post_id'];
          $title = $_POST['title'];
          $search = isset($_POST['search'])?$_POST['search']:false;
          $license = $_POST['license'];
          $author = $_POST['author'];
          $upload_dir = wp_upload_dir();
          $license_type = isset($_POST['license_type'])?$_POST['license_type']:false;
          $license_agreed = isset($_POST['license_agreed'])?$_POST['license_agreed']:false;
          $image_page = isset($_POST['image_page'])?$_POST['image_page']:false;

          // buy via custom API key if daily subscription is active and custom API key is valid
          $mdata = $module->getData();
          if (isset($mdata['subscription']) && $mdata['subscription']['type'] == 1 &&
              isset($settings['mpp_custom_api_key']) && $settings['mpp_custom_api_key'])
          {
            $module->setAPIKey($settings['mpp_custom_api_key']);
          }

          if (!$module->isLogged())
            $module->login($settings);

          // if is necessary to agree
          if (!$license_agreed)
            $license_text = $module->license($license_type);
          else
            $license_text = false;

          if ($license_text && !$license_agreed)
          {
            echo json_encode(array(
              'status' => 3,
              'license_text' => $license_text
            ));
            exit;
          }

          // get main settings of the plugin
          $main_settings = get_option(__class__.'_settings', $this->default_settings);

          $testmode = isset($main_settings['mpp_test_mode'])?$main_settings['mpp_test_mode']:$this->default_settings['mpp_test_mode'];

          $r = $module->buy(array(
            'id' => $id,
            'path' => $upload_dir['path'],
            'title' => $search?$search:$title,
            'license' => $license
          ), $testmode);

          if ($r && !is_array($r))
          {
            $wp_filetype = wp_check_filetype(basename($r), null);

            // create image caption
            if ($author)
              $copyright = $module->getCopyright($author);
            else
              $copyright = '';


            switch($main_settings['mpp_image_caption'])
            {
              case 0:
                $caption = $title;
                break;
              case 1:
                $caption = isset($main_settings['mpp_image_caption_custom'])?$main_settings['mpp_image_caption_custom']:$title;
                break;
              case 2:
                $caption = isset($main_settings['mpp_image_caption_custom_copyright'])?$main_settings['mpp_image_caption_custom_copyright'].($copyright?'. '.$copyright:''):$title.($copyright?'. '.$copyright:'');
                break;
              case 3:
                $caption = $copyright;
                break;
              default:
                $caption = '';
            }


            $attachment = array(
              'guid' => $upload_dir['url'] . '/' . basename($r),
              'post_mime_type' => $wp_filetype['type'],
              'post_title' => $title,
              'post_content' => '',
              'post_excerpt' => $caption,
              'post_status' => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $r, $post_id);

            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata($attach_id, $r);

            wp_update_attachment_metadata($attach_id, $attach_data);

            // add alternative text to image
            $alt_text = isset($main_settings['mpp_alt_text'])?$main_settings['mpp_alt_text']:$this->default_settings['mpp_alt_text'];

            switch($alt_text)
            {
              case 0:
                $alt_text = $search?$search:$title;
                break;
              case 1:
                $alt_text = $title;
                break;
              default:
                $alt_text = false;
            }

            if ($alt_text)
              update_metadata('post', $attach_id, '_wp_attachment_image_alt', $alt_text);

            // add agency info about image
            update_metadata('post', $attach_id, '_mpp_image_data', array(
              'id' => $id,
              'module' => $module->getName(),
              'name' => $module->getTitle(),
              'page' => $image_page,
              'affiliate_link' => isset($settings['mpp_affiliate']) && $settings['mpp_affiliate']?1:0,
              'affiliate_window' => 0
            ));

            echo json_encode(array('status' => 1, 'id' => $attach_id));
          }
          else
            echo json_encode(array('status' => 2, 'message' => (is_array($r)?$r['message']:false)));
        }
        else
          echo json_encode(array('status' => 0));

        break;

      // hide offer
      case 'hide_offer':
        $id = isset($_POST['id'])?$_POST['id']:0;

        // save hidden status of the offer
        if ($id)
        {
          $hidden_offers = get_option(__class__.'_hidden_offers', array());
          if (!is_array($hidden_offers)) $hidden_offers = array();

          $hidden_offers[$id] = true;
          update_option(__class__.'_hidden_offers', $hidden_offers);
        }

        echo json_encode(array('status' => 1));
        break;

      // get token for edited image
      case 'getToken':
        $id = isset($_POST['id'])?$_POST['id']:false;

        if ($id)
        {
          $token = md5(uniqid($id, true).$id);
          set_transient('mpp_'.$token, array($id, false), 3600);

          echo json_encode(array('token' => $token));
        }
        else
          echo json_encode(array());
        break;

      // check if image is ready via token
      case 'checkToken':
        $token = isset($_POST['token'])?$_POST['token']:false;
        if ($token)
        {
          if (!$data = get_transient('mpp_'.$token))
            echo json_encode(array('status' => -1));
          else
          {
            if ($data[1])
              delete_transient('mpp_'.$token);

            echo json_encode(array('status' => $data[1]?1:0, 'id' => $data[1]));
          }
        }
        else
          echo json_encode(array('status' => -1));
        break;


      // download backup feature
      case 'downloadBackup':
        if (!$module)
        {
          echo json_encode(array('status' => 0));
          exit;
        }

        // get list of all purchased files
        global $wpdb;
        $upload_dir = wp_upload_dir();

        $r = $wpdb->get_results("
          SELECT pm1.meta_value AS mv1, pm2.meta_value as mv2
          FROM ".$wpdb->prefix."postmeta AS pm1,
               ".$wpdb->prefix."postmeta AS pm2
          WHERE pm1.meta_key = '_mpp_image_data' AND
                pm1.post_id = pm2.post_id AND
                pm2.meta_key = '_wp_attached_file'
        ", ARRAY_A);

        $images = array();
        foreach($r as $img)
        {
          $ag_data = unserialize($img['mv1']);
          if ($ag_data['module'] != $module->getName() || isset($ag_data['aviary_edited']))
            continue;

          $images[] = array(
            'from' => $upload_dir['basedir'].'/'.$img['mv2'],
            'to' => $img['mv2']
          );
        }

        // if there are not any files
        if (!count($images))
        {
          echo json_encode(array('status' => 2));
          exit;
        }

        // get name for zip file
        $zipfile = 'MPP_'.$module->getTitle().'_'.date('Y_m_d_H_i_s', time()).'.zip';
        $zipfile_dir = $upload_dir['basedir'].'/'.$zipfile;
        $zipfile_url = $upload_dir['baseurl'].'/'.$zipfile;

        // try to use built-in ZipArchive class
        if (class_exists('ZipArchive'))
        {
          $zip = new ZipArchive();
          @unlink($zipfile_dir);
          if ($zip->open($zipfile_dir, ZipArchive::CREATE) === true)
          {
            foreach($images as $image)
              $zip->addFile($image['from'], $image['to']);

            $zip->close();


            // create a record in databse about the new backup
            global $wpdb;
            $wpdb->query("
              INSERT INTO ".$wpdb->prefix.self::tb_backups."
              SET creation_date = ".(int)time().",
                  module = '".$wpdb->escape($module->getName())."',
                  filename = '".$wpdb->escape($zipfile)."'
            ");

            $c = $this->getContent($this->_path.'/admin/options_backup_list.php', array(
              'module' => $module
            ));

            echo json_encode(array('status' => 1, 'downloadUrl' => $zipfile_url, 'backups' => $c));
          }
          else
            echo json_encode(array('status' => 0));
        }
        else
          echo json_encode(array('status' => 0));
        break;
    }

    exit();
  }

  protected function getContent($file, $vals = array())
  {
    extract($vals);
    ob_start();
    require_once $file;
    $c = ob_get_contents();
    ob_end_clean();
    return $c;
  }

  // sync offers with the server
  public function sync_offers()
  {
    $r = wp_remote_get(self::sync_offers_url, array(
      'timeout' => 15
    ));

    if(!is_wp_error($r))
      update_option(__class__.'_offers', json_decode($r['body']));
  }

  // helper strip function
  static function strip($t)
  {
    return htmlentities($t, ENT_COMPAT, 'UTF-8');
  }
}

new MicrostockPhotoPlugin();