jQuery(document).ready(function($)
{
  var _self = this;
  var blank_keys = [9, 13, 16, 37, 38, 39, 40];

  // add settings functionality to metaboxes which belong to modules
  $.each(MicrostockPhotoPlugin.modules, function(i, module)
  {
    var $box = $('#mpp_box_' + module);
    var $save = $('#mpp_save_' + module);
    var $loader = $('#mpp_save_loader_' + module);
    var $form = $('#mpp_form_' + module);
    var $login_fields = $('input[name=mpp_login],input[name=mpp_password]', $form);
    var $login_info = $('.mpp_login_info', $box);
    var $login_info_text = $('.mpp_login_info_text', $box);
    var $logout_button = $('.mpp_logout_button', $box);
    var $show_backup_options = $('.mpp_show_backup_options', $box);
    var $hide_backup_options = $('.mpp_hide_backup_options', $box);
    var $backup_options = $('.mpp_backup_options', $box);
    var $backup_list = $('.mpp_backup_list', $box);
    var $download_backup = $('input[name=mpp_download_backup]', $box);
    var $download_backup_loader = $('.mpp_download_backup_loader', $box);
    var $affiliate = $('input[name=mpp_affiliate]', $box);
    var $affiliate_input = $('.mpp_affiliate_input', $box);
    var $settings_saved = $('.mpp_settings_saved', $box);
    var $custom_api_key = $('input[name=mpp_custom_api_key]', $box);
    var $custom_api_key_tr = $('.mpp_custom_api_key_tr', $box);
    var $offer_signup = $('.mpp_offer_signup', $box);

    // enable save button on change
    $('input', $box).bind('change keyup', function(e)
    {
      if ($.inArray(e.keyCode, blank_keys) !== -1)
        return true;

      $save.attr('disabled', false);
    });

    $login_fields.bind('change keyup', function(e)
    {
      if ($.inArray(e.keyCode, blank_keys) !== -1)
        return true;

      $login_fields.removeClass('mpp_login_incorrect').removeClass('mpp_login_correct');
    });


    $custom_api_key.bind('change keyup', function(e)
    {
      if ($.inArray(e.keyCode, blank_keys) !== -1)
        return true;

      $(this).removeClass('mpp_login_incorrect').removeClass('mpp_login_correct');
      $save.attr('disabled', false);
    });


    // update user's data via ajax
    $loader.show();
    $.post(MicrostockPhotoPlugin.ajax_url, {
      'a': 'getUserDataOptions',
      'module': module
    }, function(r)
    {
      $loader.hide();

      if (r.status == 1)
        $login_info_text.html(r.data);
    }).error(function()
    {
      $loader.hide();
    });

    // save settings
    $save.bind('click', function()
    {
      $save.attr('disabled', true);
      $loader.show();
      $.post(MicrostockPhotoPlugin.ajax_url, {
        'a': 'saveModule',
        'module': module,
        'data': $form.serializeArray()
      }, function(r)
      {
        $loader.hide();
        $settings_saved.show();

        window.setTimeout(function()
        {
          $settings_saved.fadeOut(400);
        }, 4000);

        if (r.status == 0)
          alert(r.message);
        else
        if (r.status == 1)
        {
          $login_fields.removeClass('mpp_login_incorrect');
          $login_fields.addClass('mpp_login_correct');

          // show some info about logged user
          if (r.data)
          {
            $login_info_text.html(r.data);
            $login_info.fadeIn(300);
            $offer_signup.fadeOut(300);

            if (r.userdata.hasOwnProperty('subscription') && r.userdata.subscription.hasOwnProperty('type'))
            {
              if (r.userdata.subscription.type == 1)
                $custom_api_key_tr.fadeIn(300);
              else
                $custom_api_key_tr.fadeOut(300);
            }
            else
             $custom_api_key_tr.fadeOut(300);

            if (r.customAPI == 2)
              $custom_api_key.addClass('mpp_login_correct').removeClass('mpp_login_incorrect');
            else
            if (r.customAPI == 1)
              $custom_api_key.addClass('mpp_login_incorrect').removeClass('mpp_login_correct');
            else
              $custom_api_key.removeClass('mpp_login_incorrect').removeClass('mpp_login_correct');
          }
          else
          {
            $custom_api_key_tr.fadeOut(300);
            $login_info_text.html('');
            $login_info.fadeOut(300);
            $offer_signup.fadeIn(300);
          }
        }
        else
        if (r.status == 2)
        {
          $login_fields.removeClass('mpp_login_correct');
          $login_fields.addClass('mpp_login_incorrect');
          $login_info.fadeOut(300);
          $custom_api_key_tr.fadeOut(300);
          $offer_signup.fadeIn(300);
        }

      }).error(function()
      {
        $loader.hide();
        alert(MicrostockPhotoPlugin.text.ajax_error);
      });
    });

    // logout button
    $logout_button.bind('click', function(e)
    {
      $('input[name=mpp_custom_api_key]', $box).val('');
      $login_fields.val('');
      $save.trigger('click');
    });


    // show/hide backup options
    $show_backup_options.bind('click', function()
    {
      $show_backup_options.hide();
      $hide_backup_options.show();
      $backup_options.fadeIn(400);
    });

    $hide_backup_options.bind('click', function()
    {
      $hide_backup_options.hide();
      $show_backup_options.show();
      $backup_options.fadeOut(400);
    });


    // download backup
    $download_backup.bind('click', function()
    {
      var $t = $(this);

      $t.attr('disabled', true);
      $download_backup_loader.show();

      // send request to the server
      $.post(MicrostockPhotoPlugin.ajax_url, {
        'a': 'downloadBackup',
        'module': module
      }, function(r)
      {
        $t.attr('disabled', false);
        $download_backup_loader.hide();

        if (r.status == 1)
        {
          $backup_list.html(r.backups);
          location.href = r.downloadUrl;
        }
        else
        if (r.status == 2)
          alert(MicrostockPhotoPlugin.text.backup_nofiles);
        else
          alert(MicrostockPhotoPlugin.text.backup_error);

      }).error(function()
      {
        $t.attr('disabled', false);
        $db_loader.hide();
        alert(MicrostockPhotoPlugin.text.ajax_error);
      });
    });

    // show/hide affiliate alternative id input
    $affiliate.bind('change', function()
    {
      if ($(this).attr('checked'))
        $affiliate_input.fadeIn(400);
      else
        $affiliate_input.fadeOut(400);
    });

    // hide a offer
    $('.mpp_offer_close').bind('click', function()
    {
      var $t = $(this);

      $.post(MicrostockPhotoPlugin.ajax_url, { a: 'hide_offer', id: $t.attr('data-id') }, function(r)
      {
        // nothing to do
      });

      $t.parent().fadeOut(400, function()
      {
        $(this).remove()
      });
    });

  });


  // save main settings
  var $box = $('#mpp_box_settings');
  var $save = $('#mpp_save_settings');
  var $loader = $('#mpp_save_loader_settings');
  var $form = $('#mpp_form_settings');
  var $settings_saved = $('.mpp_settings_saved', $box);

  $('input,select', $box).bind('change keyup', function(e)
  {
    if ($.inArray(e.keyCode, blank_keys) !== -1)
      return true;

    $save.attr('disabled', false);
  });

  $save.bind('click', function()
  {
    $save.attr('disabled', true);
    $loader.show();
    $.post(MicrostockPhotoPlugin.ajax_url, {
      'a': 'saveSettings',
      'data': $form.serializeArray()
    }, function(r)
    {
      $loader.hide();
      $settings_saved.show();

      window.setTimeout(function()
      {
        $settings_saved.fadeOut(400);
      }, 4000);

    }).error(function()
    {
      $loader.hide();
      alert(MicrostockPhotoPlugin.text.ajax_error);
    });
  });


  // save states of boxes
  this.saveBoxes = function()
  {
    var bs = new Object();
    $('#poststuff').find('.postbox').each(function()
    {
      bs[$(this).attr('id')] = $(this).hasClass('mpp_closed')?1:0;
    });

    $.post(MicrostockPhotoPlugin.ajax_url,
      {
        a: 'saveBoxes',
        boxes: bs
      }, function(r) { }
    );
  }

  // show/hide functionality for metaboxes
  $('.handlediv').bind('click', function()
  {
    var box = $(this).parent();
    if (box.hasClass('mpp_closed'))
    {
      box.find('.inside').hide();
      box.removeClass('mpp_closed');
      box.find('.inside').fadeIn(400, function()
      {
        box.removeClass('closed');
      });
      _self.saveBoxes();
    }
    else
    {
      box.find('.inside').fadeOut(400, function()
      {
        box.addClass('mpp_closed closed');
        _self.saveBoxes();
      });
    }
  });
});
