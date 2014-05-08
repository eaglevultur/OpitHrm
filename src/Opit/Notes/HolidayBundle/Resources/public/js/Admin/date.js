// Generated by CoffeeScript 1.7.1
(function() {
  var deleteHolidayDate, inverse;

  $("#addHolidayDate").click(function() {
    return $.ajax({
      method: 'GET',
      url: Routing.generate('OpitNotesHolidayBundle_admin_show_holiday_date', {
        id: 0
      })
    }).done(function(data) {
      $('<div id="dialog-editholidaydate"></div>').html(data).dialog({
        title: '<i class="fa fa-list-alt"></i> Create holiday date',
        width: 750,
        modal: true,
        buttons: {
          Create: function() {
            return $.ajax({
              type: 'POST',
              global: false,
              url: Routing.generate('OpitNotesHolidayBundle_admin_add_holiday_date', {
                id: 0
              }),
              data: $('#addholidaydate_frm').serialize()
            }).done(function(data) {
              var response;
              response = data;
              return $.ajax({
                type: 'POST',
                global: false,
                url: Routing.generate('OpitNotesHolidayBundle_admin_list_holiday_dates'),
                data: {
                  "showList": 1
                }
              }).done(function(data) {
                var validationResult;
                $('#list-table').html(data);
                validationResult = $(document).data('notes').funcs.showAlert(response, "create", "Holiday date created successfully");
                if (validationResult === true) {
                  return $('#dialog-editholidaydate').dialog("destroy");
                }
              });
            });
          },
          Close: function() {
            $('#dialog-editholidaydate').dialog("destroy");
          }
        }
      });
      return;
    });
  });

  $("#list-table").on("click", ".list-holidaydate", function() {
    var id;
    id = $(this).attr("data-id");
    return $.ajax({
      method: 'GET',
      url: Routing.generate('OpitNotesHolidayBundle_admin_show_holiday_date', {
        id: id
      })
    }).done(function(data) {
      $('<div id="dialog-editholidaydate"></div>').html(data).dialog({
        title: '<i class="fa fa-list-alt"></i> Edit holiday date',
        width: 750,
        modal: true,
        buttons: {
          Save: function() {
            return $.ajax({
              type: 'POST',
              global: false,
              url: Routing.generate('OpitNotesHolidayBundle_admin_add_holiday_date', {
                id: id
              }),
              data: $('#addholidaydate_frm').serialize()
            }).done(function(data) {
              var response;
              response = data;
              return $.ajax({
                type: 'POST',
                global: false,
                url: Routing.generate('OpitNotesHolidayBundle_admin_list_holiday_dates'),
                data: {
                  "showList": 1
                }
              }).done(function(data) {
                var validationResult;
                $('#list-table').html(data);
                validationResult = $(document).data('notes').funcs.showAlert(response, "create", "Holiday date modified successfully");
                if (validationResult === true) {
                  return $('#dialog-editholidaydate').dialog("destroy");
                }
              });
            });
          },
          Close: function() {
            $('#dialog-editholidaydate').dialog("destroy");
          }
        }
      });
      return;
    });
  });

  $('#delete').click(function() {
    return deleteHolidayDate();
  });

  $('#list-table').on("click", ".delete-single-holidaydate", function() {
    var $checkbox;
    $checkbox = $(this).closest('tr').find(':checkbox');
    $checkbox.prop('checked', true);
    return deleteHolidayDate();
  });

  deleteHolidayDate = function() {
    var url;
    url = Routing.generate('OpitNotesHolidayBundle_admin_delete_holiday_date');
    return $(document).data('notes').funcs.deleteAction('Holiday date delete', 'holiday date(s)', url, '.list-delete-holidaydate');
  };

  $('#list-table').on("click", "th .fa-trash-o", function() {
    return $('.list-delete-holidaydate').filter(function() {
      return !this.disabled;
    }).checkAll();
  });

  inverse = false;

  $('form').on('click', '.fa-sort', function() {
    return inverse = $(document).data('notes').funcs.clientSideListOrdering($(this), inverse);
  });

  $('form').on('click', '.order-text', function() {
    return inverse = $(document).data('notes').funcs.clientSideListOrdering($(this).parent().find('i'), inverse);
  });

}).call(this);