# Init leave history for travel requests
history = new StatusHistory('OpitOpitHrmLeaveBundle_status_history')
do history.init

# Listener to set end date to start date if empty
$('form#leaveRequestForm').on 'focus', '.end-date', ->
    $startDateInput = $(@).closest('div').prev().find 'input.start-date'

    $(@).val $startDateInput.val() if $(@).val() is ''

# Check the leave dates overlapping
$('form#leaveRequestForm').on 'blur.validation, change.validation', '.start-date', ->
    checkDatesOverlapping $(@)

$('form#leaveRequestForm').on 'blur.validation, change.validation', '.end-date', ->
    checkDatesOverlapping $(@)

createErrorLabel = (errorMessage, errorClass = '', attributes = []) ->
    $errorLabel = $('<label>')
    $errorLabel
        .addClass 'error'
        .addClass errorClass
        .html errorMessage
        
    attributes.forEach (attribute) ->
        $errorLabel.attr attribute['property'], attribute['value']

    return $errorLabel

# Validate all leave dates
checkAllDateOverLapping = () ->
    isValid = yes
    $('.formFieldsetChild').each (index, element) ->
        isLeaveDateValid = validateDatesOverlapping $(element).find('.start-date'), $(element).find('.end-date')
        if isValid is yes then isValid = isLeaveDateValid

    return isValid

# Checking dates overlapping on the current date input field.
checkDatesOverlapping = ($self) ->
    $formFieldset = $self.closest('.formFieldsetChild')
    $currentStartDate = $formFieldset.find '.start-date'
    $currentEndDate = $formFieldset.find '.end-date'
    # Call the validator to check dates overlapping.
    validateDatesOverlapping $currentStartDate, $currentEndDate

# Validate the leave dates overlapping
validateDatesOverlapping = ($currentStartDate, $currentEndDate) ->
    isValid = yes
    currentHasError = no
    $currentEndDateParent = $currentEndDate.closest('div')

    $currentEndDate.removeClass 'error'
    $currentEndDateParent.find('.overlap-error').remove()

    # Iterate over leave dates
    $('.formFieldsetChild').each (index, element) ->
        $startDate = $(element).find '.start-date'
        $endDate = $(element).find '.end-date'
        $endDateParent = $endDate.closest('div')

        $endDate.removeClass 'error'
        $endDateParent.find('.overlap-error').remove()

        if $startDate[0] != $currentStartDate[0] and ($currentStartDate.val() <= $endDate.val()) and ($startDate.val() <= $currentEndDate.val())
            $endDate.addClass 'error'
            $endDateParent.append createErrorLabel 'Overlapping dates', 'overlap-error'

            if currentHasError is no
                currentHasError = yes
                $currentEndDate.addClass 'error'
                $currentEndDateParent.append createErrorLabel 'Overlapping dates', 'overlap-error'

            isValid = no

    return isValid

$(document).ready ->
    compareLeaveDates = () ->
        isValid = yes
        $('.formFieldsetChild').each (index) ->
            $startDate = $(@).find('.start-date')
            $startDateParent = $startDate.closest 'div'
            startDateVal = $startDate.val()
            
            $endDate = $(@).find('.end-date')
            endDateVal = $endDate.val()
            
            if startDateVal > endDateVal
                isValid = no
                if $startDateParent.children('label.bigger-error').length is 0
                    $startDateParent.append createErrorLabel('Start date can not be bigger than end date.', 'bigger-error')
                    $startDate.addClass 'error'
            else
                $startDateParent.find('label.bigger-error').remove()
                if $startDateParent.find('label.past-error').length == 0
                    $startDate.removeClass 'error'

        return isValid
        
    validateNumberOfLeaves = () ->
        isValid = yes
        if $('.formFieldsetChild').length <= 0
            isValid = no
            if $('.leave-error').length <= 0
                $errorContainer = $('#reply-message')
                $errorMessage = $('<ul>').addClass('leave-error').append $('<li>').html('No leave date added.')
                $errorContainer
                    .append $errorMessage
                    .removeClass 'display-none'
        else
            $('#reply-message').addClass 'display-none'
            $('.leave-error').remove()
            
        return isValid

    validateEmployeesForMLR = () ->
        isValid = yes
        if $('#other-employees').is(':checked') and not $('input[name="employee[]"]').is(':checked')
            isValid = no
            if $('.leave-error').length <= 0
                $errorContainer = $('#reply-message')
                $errorMessage = $('<ul>').addClass('leave-error').append $('<li>').html('No employees are selected for mass leave request.')
                $errorContainer
                    .append $errorMessage
                    .removeClass 'display-none'
        else
            $('#reply-message').addClass 'display-none'
            $('.leave-error').remove()

        return isValid

    isNotMassLR = () ->
        if $('.company-employees:checked').length > 1 then return false else return true

    addPastDateError = ($startDate) ->
        $errorLabel = createErrorLabel('Start date can not be in the past', 'past-date-error')
        $startDate.addClass 'error'
        $startDate.closest('div').append $errorLabel

    # Check if LR has leaves in the past
    hasPastDates = (addError) ->
        hasPDates = no
        $('.start-date').each () ->
            $startDate = $(@)
            $startDate.removeClass 'error'
            $startDate.parent().find('.past-date-error').remove()

            dateNow = new Date()
            startDate = new Date($startDate.val())

            if startDate < dateNow
                if addError is yes
                    addPastDateError($startDate)
                hasPDates = yes

        return hasPDates

    validateGm = () ->
        isValid = yes
        $generalManger = $('#leave_request_general_manager')
        $generalManagerAc = $('#leave_request_general_manager_ac')
        if $generalManger.val() == ''
            $parent = $generalManagerAc.closest('div')
            if $parent.find('.gm-error').length == 0
                $parent.append createErrorLabel('A general manager must be selected.', 'gm-error')
                $generalManagerAc.addClass 'error'
            isValid = no
        else
            $generalManagerAc.parent().find('.gm-error').remove()
            $generalManagerAc.removeClass 'error'

        return isValid
            
    # method to create button to delete a leave
    createLeaveDeleteButton = () ->
        $deleteButtonWrapper = $('<div>')
            .addClass 'deleteFormFieldsetChild formFieldsetButton form-fieldset-delete-button'
            .html '<i class="fa fa-minus-square"></i>Delete'
            .on 'click', ->
                $(@).closest('.formFieldsetChild').remove()
            
        return $deleteButtonWrapper
        
    # function to create and insert a leave into the interface
    createLeave = ($leave) ->
        if typeof $leave is 'object'
            $leave.find('.start-date').closest('div').addClass 'display-inline-block'
            $leave.find('.end-date').closest('div').addClass 'display-inline-block margin-left-5'
            
        index = $collectionHolder.data 'index'
        $collectionHolder.data('index', index + 1)
        $leaveWrapper = $('<div>').addClass 'formFieldsetChild padding-10 margin-left-1-em margin-bottom-1-em display-inline-block vertical-align-top'
        if $leave is undefined
            $leave = $(prototype.replace /__name__/g, index)
            $leaveWrapper.append $leave

            # init datepicker plugin
            $(document).data('opithrm').funcs.initDateInputs $leave
        else
            $leaveWrapper.append $leave

        $leave.append createLeaveDeleteButton()
        
        $errorList = $leave.find('ul')
        $errorListParent = $errorList.parent()
        if $errorList.length > 0
            $errorListParent.append $('<label>').addClass('error').html($errorList.find('li:first').html())
            $input = $errorListParent.find 'input'
            $input.addClass 'error'
            
            $errorList.remove()

        $leave.find('.description').removeAttr 'required'
    
        $leaveWrapper.insertBefore $('.addFormFieldsetChild')
        return $leave

    toggleLeaveCategory = ($leave) ->
        if $('.company-employees:checked').length > 1
            $leave.find('.leave-category').parent().hide()
        else
            $leave.find('.leave-category').parent().show()

    showRequestFor = ($self, $leaveRequestUser, $addFormFieldset, $employeeSelector) ->
        displayNone = 'display-none-important'
        if $self.val() is 'for-employees'
            # Add event listener on the employee checkboxes
            $('.company-employees').on 'change.category', ->
                toggleLeaveCategory $leave

            $leaveRequestUser.parent().addClass displayNone
            $addFormFieldset.addClass displayNone
            $employeeSelector.removeClass displayNone

            $employeeSelector.removeAttr 'disabled'

            if $('.formFieldsetChild').length is 0
                $leave = createLeave()
            else
                $leave = $('.formFieldsetChild')

            toggleLeaveCategory $leave
            $leave.find('.deleteFormFieldsetChild').remove()

        else if $self.val() is 'own'
            # Remove event listener on the employee checkboxes
            $('.company-employees').off 'change.category'

            $employeeSelector.addClass displayNone
            $leaveRequestUser.parent().removeClass displayNone
            $addFormFieldset.removeClass displayNone

            $employeeSelector.attr 'disabled', 'disabled'

        # Update scrollbar for employee container
        $('form#leaveRequestForm .option-list-scrollable').last().mCustomScrollbar 'update'

    showLRDetailsDialog = () ->
        $form = $('#leaveRequestForm')
        $submitBtn = $('#leave_request_create_leave_request')
        isNewLR = isNaN(window.location.href.slice(-1))
        modalButtonText = 'Edit'

        if isNewLR
            modalButtonText = 'Create'

        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitOpitHrmLeaveBundle_leave_show_details'
            data: 'preview=1&' + $form.serialize()
        .done (data) ->
            $preview = $('<div id="dialog-travelrequest-preview"></div>').html data
            $preview.dialog
                title: '<i class="fa fa-list-alt"></i> Details'
                close: ->
                    $preview.dialog 'destroy'
                width: 550
                buttons: [
                        text: modalButtonText,
                        click: ->
                            $form.submit()
                    ,
                        text: "#{modalButtonText} & send for approval",
                        click: ->
                            if isNewLR
                                $form.attr 'action', $form.attr('action') + '/new/fa'
                            else
                                $form.attr 'action', $form.attr('action') + '/fa'
                            $form.submit()
                            $preview.dialog 'destroy'
                    ,
                        text: 'Cancel',
                        click: ->
                            $preview.dialog 'destroy'
                            return
                ]

    $('.changeState').on 'change', ->
        $(document).data('opithrm').funcs.changeStateDialog $(@), $(document).data('opithrm').funcs.changeLeaveRequestStatus, {
            foreignId: $(@).data('lr')
            type: 'leave request'
        }
        
    $('#leave_request_team_manager_ac').autocomplete
        source: (request, response) ->
            $.post Routing.generate('OpitOpitHrmUserBundle_user_search', role: 'role_team_manager'), request, (data) -> response(data)
        minLength: 2
        select: (event, ui) ->
            $('#leave_request_team_manager').val ui.item.id
            return

    $('#leave_request_general_manager_ac').autocomplete
        source: (request, response) ->
            $.post Routing.generate('OpitOpitHrmUserBundle_user_search', role: 'role_general_manager'), request, (data) -> response(data)
        minLength: 2
        select: (event, ui) ->
            $('#leave_request_general_manager').val ui.item.id
            return

    $forAll = $('#forAll')
    $companyEmployees = $('.company-employees')

    changeLabel = (list) ->
        if $(list).filter(':checked').length is list.length
            $forAll.html('Uncheck all')
        else
            $forAll.html('Check all')

    $forAll.on 'click', ->
        $('.formFieldsetChild .leave-category').parent().toggle()
        $companyEmployees.checkAll (list) =>
            changeLabel list
            
    $companyEmployees.on 'change', ->
        changeLabel $companyEmployees

    $('#leave_request').find('label:first').remove()
    $collectionHolder = $('#leave_request_leaves')
    $collectionHolder.data 'index', 0
    
    prototype = $collectionHolder.data 'prototype'
    $prototype = $(prototype)
    $prototype.find('.start-date').parent().addClass('display-inline-block')
    $prototype.find('.end-date').parent().addClass('display-inline-block margin-left-5')
    prototype = $prototype.html().replace '<label class="required">__name__label__</label>', ''
    
    $form = $collectionHolder.closest 'form'
    $form.prepend $('.formFieldset')
    $form.find('#leave_request_create_leave_request').parent().append $('#cancel-button')

    $leaveRequestUser = $('#leave_request_user_ac')
    $addFormFieldset = $('.addFormFieldsetChild')
    $employeeSelector = $('#employee-selector')

    $collectionHolder.children().each (index) ->
        $(@).find('label:first').remove()
        if $(@).find('.isLocked').val() == '1'
            $(@).addClass 'disabled'
            $('.addFormFieldsetChild').removeClass 'display-none-important'
        createLeave($(@))

    # Init custom scrollbars on page load
    if $('form#leaveRequestForm .mCustomScrollBox').length is 0
        $('form#leaveRequestForm .option-list-scrollable').mCustomScrollbar()

    # Trigger showRequestFor if selection is present
    if $('.leave-request-owner:checked').length > 0
        showRequestFor $('.leave-request-owner:checked'), $leaveRequestUser, $addFormFieldset, $employeeSelector

    # Register request for radio event listener
    $('.leave-request-owner').on 'change', ->
        $('.formFieldsetChild').remove()
        showRequestFor $(@), $leaveRequestUser, $addFormFieldset, $employeeSelector

    $('.addFormFieldsetChild').on 'click', ->
        createLeave()

    $('.disabled .deleteFormFieldsetChild, .disabled .addFormFieldsetChild').each ->
        $(@).remove()
        
    $('.disabled select, .disabled input, .disabled textarea').each ->
        $(@).attr 'disabled', 'disabled'
        
    $('.disabled #leave_request_create_leave_request')
        .addClass 'button-disabled'
        .attr 'disabled', 'disabled'
        
    $('.disabled #leave_request_create_leave_request').attr 'disabled', 'disabled'
    
    $( '#leave_request_create_leave_request' ).on 'click', (event) ->
        event.preventDefault()
        $form = $('#leaveRequestForm')
        if compareLeaveDates() is yes and validateNumberOfLeaves() is yes and validateGm() is yes and validateEmployeesForMLR() is yes and checkAllDateOverLapping() is yes
            if isGeneralManager is yes
                if $('#own').is(':checked') and !hasPastDates()
                    do showLRDetailsDialog
                else if isNotMassLR() and hasPastDates()
                    message = 'Creating a past leave request will effect the time sheet of that period. Do you want to continue?'
                    $('<div id="dialog-show-past-lr-warning"></div>').html(message)
                        .dialog
                            title: '<i class="fa fa fa-exclamation-triangle"></i> Creating past leave request'
                            width: 550
                            maxHeight: $(window).outerHeight()-100
                            modal: on
                            buttons:
                                Yes: ->
                                    $form.submit()
                                No: ->
                                    $('#dialog-show-past-lr-warning').dialog 'destroy'
                else
                    $form.submit()
            else if hasPastDates(yes) is no
                do showLRDetailsDialog