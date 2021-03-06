$.extend true, $(document).data('opithrm'),
    funcs:
        changeLeaveRequestStatus: (data, $spinner) ->
            $.ajax
                method: 'POST'
                url: Routing.generate 'OpitOpitHrmLeaveBundle_leave_request_state'
                data: data
                global: false
            .done (data) ->
                $spinner.remove()
            .complete () ->
                $spinner.remove()
            .fail (data) ->
                $spinner.remove()
                $changeState = $('.changeState[data-tr="' + leaveRequestId + '"]')
                                .removeClass('dropdown-disabled')
                                .prop 'selectedIndex', 0
                $('<div id="dialog-tr-error"></div>').html 'Status could not be changed due to an error.'
                    .dialog
                        title: '<i class="fa fa-exclamation-triangle"></i> An error occurred'
                        width: 550
                        buttons:
                            Close: ->
                                $(@).dialog 'destroy'
                                return
