jQuery(document).ready ($) ->

  # select image in media library
  window.mpp_selectImage = (id, aff_link = false) ->
    m = wp.media.editor.get(wpActiveEditor)
    m.setState('insert')
    m.content.mode('browse')
    m.views._views[".media-frame-content"][0].views._views[""][1].collection.props.set({ignore:(+(new Date()))})

    s = m.state('insert').get('selection')
    s.reset([])

    a = wp.media.attachment(id)
    a.fetch({
      success: (model, response, options) ->
        s.add(a)
    })


  if not wp?.media
    return


  window.mpp_syncSearch =
    sync: true
    search: ''
    filters: ['mpp_search_filter_photo']


  # add stock photo button
  window.add_stock_photo = false
  $('.add-stock-photo').on('click', ->
    window.add_stock_photo = true
    $('.insert-media').trigger('click')
  )


  # controller
  wp.media.controller.MPP = wp.media.controller.State.extend(
    initialize: ->
      @

    refresh: ->
      @frame.toolbar.get().refresh()

    _renderTitle: (view) ->
      view.$el.html(this.get('headerTitle'))


    _renderMenu: (view) ->
      menuItem = view.get('menuItem')
      title = @get('title')
      priority = @get('priority')

      if not menuItem then menuItem = { html: title }
      if priority then menuItem.priority = priority;

      view.set(@id, menuItem)
  )

  # view
  wp.media.view.MPP = wp.media.View.extend(
    template:  wp.media.template('mpp-content')
    initialize: ->
      @module = @options.module

    render: ->
      @
  )

  # toolbar
  wp.media.view.Toolbar.MPP = wp.media.view.Toolbar.extend(

    initialize: ->
      wp.media.view.Toolbar.prototype.initialize.apply(@, arguments)

    refresh: ->
      wp.media.view.Toolbar.prototype.refresh.apply(@, arguments)

      state = @controller.state()

      for module in MicrostockPhotoPlugin.modules
        if state.id == 'mpp-'+module.name
          @secondary.$el.html(wp.media.template('mpp-loader'))
          window.mpp_loader = @secondary.$el.find('.mpp_loader')

          $img_logo = $('<img />').attr('src', module.logo).css('margin-top', '13px')
          @primary.$el.html($img_logo)

          iframe = @controller.$el.find('.mpp-iframe-' + module.name)
          if iframe.length > 0
            iframe[0].contentWindow.mpp_checkStatus()
      @
  )


  # extend media frame
  MediaFrame = wp.media.view.MediaFrame.Post
  wp.media.view.MediaFrame.Post = MediaFrame.extend(
    initialize: ->

      for module in MicrostockPhotoPlugin.modules
        delete wp.media.view.settings.tabs[module.name]

      MediaFrame.prototype.initialize.apply(@, arguments)

      @on 'deactivate', =>
        for module in MicrostockPhotoPlugin.modules
          if @state().id is 'mpp-'+module.name
            iframe = @$el.find('.mpp-iframe-' + module.name)
            if iframe.length > 0
              window.mpp_syncSearch = iframe[0].contentWindow.mpp_syncSearch()

        @$el.find('.mpp-frame-content').hide()


      @on 'activate', =>
        window.mpp_currentState = @state().id

      @on 'open', =>
        id = 'mpp-MicrostockPhotoPlugin_depositphotos'
        if @states.where({ id: id}).length > 0 and window.add_stock_photo
          window.add_stock_photo = false
          @setState(id)


      # add modules
      for module in MicrostockPhotoPlugin.modules
        @states.remove(
          @states.where({ id: 'iframe:' + module.name })
        )

        @states.add([
          new wp.media.controller.MPP(
            id: 'mpp-' + module.name
            menu: 'default'
            content: 'mpp-' + module.name
            toolbar: 'mpp-' + module.name
            title: '<img src="' + module.icon + '" width="16" height="16" style="margin-right: 3px; position: relative; top: 2px;" /> ' + module.title + (if module.new then '<span style="font-size: 8px;color: #ff0000;top: -5px;position: relative;font-weight: bold;margin-left: 4px;">' + MicrostockPhotoPlugin.text.new + '</span>' else '')
            headerTitle: '<img src="' + module.icon + '" width="16" height="16" style="margin-right: 5px;" /> ' + module.title + (if module.new then '<span style="font-size: 11px;color: #ff0000;top: -7px;position: relative;font-weight: bold;margin-left: 4px;">' + MicrostockPhotoPlugin.text.new + '</span>' else '')
            priority: 200
            type: 'link'
          )
        ])

        @on('content:render:mpp-' + module.name,  _.bind(@moduleContent, @, module))
        @on('toolbar:create:mpp-' + module.name, @createToolbar, @)


    moduleContent: (module) ->
      @$el.addClass('hide-router')

      if not @$el.find('.mpp-frame-content-'+module.name).length
        iframe = '<iframe src="'+module.src+'" class="mpp-iframe-'+module.name+'" width="100%" height="100%"></iframe>'
        def_content = @$el.find('.media-frame-content')
        content = $('<div class="mpp-frame-content mpp-frame-content-'+module.name+'">'+iframe+'</div>')
        content.css(
          'position': def_content.css('position')
          'top': def_content.css('top')
          'left': def_content.css('left')
          'bottom': def_content.css('bottom')
          'right': def_content.css('right')
          'margin': def_content.css('margin')
        )
        @$el.append(content)
      else
        @$el.find('.mpp-frame-content-'+module.name).show()

      view = new wp.media.view.MPP(
        controller: @
        model: @state().props
        className: 'MPP media-' + module.name
        module: module
      )

      @content.set(view)


    createToolbar: (toolbar) ->
      toolbar.view = new wp.media.view.Toolbar.MPP(
        controller: @
      )
  )

