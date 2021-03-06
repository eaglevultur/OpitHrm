$("#addLeaveCategory").click ->
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_category', id: 0
    .done (data) ->
        $('<div id="dialog-editleavecategory"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Create leave category'
                width: 750
                modal: on
                buttons:
                    Create: ->
                        $.ajax
                            type: 'POST'
                            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_add_leave_category', id: 0
                            data: $('#addleavecategory_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_categories'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavecategory').html data
                                $(document).data('opithrm').funcs.initListPageListeners()
                                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('opithrm').funcs.showAlert $('#dialog-editleavecategory'), response, "create", "Leave category created successfully"
                                if validationResult is true
                                    $('#dialog-editleavecategory').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavecategory').dialog "destroy"
                        return
            return
        return

$("#form-leavecategory").on "click", ".list-leavecategory", ->
    id = $(@).attr "data-id"
    $.ajax
        method: 'GET'
        url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_show_leave_category', id: id
    .done (data) ->
        $('<div id="dialog-editleavecategory"></div>').html(data)
            .dialog
                title: '<i class="fa fa-list-alt"></i> Edit leave category'
                width: 750
                modal: on
                buttons:
                    Edit: ->
                        $.ajax
                            type: 'POST'
                            url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_add_leave_category', id: id
                            data: $('#addleavecategory_frm').serialize()
                        .done (data)->
                            response = data
                            $.ajax
                                type: 'POST'
                                url: Routing.generate 'OpitOpitHrmLeaveBundle_admin_list_leave_categories'
                                data: "showList" : 1
                            .done (data)->
                                $('#form-leavecategory').html data
                                $(document).data('opithrm').funcs.initListPageListeners()
                                $(document).data('opithrm').funcs.initDeleteMultipleListener()
                                validationResult = $(document).data('opithrm').funcs.showAlert $('#dialog-editleavecategory'), response, "create", "Leave category modified successfully"
                                if validationResult is true
                                    $('#dialog-editleavecategory').dialog "destroy"
                    Close: ->
                        $('#dialog-editleavecategory').dialog "destroy"
                        return
            return
        return

# Delete button
$('#delete').click ->    
    do deleteLeaveCategory

# Delete icon in the table row
$('#form-leavecategory').on 'click', '.delete-single-leavecategory', (event) ->
    event.preventDefault()
    $checkbox = $(@).closest('tr').find ':checkbox'
    $checkbox.prop 'checked', true
    do deleteLeaveCategory

# Call the deleteAction from the app main.js
deleteLeaveCategory = () ->  
    url = Routing.generate 'OpitOpitHrmLeaveBundle_admin_delete_leave_category'
    $(document).data('opithrm').funcs.deleteAction('Leave category delete', 'leave category(s)', url, '.list-delete-leavecategory')

$('#form-leavecategory').on 'click', '.order-text', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).parent().find('i').attr('data-field'), 'OpitOpitHrmLeaveBundle_admin_list_leave_categories', 'list-table', 'searchForm'
$('#form-leavecategory').on 'click', '.fa-sort', ->
    $(document).data('opithrm').funcs.serverSideListOrdering $(@), $(@).data('field'), 'OpitOpitHrmLeaveBundle_admin_list_leave_categories', 'list-table', 'searchForm'