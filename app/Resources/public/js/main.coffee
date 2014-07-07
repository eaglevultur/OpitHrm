$(document).data 'notes', {}
$.extend true, $(document).data('notes'),
    funcs:
        changeStateDialog: ($dropdown, callback, options = {}) ->
            $.extend options, {
                id: $dropdown.val()
                statusFrom: $dropdown.find('option:nth-child(1)').text().toLowerCase()
                statusTo: $dropdown.find('option:selected').text().toLowerCase()
            }

            throw "Upps, something went wrong." if not options.foreignId?

            $.post Routing.generate('OpitNotesStatusBundle_status_change_show'), options, (data) ->
                $('<div></div>').html(data).dialog
                    width: 500
                    title: '<i class="fa fa-exclamation-triangle"></i> ' + ((if options.type? then options.type.toString() else '') + ' status change').capitalize()
                    buttons:
                        Yes: ->
                            callback $(@).find('form').serialize(), $(document).data('notes').funcs.disableStatusDropdown($dropdown)
                            $(@).dialog 'destroy'
                        No: ->
                            $(document).data('notes').funcs.enableStatusDropdown $dropdown
                            $(@).dialog 'destroy'
                    close: ->
                        $(@).dialog 'destroy'
                        $(document).data('notes').funcs.enableStatusDropdown $dropdown

        initDateInputs: ($container) ->
            $dateInputs = if $container then $container.find('input[type=date]') else $('input[type=date]')
            if not Modernizr.inputtypes.date
                $dateInputs.each ->
                    name = $(@).attr 'name'
                    id = $(@).attr('id')
                    $input = $(@).after '<input type="hidden" name="'+name+'" id="altDate'+id+'" />'
                    if $(@).val()
                        $input.val $.datepicker.formatDate($.datepicker.ISO_8601, new Date($(@).val()))
                    $(@).datepicker()
    
        deleteSingleRequest: (type, self) ->
            $checkbox = self.closest('tr').find(':checkbox')
            $checkbox.prop 'checked', true
            # TODO: Add travel request ID to the dialog body text.
            #$('<div></div>').html("Are you sure you want to delete the travel request \"#{travel-request-id}\"?").dialog
            $('<div></div>').html("Are you sure you want to delete the travel #{ type }?").dialog
                title: 'Travel request removal'
                buttons:
                    Yes: ->
                        $.ajax
                          method: 'POST'
                          url: if type is 'expense' then Routing.generate 'OpitNotesTravelBundle_expense_delete' else Routing.generate 'OpitNotesTravelBundle_travel_delete'
                          data: 'id': self.data 'id'
                        .done (data) ->
                            if data is '0' then self.parent().parent().remove()
                            return
                        .fail () ->
                            $('<div></div>').html("The travel #{ type } could not be deleted due to an error.").dialog
                                title: 'Error'
                        $(document).data('notes').funcs.initListPageListeners()
                        $(document).data('notes').funcs.initPager()
                        $(@).dialog 'close'
                        return
                    No: ->
                        # Unset checkbox
                        $checkbox.prop 'checked', false
                        $(@).dialog 'close'
                        return
                close: ->
                    $(@).dialog 'destroy'
                    return
            return
            
        deleteAction: (title, message, url, identifier) ->
          if $(identifier+':checked').length > 0
            $('<div></div>').html('Are you sure you want to delete the '+message+'?').dialog
              title: title
              buttons:
                  Yes: ->
                      $.ajax
                        method: 'POST'
                        url: url
                        data: $(identifier).serialize()
                      .done (data) ->
                        if data[0]?.userRelated
                            $(document).data('notes').funcs.showAlert data, 'create', 'Deletion not allowed for roles with relations', true
                        else
                          $(identifier+':checked').closest('tr').remove()

                        $(document).data('notes').funcs.initListPageListeners()
                        $(document).data('notes').funcs.initDeleteMultipleListener()
                        $(document).data('notes').funcs.initPager()
                        return
                      .fail () ->
                          $('<div></div>').html('The '+message+' could not be deleted due to an error.').dialog
                              title: 'Error'
                      $(@).dialog 'close'
                      return
                  No: ->
                      $(identifier + ':checkbox').attr 'checked', false
                      $(@).dialog 'close'
                      return
              close: ->
                  $(@).dialog 'destroy'
                  return
                
        showAlert: ($owner, response, actionType, message, forceClass) ->
            $errorContainer = $owner.find '#reply-message'
            $relatedContainer = $('#list-reply-message')

            if $errorContainer.length is 0
                return off

            $errorContainer.addClass "alert-message"

            if response[0]? and response[0].response == 'error'
              if "update" == actionType or "create" == actionType
                errorString = "<ul>"
                for i in response[0].errorMessage
                  errorString += "<li>"+i+"</li>"
                errorString += "</ul>"
                $errorContainer
                  .html("<i class='fa fa-exclamation-triangle'></i> <strong>Error messages:</strong>"+errorString)
                  .removeClass('success-message')
                  .addClass('error-message')
              else if "delete" == actionType
                $relatedContainer
                  .html("<i class='fa fa-exclamation-triangle'></i> Error, while tried to delete the user(s)! <i class='float-right fa fa-chevron-circle-up'></i> ")
                  .removeClass('success-message')
                  .addClass('error-message')
                  .fadeIn(200)
                  .delay(5000)
                  .slideUp(1000)
              returnVal = off
            else
                $relatedContainer
                  .html("<i class='fa fa-check-square'></i> "+message+"! <i class='float-right fa fa-chevron-circle-up'></i> ")
                  .addClass("alert-message")
                  .addClass('success-message')
                  .fadeIn(200)
                  .delay(2000)
                  .slideUp(1000)
                $errorContainer
                  .removeClass('alert-message error-message')
                  .empty()
                returnVal = on
              
              if forceClass
                $relatedContainer.removeClass('success-message').addClass('error-message')

            # Update custom scrollbar
            $owner.mCustomScrollbar 'update' if $owner?

            return returnVal
            
        createButton: (text, classes, id, $parent = '', redirectAction = '') ->
            $button = $('<div>').html(text)
                        .addClass(classes)
                        .attr('id', id)
               
            if '' != redirectAction
                $button.on 'click', ->
                    window.location.href = Routing.generate redirectAction
               
            if '' != parent
                $parent.append $button
                
            return $button
            
        makeElementToggleAble: (parent, $toggleItems, elementToToggle = '') ->
            $toggleItems.each ->
                $parent = $(@).find(parent)
                self = $(@)
                $toggleIcon = $('<i>')
                                .addClass('fa fa-chevron-up toggle-icon')
                                .addClass 'color-white background-color-orange border-radius-5 cursor-pointer float-right'
                $toggleIcon.on 'click', ->
                    if '' != elementToToggle
                        $elementToToggle = self.find elementToToggle
                        if not $elementToToggle.is(':animated')
                            $toggleIcon.toggleClass 'fa-chevron-down'
                            $elementToToggle.slideToggle()
                    else
                        if not $parent.next().is(':animated')
                            $toggleIcon.toggleClass 'fa-chevron-down'
                            $parent.next().slideToggle()
                $parent.append $toggleIcon
                    
###
 * jQuery datepicker extension
 * Datepicker extended by custom rendering possibility
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @depends jQuery
 *
 * @param object  options List of options
###
__picker = $.fn.datepicker

$.fn.datepicker = (options = {}, parameters) ->
    $self = @

    defaultOptions =
        altField: '#altDate' + $(@).attr('id')
        altFormat: $.datepicker.ISO_8601
    # Merge passed options
    $.extend true, defaultOptions, options

    defaultParameters =
        wrapper: $('<span class="position-relative datepicker-wrapper"></span>')
        indicatorIcon: $('<i>')
        deleteIcon: $('<i>')
        testNativeSupport: off
    # Merge passed parameters
    $.extend true, defaultParameters, parameters

    # Allow bypassing native support test
    if parameters?.testNativeSupport is on and Modernizr.inputtypes.date
        return

    __picker.apply this, [defaultOptions]

    if options.showOn isnt 'button'
        $wrapper = defaultParameters.wrapper
        $deleteIcon = defaultParameters.deleteIcon
        $calendarIcon = defaultParameters.indicatorIcon

        $self.attr
            readonly: 'readonly'
        .addClass 'icon-prefix-indent display-inline-block'

        $self.wrap $wrapper

        # Adding delete icon and handle delete event
        $deleteIcon.addClass 'fa fa-times-circle position-absolute input-postfix-position cursor-pointer'
        $deleteIcon.click ->
            $.datepicker._clearDate $self

        $self.after $deleteIcon

        # Adding calendar icon.
        $calendarIcon.addClass 'fa fa-calendar position-absolute input-prefix-position cursor-pointer'
        $calendarIcon.click ->
            $(@).parent().parent().children('input').focus()
            $(@).addClass 'display-none-important'

        $self.before $calendarIcon

        # Register delete icon display events
        $self.closest('.datepicker-wrapper')
            .on 'mouseenter.datepicker', 'input, i.fa-times-circle',  ->
                if $self.val() != ''
                    $deleteIcon.addClass 'display-inline-block-important'
            .on 'mouseleave.datepicker', 'input, i.fa-times-circle',  ->
                $deleteIcon.removeClass 'display-inline-block-important'

    return $self

###
 * jQuery dialog extension
 * Title attribute extended to support html chunks
 * Close function overwritten to destory by default
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @depends jQuery
 *
###
$.widget "ui.dialog", $.extend {}, $.ui.dialog.prototype, {
    # Fix to allow dialog to pass html strings for title option
    _title: (title) ->
        if not @options.title
            title.html "&#160;"
        else
            title.html @options.title
        return

    # Enforce dialog destroy on close
    close: ->
        $.ui.dialog.prototype.destroy.call(this);
}

__dialog = $.fn.dialog

$.fn.dialog = (options) ->
    __dialog.apply this, [options]
    @.mCustomScrollbar()
    # to update scrollbar if element is resized
    $(@).on 'dialogresizestop', (event, ui) ->
        $(@).mCustomScrollbar 'update'

# Prototype extensions
        
$.fn.checkAll = (callback, selector) ->
    $el = if selector then $(selector) else $(@)
    checkAll = if $el.filter(':checked').length is $el.not(':disabled').length then false else true
    $el.each ->
        if 'disabled' != $(@).attr('disabled')
            $(@).prop 'checked', checkAll

    if callback
        callback($el)
        
String.prototype.capitalize = () ->
    result = @.trim()
    return result.charAt(0).toUpperCase() + result.slice(1)

$(document).ajaxStart ->
    # Add generic ajax indicator for global requests
    # Requests which do not require an indicator should set { global: false }
    if $('#ajax-loader').length is 0
        $loader = $('<div id="ajax-loader"><span></span><span></span><span></span></div>')
        $loader.css { bottom: $('.sf-toolbar').outerHeight() } if $('.sf-toolbar').length
        $loader.appendTo 'body'
        
$(document).ajaxStop ->
    $('#ajax-loader').remove()

$(document).ajaxComplete (event, XMLHttpRequest, ajaxOptions) ->
    id = XMLHttpRequest.responseText.match(/id="([\w|-]+)"/)
    $("##{id[1]} *[title]").tipsy() if id?[1]?
    
$(document).ajaxError (event, request, settings, thrownError) ->
    if window.location.href.indexOf('login') <= -1 and 401 is request.status
        loginUrl = Routing.generate 'OpitNotesUserBundle_security_login'
        $sessionTimeout = $('<div id="dialog-travelrequest-preview"></div>').html "Your session has timed out please <a href='#{ loginUrl }'>login</a> again."
        $sessionTimeout.dialog
            title: '<i class="fa fa-exclamation-circle"></i> Session timeout'
            width: 550
            maxHeight: $(window).outerHeight()-100
            modal: on
            buttons:
                Login: ->
                    window.location.href = loginUrl
    else
        serverMessage = request.responseText.match /<h1[^>]*>((?:.|\r?\n)*?)<\/h1>/
        message = "<h2 class=\"dialog-h2\">#{ thrownError }</h2>"

        if null isnt serverMessage
            message += "<p>#{ serverMessage[1] }</p>"

        $('<div id="dialog-error"></div>').html(message).dialog
            title: '<i class="fa fa-warning"></i> Error occured'
            width: 500
            buttons:
                Close: ->
                    $(@).dialog "destroy"
                    return
            close: ->
                $(@).dialog "destroy"
                return
    return

# Secret weather app feature ;)
$(document).keydown (e) ->
    # Load weather app on CTRL + w
    if(e.ctrlKey and e.altKey and e.keyCode == 87)
        return if $('#weather-dialog').length > 0

        if not $.fn.simpleWeather
            $.getScript '/libs/simpleWeather/js/jquery.simpleWeather.min.js', (data, textStatus, jqxhr) ->
                $('head').append( $('<link rel="stylesheet" type="text/css" />').attr('href', '/libs/simpleWeather/css/simpleWeather.css') );

                loadWeather 'Budapest'

loadWeather = (location, woeid, reinit = false) ->
    if not reinit
        $weatherContainer = $('<div id="weather-dialog" />')
    else
        $weatherContainer = $('#weather-dialog')

    $.simpleWeather
        location: location,
        woeid: woeid,
        unit: 'c',
        success: (weather) ->
            html = "<h2><i class=\"icon-#{weather.code}\"></i>#{weather.temp}&deg;#{weather.units.temp}</h2>
                <ul>
                    <li>#{weather.city}, #{weather.region}</li>
                    <li class=\"currently\">#{weather.currently}</li>
                    <li>#{weather.wind.direction} #{weather.wind.speed} #{weather.units.speed}</li>
                </ul>
                <button class=\"js-geolocation\" style=\"display: none;\">Use Your Location</button>"

            # Create the dialog
            $weatherContainer.html(html)

            if not reinit
                $weatherContainer.dialog
                    width: 550
                    height: 350
                    close: ->
                        $(@).dialog('destroy')

            # Enable geolocation option if available
            if Modernizr.geolocation
                # Register location button event
                $('.js-geolocation').on 'click.weather', ->
                    navigator.geolocation.getCurrentPosition (position) ->
                        loadWeather "#{position.coords.latitude},#{position.coords.longitude}", '', true
                $('.js-geolocation').show()
            else
                $('.js-geolocation').off 'click.weather'
                $('.js-geolocation').hide()

            return
        error: (error) ->
            console.log "<p>#{error}</p>"
            return

$(document).ready ->
    # init date picker plugin
    $(document).data('notes').funcs.initDateInputs()
    # init tooltips
    $('[title]').each ->
        if $(@).hasClass 'tipsy-notification'
            $('.tipsy-notification').tipsy({className: 'tipsy-notification-style', opacity: 1});
        else
            $(@).tipsy()