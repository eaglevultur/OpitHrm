createDeleteButton = ->
    $deleteButton = $('<div>')
    $deleteButton.addClass('deleteFormFieldsetChild formFieldsetButton').html '<i class="fa fa-minus-square"></i>Delete'
    $deleteButton.on 'click', ->
        $(@).parent().remove()
        
    return $deleteButton

reCreateExpenses = (self) ->
    $selectedExpense = $('<span>').addClass 'selected-expense'
    $selectedExpense.html self.find('select').find(':selected').text()
    $container = $('<div>').addClass 'formFieldsetChild'
    self.children('label:first').remove()
    $container.append self
    $container.append createDeleteButton()
    $container.prepend $selectedExpense
    
    return $container

addNewForm = (collectionHolder, parent) ->
    event.preventDefault()
    
    #collectionHolder.data 'index', collectionHolder.find(':input').length
    
    # get form data from collection holder
    prototype = collectionHolder.data 'prototype'
    
    index = collectionHolder.data 'index'
    
    prototype = prototype.replace '<label class="required">__name__label__</label>', ''
    newForm = prototype.replace /__name__/g, index
    
    $selectedExpense = $('<span>').addClass 'selected-expense'
    $selectedExpense.html 'Expense type'
    
    $formFieldsetChild = $('<div>').addClass 'formFieldsetChild'
    $formFieldsetChild.append newForm
    $formFieldsetChild.append createDeleteButton()
    $formFieldsetChild.prepend $selectedExpense
    
    collectionHolder.data 'index', index + 1

    parent.append $formFieldsetChild
    
createTableRow = (text, value, rowTitle) ->
    $row = $('<tr>')
    
    $textColumn = $('<td>')
    $textColumn.addClass 'bgGrey bold'
    $textColumn.html text + ' <i class="fa fa-clock-o" title="'+rowTitle+'"></i>'
    
    $valueColumn = $('<td>')
    $valueColumn.text value + ' EUR'
    
    if text == 'Total'
        $textColumn.html ''
        $valueColumn.html '<strong>Total</strong><br /> ' + value + ' EUR'
        
    $row.append $textColumn
    $row.append $valueColumn
    
    return $row
    
$perDiem = $('<div>')
    
getHoursBetween = (departureDate, departureHour, departureMinute, arrivalDate, arrivalHour, arrivalMinute) ->
    departure = new Date "#{ departureDate } #{ departureHour }:#{ departureMinute }"
    arrival = new Date "#{ arrivalDate } #{ arrivalHour }:#{ arrivalMinute }"
    
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_expense_perdiem'
        data: {arrival: arrival, departure: departure}
    .done (data) ->
        $('.perDiemTable').remove()
        $perDiemTable = $('<table>')
        $perDiemTable.addClass 'perDiemTable bordered'
        
        $perDiemHeader = $('<tr>')
        $perDiemDay = $('<th>')
        $perDiemDay.text 'Day'
        
        $perDiemAmount = $('<th>')
        $perDiemAmount.text 'Amount'
        
        $perDiemHeader.append $perDiemDay
        $perDiemHeader.append $perDiemAmount
        $perDiemTable.append $perDiemHeader
        
        if data['totalTravelHoursOnSameDay'] > 0
            $perDiemTable.append createTableRow(
                'Travel hours',
                data['totalTravelHoursOnSameDay'],
                ""
            )                 
            
            $perDiemTable.append createTableRow(
                'Total',
                data['totalPerDiem'],
                ""
            )
            
        else
            $perDiemTable.append createTableRow(
                'Departure',
                data['departurePerDiem'],
                "Number of hours traveled on departure day #{ data['departureHours'] }."
            )
        
            $perDiemTable.append createTableRow(
                "Full (#{ data['daysBetween'] })", 
                data['daysBetweenPerDiem'], 
                "Number of full days #{ data['daysBetween'] }."
            )        
        
            $perDiemTable.append createTableRow(
                'Arrival',
                data['arrivalPerDiem'],
                "Number of hours traveled on arrival day #{ data['arrivalHours'] }."
            )

            $perDiemTable.append createTableRow('Total', data['totalPerDiem'])
            
            $perDiem.append $perDiemTable

$(document).ready ->
    arrivalDate = $('#travelExpense_arrivalDateTime_date')
    arrivalTime = $('#travelExpense_arrivalDateTime_time')
    departureDate = $('#travelExpense_departureDateTime_date')
    departureTime = $('#travelExpense_departureDateTime_time')
    
    arrivalDate.attr 'readonly', 'readonly'
    departureDate.attr 'readonly', 'readonly'
    
    arrivalTime.addClass 'inlineElements time-picker'
    departureTime.addClass 'inlineElements time-picker'
    
    arrivalDate.css display: 'inline-block'
    departureDate.css display: 'inline-block'

    $('#travelExpense').children('.formFieldset:nth-child(3)').append $addCompanyTagLink
    $('#travelExpense').children('.formFieldset:nth-child(2)').append $addUserTagLink
    companyPaidExpensesIndex = 0;
    userPaidExpensesIndex = 0;
    
    if $('#travelExpense_companyPaidExpenses').children('div').length > 0
        $('#travelExpense_companyPaidExpenses').children('div').each ->
            $container = reCreateExpenses($(@))
            $('#travelExpense').children('.formFieldset:nth-child(3)').append $container
            companyPaidExpensesIndex++
        
    if $('#travelExpense_userPaidExpenses').children('div').length > 0
        $('#travelExpense_userPaidExpenses').children('div').each ->
            $container = reCreateExpenses($(@))
            $('#travelExpense').children('.formFieldset:nth-child(2)').append $container     
            userPaidExpensesIndex++
    
    $('#travelExpense_companyPaidExpenses').data 'index', companyPaidExpensesIndex
    $('#travelExpense_userPaidExpenses').data 'index', userPaidExpensesIndex
    $('#travelExpense_companyPaidExpenses').parent().children('label').remove()
    $('#travelExpense_userPaidExpenses').parent().children('label').remove()
    
    $('#travelExpense').css display: 'block'
    
    $perDiemAmountsTable = $('<table>')
    $perDiemAmountsTable.addClass 'per-diem-amounts display-none'
    $.ajax
        method: 'POST'
        url: Routing.generate 'OpitNotesTravelBundle_expense_perdiemvalues'
    .done (data) ->
        for key, value of data
            $tr = $('<tr>')
            $tdHours = $('<td>')
            $tdHours.attr 'width', '100px'
            $tdHours.text "Over #{ key } hours"
            $tdAmount = $('<td>')
            $tdAmount.text value + ' EUR'
            
            $tr.append $tdHours
            $tr.append $tdAmount
            
            $perDiemAmountsTable.append $tr
    $tr = $('<tr>')
    $td = $('<td>')
    $td.attr 'colspan', 2
    $td.html 'Per diem is given to employee considering the following slab.'
    $tr.append $td
    $perDiemAmountsTable.prepend $tr
    $perDiem.append $perDiemAmountsTable
    
    $perDiemTitle = $('<h3>')
    $perDiemTitle.html 'Per diem <i class="fa fa-question-circle"></i>';
    $perDiem.append $perDiemTitle
    $perDiem.addClass 'formFieldset'
    $perDiem.insertBefore($('#travelExpense_add_travel_expense').parent())
    
    $('.fa-question-circle').on 'mouseover', ->
        $('.per-diem-amounts').removeClass 'display-none'
    $('.fa-question-circle').on 'mouseout', ->
        $('.per-diem-amounts').addClass 'display-none'
    
    $departureHour = $('#travelExpense_departureDateTime_time_hour')
    $departureMinute = $('#travelExpense_departureDateTime_time_minute')
    $arrivalHour = $('#travelExpense_arrivalDateTime_time_hour')
    $arrivalMinute = $('#travelExpense_arrivalDateTime_time_minute')
    
    departureDateVal = departureDate.val()
    departureHourVal = $departureHour.val()
    departureMinuteVal = $departureMinute.val()
    arrivalDateVal = arrivalDate.val()
    arrivalHourVal = $arrivalHour.val()
    arrivalMinuteVal = $arrivalMinute.val()
    
    getHoursBetween(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    
    $departureHour.on 'change', ->
        departureHourVal = $departureHour.val()
        getHoursBetween(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    $departureMinute.on 'change', ->
        departureMinuteVal = $departureMinute.val()
        getHoursBetween(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    $arrivalHour.on 'change', ->
        arrivalHourVal = $arrivalHour.val()
        getHoursBetween(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)
    $arrivalMinute.on 'change', ->
        arrivalMinuteVal = $arrivalMinute.val()
        getHoursBetween(departureDateVal, departureHourVal, departureMinuteVal, arrivalDateVal, arrivalHourVal, arrivalMinuteVal)

$formFieldset = $('<div>')
$formFieldset.addClass 'formFieldset'

$generalFormFieldset = $formFieldset.clone().addClass 'generalFormFieldset'
$expensesPaidByMe = $formFieldset.clone().append $('<h3>').html 'Expenses paid by me'
$expensesPaidByOpit = $formFieldset.clone().append $('<h3>').html 'Expenses paid by opit'

$('#travelExpense').prepend $expensesPaidByOpit
$('#travelExpense').prepend $expensesPaidByMe
$('#travelExpense').prepend $generalFormFieldset
$('#travelExpense').addClass 'travelForm'
        
$('.formFieldset').on 'change', '.te-expense-type', ->
    $(@).closest('.formFieldsetChild').children('.selected-expense').html $("##{ $(@).attr 'id' } :selected").text()

# move all element with specified class into form fieldset
$('.te-claim').each (index) ->
    $(@).parent().addClass 'inlineElements'
    $generalFormFieldset.append $(@).parent()
    
    # if element has class display-none add class to elements parent node
    if $(@).hasClass 'display-none'
        $(@).removeClass 'display-none'
        $(@).parent().addClass 'display-none'
    # allow two properties in a row
    if index % 2
        $generalFormFieldset.append $('<br>')
        
# create add expenses button, and add on click listeners to them
$addCompanyTagLink = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add company expense</div>')
$addCompanyTagLink.on 'click', ->
    addNewForm($('#travelExpense_companyPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(3)'))    
    
$addUserTagLink = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add user expense</div>')
$addUserTagLink.on 'click', ->
    addNewForm($('#travelExpense_userPaidExpenses'), $('#travelExpense').children('.formFieldset:nth-child(2)'))
    
    
$form = $('#travelExpenseForm')    
    
$.validator.addMethod 'compare', (value, element) ->
    departureDate = $('#travelExpense_departureDateTime_date').val()
    arrivalDate = $('#travelExpense_arrivalDateTime_date').val()
    
    departureTimeHour = $('#travelExpense_departureDateTime_time_hour').val()
    arrivalTimeHour = $('#travelExpense_arrivalDateTime_time_hour').val()
    
    departureTimeMinute = $('#travelExpense_departureDateTime_time_minute').val()
    arrivalTimeMinute = $('#travelExpense_arrivalDateTime_time_minute').val()
    
    departure = departureDate+' '+departureTimeHour+':'+departureTimeMinute
    arrival = arrivalDate+' '+arrivalTimeHour+':'+arrivalTimeMinute
    
    departure = new Date(departure)
    arrival = new Date(arrival)
    
    $('#travelExpense_arrivalDateTime_time_minute').css border: 'solid 1px rgb(170, 170, 170)'
    
    return departure < arrival
, 'Arrival date should not be smaller than departure date.'    
    
$form.validate
    ignore: []
    rules:
        'travelExpense[arrivalDateTime][time][minute]': 'compare',
        'travelExpense[taxIdentification]': {maxlength: 11},
        'travelExpense[advancesPayback]': {digits: true},
        'travelExpense[toSettle]': {digits: true}
 
$('#travelExpense_add_travel_expense').on 'click', ->
    event.preventDefault()
    if $form.valid()
        console.log 'valid'
        $.ajax
            method: 'POST'
            url: Routing.generate 'OpitNotesTravelBundle_expense_show_details'
            data: 'preview=1&' + $form.serialize()
        .done (data) ->
            $preview = $('<div id="dialog-travelrequest-preview"></div>').html data
            $preview.dialog
                open: ->
                    $('.ui-dialog-title').append '<i class="fa fa-list-alt"></i> Details'
                close: ->
                    $preview.dialog "destroy"
                width: 550
                maxHeight: $(window).outerHeight()-100
                modal: on
                buttons:
                    Cancel: ->
                        $preview.dialog "destroy"
                        return
                    Save: ->
                        $form.submit()
                        $preview.dialog "destroy"
                        return  
        .fail () ->
            $('<div></div>').html('The travel expense could not be saved due to an error.').dialog
                title: 'Error'                        