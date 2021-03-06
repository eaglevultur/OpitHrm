$("#addLeaveType").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_type', id: 0
    .done (data) ->
        $('<div id="dialog-editleavetype"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create Administrative Leave/Working Day type'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_add_leave_type', id: 0
                            data: $('#addleavetype_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_types'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavetype').html data
                                $(document).data('opithrm').funcs.initListPageListeners()
                                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('opithrm').funcs.showAlert $('#dialog-editleavetype'), response, "create", "Leave type created successfully"
                                if validationResult is true
                                    $('#dialog-editleavetype').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavetype').dialog "destroy"
                        return
            return
        return

$("#form-leavetype").on "click", ".list-leavetype", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_type', id: id
    .done (data) ->
        $('<div id="dialog-editleavetype"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit Administrative Leave/Working Day type'
                width: 750
                modal: on
                buttons:
                    Edit: ->
                        $.ajax
                            type: 'POST'
                            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_add_leave_type', id: id
                            data: $('#addleavetype_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_types'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavetype').html data
                                $(document).data('opithrm').funcs.initListPageListeners()
                                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('opithrm').funcs.showAlert $('#dialog-editleavetype'), response, "create", "Administrative Leave/Working Day type modified successfully"
                                if validationResult is true
                                    $('#dialog-editleavetype').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavetype').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->
    do deleteLeaveType

# Delete icon in the table row
$('#form-leavetype').on "click", ".delete-single-leavetype", (event) ->
    event.preventDefault()
    $checkbox = $(@).closest('tr').find(':checkbox')
    $checkbox.prop 'checked', true
    do deleteLeaveType

# Call the deleteAction from the app main.js
deleteLeaveType = () ->
    url = Routing.generate 'OpitOpitHrmLeaveBundle_admin_delete_leave_type'
    $(document).data('opithrm').funcs.deleteAction('Leave type delete', 'leave type(s)', url, '.list-delete-leavetype')

$('#form-leavetype').on 'click', '.order-text', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitOpitHrmLeaveBundle_admin_list_leave_types', 'list-table', 'searchForm'
$('#form-leavetype').on 'click', '.fa-sort', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmLeaveBundle_admin_list_leave_types', 'list-table', 'searchForm'