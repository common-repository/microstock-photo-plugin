jQuery(document).ready ($) ->
  # parent document
  pd = parent.document

  # last scroll position
  scroll_position = 0

  # controls
  $search_input = $('input[name=mpp_search_input]')
  $search_input_copy = $('input[name=mpp_search_input_copy]')
  $search_button = $('input[name=mpp_search_button]')
  $search_sync = $('input[name=mpp_search_sync]')
  $search_filter = $('.mpp_search_filter_checkbox')
  $search_sort = $('select[name=mpp_sort]')
  $back_button = $('input[name=mpp_button_back]')
  $ui_search = $('.mpp_ui_search')
  $ui_detail = $('.mpp_ui_detail')
  $images = $('.mpp_images')
  $paging = $('.mpp_paging')
  $detail = $('.mpp_detail')
  $nb_images = $('.mpp_nb_images')
  $button_license_accept = $('input[name=mpp_license_accept]')
  $button_license_reject = $('input[name=mpp_license_reject]')

  currentStatus = false
  isUserLogged = false


  # sync search if enabled
  mpp_syncSearch1 = ->
    syncSearch = parent.window.mpp_syncSearch
    if syncSearch.sync
      $search_sync.attr('checked', true)

      if syncSearch.search isnt $search_input.val()
        $search_input.val(syncSearch.search)
        $search_input_copy.val(syncSearch.search)
        search(syncSearch.search)

        # sync search filters
        if syncSearch.filters?
          $search_filter.attr('checked', false)
          for filter in syncSearch.filters
            $('#' + filter).attr('checked', true)
    else
      $search_sync.attr('checked', false)


  # get status of credits
  mpp_checkStatus = (refresh = false) ->
    # get status
    if refresh or not currentStatus
      $.post(MicrostockPhotoPlugin.ajax_url, { a: 'getUserData', module: MicrostockPhotoPlugin.module}, (r) ->

        # set current status
        if r.status is 1 and r.data
          isUserLogged = if r.isLogged then true else false
          currentStatus = r.data
        else
          isUserLogged = false
          currentStatus = false

        # modify parent header only if this frame is active
        if parent.mpp_currentState is 'mpp-' + MicrostockPhotoPlugin.module
          $(pd).find('#mpp_status')?.remove()
          if r.status is 1 and r.data
            $(pd).find('.media-frame-title').append(r.data)

        mpp_syncSearch1()
      )
    else
      $(pd).find('#mpp_status')?.remove()
      $(pd).find('.media-frame-title').append(currentStatus)
      mpp_syncSearch1()

  window.mpp_checkStatus = mpp_checkStatus


  mpp_syncSearch = ->
    filters = new Array()
    for filter in $search_filter
      filter = $(filter)
      if filter.attr('checked')
        filters.push(filter.attr('id'))
    {
      sync: $search_sync.is(':checked')
      search: $search_input.val()
      filters: filters
    }

  window.mpp_syncSearch = mpp_syncSearch



  bindPaging = ->
    $('.mpp_page').bind 'click', ->
      search($search_input.val(), $(@).attr('href'))

  buyImage = (license_agreed) ->
    $('.mpp_detail_error_message').text('')
    $.post(MicrostockPhotoPlugin.ajax_url, {
      a: 'buy'
      module: MicrostockPhotoPlugin.module
      id: $('input[name=mpp_buy_id]').val()
      license: $('input[name=mpp_license]:checked').val()
      post_id: parent.wp.media.view.settings.post.id
      title: $('input[name=mpp_buy_title]').val()
      search: $search_input_copy.val()
      license_type: $('input[name=mpp_license_type]').val()
      license_agreed: license_agreed
      author: $('input[name=mpp_buy_author]').val()
      image_page: $('input[name=mpp_image_page]').val()
    }, (r) ->
      if r.status is 1 and r.id?
        window.mpp_checkStatus(true)
        parent.mpp_selectImage(r.id, if r.aff_link? and r.aff_link then r.aff_link else false)
      else if r.status is 3
        $('.mpp_license_text').html(r.license_text)
        tb_show(MicrostockPhotoPlugin.text.license_agreement, '#TB_inline?width=500&modal=true&height=400&inlineId=mpp_license_dialog', false)
      else if r.status is 2
        $('input[name=mpp_license]').attr('disabled', false)
        $('input[name=mpp_buy]').attr('disabled', false).removeClass('button-primary')
          .addClass('button-secondary').val(MicrostockPhotoPlugin.text.buy)
        if r.message
          $('.mpp_detail_error_message').html(r.message)
        else
          $('.mpp_detail_error_message').text(MicrostockPhotoPlugin.text.error_purchase)
    )


  bindLicenseTable = ->
    # if user is not logged then he can't buy something
    if not isUserLogged
      $('input[name=mpp_license]').attr('disabled', true)
      $('input[name=mpp_buy]').attr('disabled', false).attr('data-link', MicrostockPhotoPlugin.options_url).val(MicrostockPhotoPlugin.text.please_login)
      if MicrostockPhotoPlugin.module_register_link
        $('a.mpp_register').show().attr('href', MicrostockPhotoPlugin.module_register_link)

      if MicrostockPhotoPlugin.module_detail_offer
        $('.mpp_offer_detail').html(MicrostockPhotoPlugin.module_detail_offer).show()

    $('input[name=mpp_buy_method]').bind 'change', ->
      $t = $(@)
      if $t.val() < 2
        $('.mpp_license_subscription').hide()
        $('.mpp_license_credits').show()
      else
        $('.mpp_license_credits').hide()
        $('.mpp_license_subscription').show()

    $('.mpp_license_row').bind 'click', ->
      $t = $(@).find('input[name=mpp_license]')
      if not $t.attr('disabled')
        $('input[name=mpp_buy]').attr('disabled', false).removeClass('button-primary')
          .addClass('button-secondary').val(MicrostockPhotoPlugin.text.buy)
        $t.attr('checked', true)

    $('input[name=mpp_license]').bind 'click', ->
      if not $(@).attr('disabled')
        $('input[name=mpp_buy]').attr('disabled', false).removeClass('button-primary')
          .addClass('button-secondary').val(MicrostockPhotoPlugin.text.buy)

    $('input[name=mpp_buy]').bind 'click', ->
      $t = $(@)
      if not $t.attr('disabled') and not $t.attr('data-link')
        if $t.hasClass('button-secondary')
          $(@).removeClass('button-secondary').addClass('button-primary').val(MicrostockPhotoPlugin.text.confirm)
        else
          $('input[name=mpp_license]').attr('disabled', true)
          $(@).attr('disabled', true)
          # buy action
          buyImage(0)
      else
      if $t.attr('data-link')
        top.location.href = $t.attr('data-link')


  $button_license_accept.bind 'click', ->
    tb_remove()
    buyImage(1)

  $button_license_reject.bind 'click', ->
    tb_remove()
    $('input[name=mpp_license]').attr('disabled', false)
    $('input[name=mpp_buy]').attr('disabled', false).removeClass('button-primary')
      .addClass('button-secondary').val(MicrostockPhotoPlugin.text.buy)




  bindImages = ->
    # bind offers
    $('.mpp_offer_close').bind 'click', ->
      $t = $(@)

      $.post(MicrostockPhotoPlugin.ajax_url, {
        a: 'hide_offer'
        id: $t.attr('data-id')
      }, (r) ->
        # nothing todo
      )

      $t.parent().fadeOut(400, ->
        $(@).remove()
      )

    # bind images
    $('.mpp_image_link').bind 'click', ->
      $.post(MicrostockPhotoPlugin.ajax_url, {
        a: 'detail'
        module: MicrostockPhotoPlugin.module
        id: $(@).attr('href')
      }, (r) ->
        # save current scroll position
        scroll_position = $(window).scrollTop()
        $('html,body').scrollTop(0)

        if r.status is 1 and r.data
          $detail.html(r.data)
          bindLicenseTable()
          $ui_search.hide()
          $ui_detail.show()
      )

  # search for images
  search = (text, page = 1) ->
    $search_button.attr('disabled', true)
    $search_input.attr('disabled', true)
    $search_input_copy.val($search_input.val())

    # get search filters
    filters = []
    $search_filter.each((i, e)->
      $e = $(e)
      if $e.is(':checked') then filters.push($e.val())
    )

    $.post(MicrostockPhotoPlugin.ajax_url, {
      a: 'search'
      module: MicrostockPhotoPlugin.module
      search: text
      page: page
      filters: filters
      sort: $search_sort.val()
    }, (r) ->
      $search_button.attr('disabled', false)
      $search_input.attr('disabled', false)

      if r.status is 1 and r.images and r.paging
        $('html,body').scrollTop(0)
        $nb_images.html(r.nb_images)
        $paging.html(r.paging)
        $images.html(r.images)

        # create tooltips
        stickytooltip.init('data-tooltip-mpp', 'mpp_tooltips')

        bindPaging()
        bindImages()

    )


  # bind search
  $search_button.bind 'click', ->
    if not $(@).attr('disabled')
      search($search_input.val())

  $search_input.bind 'keypress', (e) ->
    if not $search_button.attr('disabled')
      code = e.keyCode or e.charCode
      if code is 13
        search($search_input.val())


  #$search_sort.bind 'change', ->
  #  search($search_input.val())

  getPopularImages = ->
    $.post(MicrostockPhotoPlugin.ajax_url, {
      a: 'getPopularImages'
      module: MicrostockPhotoPlugin.module
    }, (r) ->
      if r.status is 1 and r.images
        $('html,body').scrollTop(0)
        $images.html(r.images)

        # create tooltips
        stickytooltip.init('data-tooltip-mpp', 'mpp_tooltips')

        bindImages()
    )

  # show loader when any AJAX request begin
  $(document).ajaxStart ->
    parent.mpp_loader.show()

  # hide loader if there are not any ajax requests
  $(document).ajaxStop ->
    parent.mpp_loader.hide()


  # bind back button
  $back_button.bind 'click', ->
    $ui_detail.hide()
    $ui_search.show()

    # restore last saved scroll position
    $('html,body').scrollTop(scroll_position)


  # init some things
  $search_input.focus()
  window.mpp_checkStatus()

  getPopularImages()
