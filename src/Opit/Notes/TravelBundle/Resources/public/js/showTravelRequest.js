// Generated by CoffeeScript 1.6.3
(function() {
  var $accomodationCollection, $addAccomodation, $addDestination, $destinationCollection, $form, accomodationsLabel, addForm, addFormDeleteButton, destinationsLabel, generalData, requiredApprovals, travelOpportunity, travelRequestAccomodations0, travelRequestDestinations0, url;

  addFormDeleteButton = function() {
    var $deleteButton;
    $deleteButton = $('<div>').html('<i class="fa fa-minus-square"></i>Delete');
    $deleteButton.addClass('deleteFormFieldsetChild formFieldsetButton');
    $deleteButton.click(function() {
      return $(this).parent().remove();
    });
    return $deleteButton;
  };

  $('label.required').each(function() {
    if ($(this).text() === '0') {
      $(this).remove();
    }
  });

  generalData = $('<div>').addClass('formFieldset generalFormFieldset');

  generalData.append($('#travelRequest_user_ac,\
                      #travelRequest_departure_date,\
                      #travelRequest_arrival_date,\
                      #travelRequest_customer_related,\
                      #travelRequest_opportunity_name,\
                      #travelRequest_trip_purpose').parent());

  $('#travelRequest').prepend(generalData);

  requiredApprovals = $('<div>').addClass('formFieldset marginLeft');

  requiredApprovals.append($('<h3>').html('Required approvals'));

  requiredApprovals.append($('#travelRequest_team_manager_ac').parent());

  requiredApprovals.append($('#travelRequest_general_manager_ac').parent());

  $('#travelRequest_general_manager').after(requiredApprovals);

  $('#travelRequest_destinations').parent().addClass('formFieldset');

  $('#travelRequest_accomodations').parent().addClass('formFieldset');

  $('#travelRequest_departure_date, #travelRequest_arrival_date,\
   #travelRequest_customer_related, #travelRequest_opportunity_name,\
   #travelRequest_team_manager_ac, #travelRequest_general_manager_ac').parent().addClass('inlineElements');

  $('#travelRequest_arrival_date').parent().after('<br />');

  accomodationsLabel = $('#travelRequest_accomodations').parent().children('label');

  accomodationsLabel.replaceWith('<h3>' + accomodationsLabel.html() + '</h3>');

  destinationsLabel = $('#travelRequest_destinations').parent().children('label');

  destinationsLabel.replaceWith('<h3>' + destinationsLabel.html() + '</h3>');

  travelRequestDestinations0 = $('#travelRequest_destinations_0');

  if ($('#travelRequest_destinations :input[type=text]').length > 1) {
    $('#travelRequest_destinations').children().each(function() {
      $(this).addClass('formFieldsetChild');
      $(this).children().remove('label');
      return $(this).append(addFormDeleteButton);
    });
  } else {
    if ($('#travelRequest_destinations :input[type=text]').val() === "") {
      travelRequestDestinations0.parent().remove();
    } else {
      travelRequestDestinations0.parent().addClass('formFieldsetChild');
      travelRequestDestinations0.parent().append(addFormDeleteButton);
    }
  }

  travelRequestAccomodations0 = $('#travelRequest_accomodations_0');

  if ($('#travelRequest_accomodations :input[type=text]').length > 2) {
    $('#travelRequest_accomodations').children().each(function() {
      $(this).addClass('formFieldsetChild');
      $(this).children().remove('label');
      return $(this).append(addFormDeleteButton);
    });
  } else {
    if ($('#travelRequest_accomodations :input[type=text]').val() === "") {
      travelRequestAccomodations0.parent().remove();
    } else {
      travelRequestAccomodations0.parent().addClass('formFieldsetChild');
      travelRequestAccomodations0.parent().append(addFormDeleteButton);
    }
  }

  travelOpportunity = $('#travelRequest_opportunity_name');

  if (travelOpportunity.val() === '') {
    travelOpportunity.parent().css({
      display: 'none'
    });
  } else {
    $('#travelRequest_customer_related').val('0');
  }

  $('#travelRequest_customer_related').change(function() {
    if ($(this).val() === "0") {
      travelOpportunity.parent().css({
        display: 'inline-block'
      });
      return travelOpportunity.attr('required', 'required');
    } else {
      travelOpportunity.parent().css({
        display: 'none'
      });
      return travelOpportunity.removeAttr('required');
    }
  });

  if (!Modernizr.inputtypes.date) {
    $('input[type=date]').each(function() {
      var id, name;
      name = $(this).attr('name');
      id = $(this).attr('id');
      $(this).after('<input type="hidden" name="' + name + '" id="altDate' + id + '" />');
      return $(this).datepicker({
        altField: '#altDate' + id,
        altFormat: 'yy-mm-dd'
      });
    });
  }

  $form = $('#travelRequestForm');

  url = $form.data('search');

  $('#travelRequest_user_ac').autocomplete({
    source: url + '?user=all',
    minLength: 2,
    response: function(event, ui) {},
    select: function(event, ui) {
      $('#travelRequest_user').val(ui.item.id);
    }
  });

  $('#travelRequest_team_manager_ac').autocomplete({
    source: url + '?user=team_manager',
    minLength: 2,
    select: function(event, ui) {
      $('#travelRequest_team_manager').val(ui.item.id);
    }
  });

  $('#travelRequest_general_manager_ac').autocomplete({
    source: url + '?user=general_manager',
    minLength: 2,
    select: function(event, ui) {
      $('#travelRequest_general_manager').val(ui.item.id);
    }
  });

  $addDestination = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add destination</div>');

  $destinationCollection = $('#travelRequest_destinations').append($addDestination);

  $destinationCollection.data('index', $destinationCollection.find(':input').length);

  $addDestination.click(function(e) {
    e.preventDefault();
    addForm($destinationCollection, $addDestination);
  });

  $addAccomodation = $('<div class="addFormFieldsetChild formFieldsetButton"><i class="fa fa-plus-square"></i>Add accomodation</div>');

  $accomodationCollection = $('#travelRequest_accomodations').append($addAccomodation);

  $accomodationCollection.data('index', $accomodationCollection.find(':input').length);

  $addAccomodation.click(function(e) {
    e.preventDefault();
    addForm($accomodationCollection, $addAccomodation);
  });

  addForm = function($collectionHolder, $addButton) {
    var $newForm, index, newForm, prototype;
    prototype = $collectionHolder.data('prototype');
    index = $collectionHolder.data('index');
    newForm = prototype.replace('<label class="required">__name__label__</label>', '');
    newForm = newForm.replace(/__name__/g, index);
    $newForm = $(newForm);
    $newForm = $newForm.append(addFormDeleteButton);
    $newForm.addClass('formFieldsetChild');
    $collectionHolder.data('index', index + 1);
    return $addButton.before($newForm);
  };

}).call(this);