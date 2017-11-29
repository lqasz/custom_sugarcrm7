({
  extendsFrom: 'RecordlistView',
  id: 'moduleTimeSheets',
  repairList: undefined,

  initialize: function (options) {
    app.view.invokeParent(this, {type: 'view', name: 'recordlist', method: 'initialize', args:[options]});
    this.context.on('list:export_csv:fire', this.export, this);
  },

  export: function() {
    var timeSheets = {},
        selector = 'AB_TimeSheetQS';

    var i = 0;
    $("tr[name^='"+selector+"']").each(function() {
      if($(this).find('input[name="check"]:checked').length > 0) {
        timeSheets[i] = $(this).attr('name').substring(selector.length+1).trim();
      }

      i++;
    });

    $.ajax({
      url: 'index.php?entryPoint=getData&exportTimeSheets=1&noCache='+ (new Date().getTime()),
      type: 'POST',
      data: {
        timeSheets: timeSheets,
      },
      success: function(data) {
        let csvContent = "data:text/csv;charset=utf-8,\ufeff";
        
        csvContent += "Imie i Nazwisko; Typ; Rekord; Wartość\r\n";
        _.each(JSON.parse(data), function(sheet, key) {
          csvContent += sheet.user_name +"; "+ sheet.parent_type +"; "+ sheet.parent_name +"; "+ sheet.value +"\r\n";
        });

        var encodedUri = encodeURI(csvContent);
        var link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "my_data.csv");
        document.body.appendChild(link);

        link.click();
      },
    }); // ajax
  },

  render: function() {
    this._super("render");
    
    if(app.user.id != "801c0c78-edc1-e54f-08c2-5407f786ce48") {
      $('a[name="qs_structure_button"]').hide();
    }
  },
})