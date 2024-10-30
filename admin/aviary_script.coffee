jQuery(document).ready ($) ->

  _vals = MicrostockPhotoPlugin_aviary_script

  featherEditor = new Aviary.Feather(
    apiKey: _vals.api_key
    apiVersion: 3
    theme: 'light'
    postUrl: _vals.ajax_url

    onLoad: ->
      $('input[name=mpp_edit_button]').live 'click', ->
        $t = $(@)
        $loader = $t.parent().find('.mpp_edit_button_loader')

        # show loader
        $loader.css('display', 'inline')

        # get data token
        $.post(MicrostockPhotoPlugin.ajax_url, {
          'a': 'getToken'
          'id': $t.attr('data-id')
        }, (r) ->
          $loader.hide()

          if not r.token?
            return false

          token = r.token

          # remove if there is another img element with this ID
          $mp = $('#mpp_edit_image')
          if $mp.length > 0 then $mp.remove()

          # create a new image with correct URL
          url = $t.attr('data-url')
          img = $('<img id="mpp_edit_image" style="display: none;" />')
          img.attr('src', url)
          img.appendTo('body')

          # launch editor
          featherEditor.launch(
            image: 'mpp_edit_image'
            url: url
            postData: token
            onSave: (imageID, newURL) ->
              $loader.css('display', 'inline')

              # check periodically if image is ready
              checkToken = ->
                $.post(MicrostockPhotoPlugin.ajax_url, {
                  'a': 'checkToken'
                  'token': token
                }, (r) ->
                  if r.status is -1
                    $loader.hide()
                    return false
                  else if r.status is 0
                    window.setTimeout(->
                      checkToken()
                    , 1000)
                  else if r.status is 1 and r.id?
                    $loader.hide()
                    window.mpp_selectImage(r.id)

                ).error(->
                  $loader.hide()
                  alert(MicrostockPhotoPlugin.text.ajax_error)
                )

              checkToken()
          )

        ).error(->
          $loader.hide()
          alert(MicrostockPhotoPlugin.text.ajax_error)
        )
  )

