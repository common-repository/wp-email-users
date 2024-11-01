var weu = jQuery.noConflict();
weu(document).ready(function () {
  var nonce = wp_email_users._ajax_nonce;
  jQuery('[data-toggle="tooltip"]').tooltip();
  jQuery("#weu_send ").click(function () {
    var act = [];
    jQuery.each(jQuery("input[name='ea_user_group[]']:checked"), function () {
      var act = jQuery(this).val();
      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "get_group",
          nonce_ajax: nonce,
          act: act,
        },
        success: function (data) {
          console.log(data);
        },
      });
    });
  });
  weu(document).ready(function () {
    weu("#user_Bcc").DataTable();
  });
  var dataTable = weu("#Mail_user_table").DataTable({
    scrollY: "40vh",
    scrollCollapse: true,
    paging: false,
    columnDefs: [
      {
        orderable: false,
        className: "select-checkbox",
        targets: 0,
        checkboxes: {
          selectRow: true,
        },
      },
    ],
    select: {
      style: "multi",
      selector: "td:first-child",
    },
    order: [[1, "asc"]],
  });
  // Handle click on "Select all" control
  weu("#example-select-all").on("click", function () {
    // Check/uncheck all checkboxes in the table
    var rows = dataTable
      .rows({
        search: "applied",
      })
      .nodes();
    weu('input[type="checkbox"]', rows).prop("checked", this.checked);
  });
  // Handle click on checkbox to set state of "Select all" control
  weu("#example-select-all tbody").on(
    "change",
    'input[type="checkbox"]',
    function () {
      // If checkbox is not checked
      if (!this.checked) {
        var el = weu("#example-select-all").get(0);
        // If "Select all" control is checked and has 'indeterminate' property
        if (el && el.checked && "indeterminate" in el) {
          // Set visual state of "Select all" control
          // as 'indeterminate'
          el.indeterminate = true;
        }
      }
    }
  );
  var User_email_bcc_table = weu("#User_email_bcc_table").DataTable({
    scrollY: "50vh",
    scrollCollapse: true,
    paging: false,
  });
  var Group_table_mailbcc_field = weu("#list_bcc").DataTable({
    scrollY: "50vh",
    scrollCollapse: true,
    paging: false,
  });
  var group_table_bcc_field = weu("#group_bcc").DataTable({
    scrollY: "50vh",
    scrollCollapse: true,
    paging: false,
  }); // for table 1
  var List_table = weu("#list_user_table").DataTable({
    scrollY: "50vh",
    scrollCollapse: true,
    paging: false,
  });
  var User_table_autoresponder = weu("#User_autoresponder_table").DataTable({
    scrollY: "50vh",
    scrollCollapse: true,
    paging: false,
  });
  var table_list = weu("#example_temp").DataTable({
    lengthMenu: [10, 25, 50, 75, 100],
  });
  var Group_table_mail = weu(".data_list").DataTable({
    lengthMenu: [10, 25, 50, 75, 100],
  });
  var table3 = weu(".data_expo").DataTable({
    lengthMenu: [10, 25, 50, 75, 100],
    dom: "Bfrtip",
    buttons: [
      {
        extend: "copy",
        exportOptions: {
          columns: [1, 2],
        },
        text:
          'Copy <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>',
      },
      {
        extend: "csv",
        exportOptions: {
          columns: [1, 2],
        },
      },
      "excel",
      {
        extend: "print",
        exportOptions: {
          columns: [1, 2],
        },
      },
    ],
  }); // for table 3
  var Group_table_mail = weu("#example4").DataTable({
    scrollY: "50vh",
    scrollCollapse: true,
    paging: false,
  }); // for table 4
  var table5 = weu("#example5").DataTable({
    order: [[7, "desc"]],
    lengthMenu: [10, 25, 50, 75, 100],
  }); // for table 5
  weu("#table-1").tableDnD();
  weu("#table-2 tr:even").addClass("alt");
  // Initialise the second table specifying a dragClass and an onDrop function that will display an alert
  weu("#table-2").tableDnD({
    onDragClass: "myDragClass",
    onDrop: function (table, row) {
      var rows = table.tBodies[0].rows;
      if (row.id == "") {
        swal(
          {
            title: "Please enable SMTP you have chosen to set priority!",
            text: "",
            type: "error",
          },
          function () {
            location.reload();
          }
        );
      } else {
        var debugStr = "Row dropped was " + row.id + ". New order: ";
        for (var i = 0; i < rows.length; i++) {
          debugStr += rows[i].id + " ";
        }
        weu("#debugArea").html(debugStr);
        var change_priority = weu.tableDnD.serialize();
        var data = {
          data_raw: change_priority,
          action: "weu_smtp_priority_action_1",
          nonce_ajax: nonce,
        };
        weu.post(ajaxurl, data, function (response) {
          swal(
            {
              title: "Priority updated successfully!",
              text: "",
              type: "success",
            },
            function () {
              location.reload();
            }
          );
        });
      }
    },
    onDragStart: function (table, row) {
      weu("#debugArea").html("Started dragging row " + row.id);
    },
  });
  var table5 = weu("#table12").DataTable({
    lengthMenu: [10, 25, 50, 75, 100],
  }); //for table 12
  weu("#list_user_table_wrapper").hide();
  weu("#group_bcc_wrapper").hide();
  weu("#list_bcc_wrapper").hide();
  weu("#example4_wrapper").hide();
  weu(".wau_user_toggle1").hide();
  weu(".group_toggle").hide();
  weu(".group_toggle_bcc").hide();
  weu(".list_bcc").hide();
  weu("#example-select-all").change(function () {
    weu(".checkbox").prop("checked", weu(this).prop("checked"));
  });
  weu(".checkbox").change(function () {
    if (false == weu(this).prop("checked")) {
      weu("#example-select-all").prop("checked", false);
    }
    if (weu(".checkbox:checked").length == weu(".checkbox").length) {
      weu("#example-select-all").prop("checked", true);
    }
  });
  weu("#example-select-all_bcc").change(function () {
    weu(".checkbox_bcc").prop("checked", weu(this).prop("checked"));
  });
  weu(".checkbox_bcc").change(function () {
    if (false == weu(this).prop("checked")) {
      weu("#example-select-all_bcc").prop("checked", false);
    }
    if (weu(".checkbox_bcc:checked").length == weu(".checkbox").length) {
      weu("#example-select-all_bcc").prop("checked", true);
    }
  });
  weu("#example-csv-select-all_bcc").change(function () {
    weu(".checkbox_list").prop("checked", weu(this).prop("checked"));
  });
  weu(".checkbox_list").change(function () {
    if (false === weu(this).prop("checked")) {
      weu("#example-csv-select-all_bcc").prop("checked", false);
    }
    if (weu(".checkbox_list:checked").length == weu(".checkbox_list").length) {
      weu("#example-csv-select-all_bcc").prop("checked", true);
    }
  });

  weu("#example-csv-select-all").change(function () {
    weu(".checkbox1").prop("checked", weu(this).prop("checked"));
  });
  weu(".checkbox1").change(function () {
    if (false === weu(this).prop("checked")) {
      weu("#example-csv-select-all").prop("checked", false);
    }
    if (weu(".checkbox1:checked").length == weu(".checkbox1").length) {
      weu("#example-csv-select-all").prop("checked", true);
    }
  });

  weu(document).on("change", "#example-responder", function () {
    weu(".select-all_auto").prop("checked", weu(this).prop("checked"));
  });
  weu(document).on("change", ".select-all_auto", function () {
    if (false === weu(this).prop("checked")) {
      weu("#example-csv-select-all").prop("checked", false);
    }
    if (
      weu(".select-all_auto:checked").length == weu(".select-all_auto").length
    ) {
      weu("#example-responder").prop("checked", true);
    }
  });

  weu("#example-group-select-all").change(function () {
    weu(".checkbox2").prop("checked", weu(this).prop("checked"));
  });
  weu(".checkbox2").change(function () {
    if (false === weu(this).prop("checked")) {
      weu("#example-group-select-all").prop("checked", false);
    }
    if (weu(".checkbox2:checked").length == weu(".checkbox2").length) {
      weu("#example-group-select-all").prop("checked", true);
    }
  });
  weu("#example-group-select-all_bcc").change(function () {
    weu(".checkbox3").prop("checked", weu(this).prop("checked"));
  });
  weu(".checkbox3").change(function () {
    if (false === weu(this).prop("checked")) {
      weu("#example-group-select-all_bcc").prop("checked", false);
    }
    if (weu(".checkbox3:checked").length == weu(".checkbox2").length) {
      weu("#example-group-select-all_bcc").prop("checked", true);
    }
  });
  //csvlist
  weu("#example-select-all-import").on("click", function () {
    // Check/uncheck all checkboxes in the table
    var rows_csvlist = table2
      .rows({
        search: "applied",
      })
      .nodes();
    weu('input[type="checkbox"]', rows_csvlist).prop("checked", this.checked);
  });
  weu("#example-select-all-export").on("click", function () {
    var rows_csvlist = table3
      .rows({
        search: "applied",
      })
      .nodes();
    weu('input[type="checkbox"]', rows_csvlist).prop("checked", this.checked);
  });
  weu("#example-responder").on("click", function () {
    var rows_responder = Group_table_mail.rows({
      search: "applied",
    }).nodes();
    weu('input[type="checkbox"]', rows_responder).prop("checked", this.checked);
  });
  // conformation prompt
  weu(".delete-email-indi").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "This will also delete subscribers associated with this list!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Deleted!",
              text: "List has been successfully deleted!",
              type: "success",
            },
            function () {
              weu(target).unbind("click").click();
              var CurrList = weu(this).val();
              weu("#delete-email-indi").val(CurrList);
              weu(target).parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your List is safe!", "error");
        }
      }
    );
  });
  weu(".delete-smtp-conf").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "You will not be able to recover this SMTP again!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Deleted!",
              text: "SMTP has been successfully deleted!",
              type: "success",
            },
            function () {
              weu(".delete-smtp-conf").unbind("click").click();
              weu(target).parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your SMTP configuration is safe!", "error");
        }
      }
    );
  });
  weu(".delete-temp-conf").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "You will not be able to recover this Template again!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Deleted!",
              text: "Template has been successfully deleted!",
              type: "success",
            },
            function () {
              weu(".delete-temp-conf").unbind("click").click();
              weu(target).parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your Template is safe!", "error");
        }
      }
    );
  });
  weu(".delete-sent-mail").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "You will not be able to recover this mail!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Deleted!",
              text: "Sent mail has been successfully deleted!",
              type: "success",
            },
            function () {
              weu(".delete-sent-mail").unbind("click").click();
              weu(target).parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your sent mail is safe!", "error");
        }
      }
    );
  });
  weu(".edit-smtp-conf").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "Do you really want to edit this SMTP Configuration?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Edited for you!",
              text: "SMTP has been successfully edited!",
              type: "success",
            },
            function () {
              weu(".edit-smtp-conf").unbind("click");
              weu(target).parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your SMTP configuration is safe!", "error");
        }
      }
    );
  });
  weu(".edit-smtp-disable").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "Do you really want to disable SMTP?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Status changed!",
              text: "SMTP has been successfully disabled!",
              type: "success",
            },
            function () {
              weu(".edit-smtp-disable").unbind("click").click();
              weu(target).parent().parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your SMTP status is safe!", "error");
        }
      }
    );
  });
  weu(".edit-smtp-enable").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "Do you really want to enable SMTP?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Status changed!",
              text: "SMTP has been successfully enabled!",
              type: "success",
            },
            function () {
              weu(".edit-smtp-enable").unbind("click").click();
              weu(target).parent().parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Your SMTP status is safe!", "error");
        }
      }
    );
  });
  // conformation prompt
  weu(".delete-member-indi").on("click", function (e) {
    var target = weu(this);
    e.preventDefault();
    swal(
      {
        title: "Are you sure?",
        text: "Do you really want to delete member?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, I am sure!",
        cancelButtonText: "No, cancel it!",
        closeOnConfirm: false,
        closeOnCancel: false,
      },
      function (isConfirm) {
        if (isConfirm) {
          swal(
            {
              title: "Deleted!",
              text: "Member has been successfully deleted!",
              type: "success",
            },
            function () {
              weu(target).unbind("click").click();
              weu(target).parent().submit();
              return true;
            }
          );
        } else {
          swal("Cancelled", "Congrats! The member is safe in list", "error");
        }
      }
    );
  });

  function isUrl(s) {
    var regexp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
    return regexp.test(s);
  }
  //for wp email user
  weu("#wau_template").change(function () {
    var filename_id = weu("#wau_template").val();
    var TemplateName = weu(this).val();

    setTimeout(function () {
      jQuery(".mce-panel").trigger("click");
      tinymce.activeEditor.setContent(filename_id);
    }, 500);
  });
  // for template page
  weu("#wau_template_single").change(function () {
    var filename_id = weu("#wau_template_single").val();
    var TemplateName = weu(this).val();

    setTimeout(function () {
      jQuery(".mce-panel").trigger("click");
      tinymce.activeEditor.setContent(filename_id);
    }, 500);
  });
  /*for Autoresponder Page Template Send Email for */
  weu(".select-all").removeAttr("checked");
  weu("#email_role").click(function () {
    var filename_id = weu("#email_role").val();
    if (
      filename_id == "5-User Role Changed" ||
      filename_id == "4-Password Reset"
    ) {
      weu("#drop_hide").hide();
      weu("#User_autoresponder_table_wrapper").hide();
      weu("#wau_user_responder").hide();
    } else {
      jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "weu_autoresponder_selected_user",
          filename_id: filename_id,
          nonce_ajax: nonce,
        },
        success: function (data) {
          weu("#User_autoresponder_table").html(data);
          User_table_autoresponder.draw();
        },
        error: function (data) {
          alert("error2");
        },
      });
      jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: {
          action: "weu_autoresponder_selected_user_role",
          filename_id: filename_id,
          nonce_ajax: nonce,
        },
        success: function (data) {
          weu("#wau_role").html(data);
        },
        error: function (data) {
          alert("error1");
        },
      });
      weu("#drop_hide").show();
      weu("#User_autoresponder_table_wrapper").show();
    }
  });
  //});
  /*--End---*/
  /*-- retreive already set users for autoresponder  --*/
  weu(".mail_for").change(function () {
    weu(".alluser_datatable tbody tr input[type=checkbox]").each(function () {
      weu(this).prop("checked", false);
    });
    var template_id = this.value;
    var temp_id = template_id.split("-", 1);
    var security = weu("#bk-ajax-nonce").val();

    var data = {
      data_raw: temp_id,
      nonce: security,
      action: "weu_selected_users_1",
    };
    weu.post(ajaxurl, data, function (response) {
      var users_new = weu.parseJSON(response);
      weu(".alluser_datatable tbody tr input[type=checkbox]").each(function () {
        var id = weu(this).val();
        if (users_new.indexOf(id) != -1) {
          weu(this).prop("checked", true);
        }
      });
    });
    var data_temp = {
      data_raw: temp_id,
      nonce: security,
      action: "weu_selected_users_temp",
    };
    weu.post(ajaxurl, data_temp, function (data_temp) {
      tinyMCE.activeEditor.setContent(data_temp);
    });

    var data_sub = {
      data_raw: temp_id,
      nonce: security,
      action: "weu_selected_users_sub",
    };
    weu.post(ajaxurl, data_sub, function (subject) {
      weu("#weu_temp_sub").val(subject);
    });
  });

  // for template delete
  weu("#weu_delete_template").click(function () {
    var filename_id = weu("#wau_template_single").val();
    var data = {
      temp_key: "template",
      action: "weu_my_action",
      temp_del_key: "delete_temp",
      filetitle: filename_id,
      nonce_ajax: nonce,
    };
    weu.post(ajaxurl, data, function (response) {
      tinyMCE.activeEditor.setContent(response);
    });
  });
});

function check_sent_email1() {
  var group = document.form1.sent_mail_del;
  var $checkboxes = weu('#delete_mail tr input[type="checkbox"]');
  $checkboxes.change(function () {
    var countCheckedCheckboxes = $checkboxes.filter(":checked").length;
    if (countCheckedCheckboxes >= 1) {
      weu("#show-delete-button").show();
    } else {
      weu("#show-delete-button").hide();
    }
  });
}

function check_sent_email() {
  var group = document.form1.sent_mail_del;
  var $checkboxes = weu('#delete_mail td input[type="checkbox"]');
  $checkboxes.change(function () {
    var countCheckedCheckboxes = $checkboxes.filter(":checked").length;
    if (countCheckedCheckboxes >= 1) {
      weu("#show-delete-button").show();
    } else {
      weu("#show-delete-button").hide();
    }
  });
}

function checkmail() {
  var group = document.wau_smtp_form.weu_smtp_status;
  for (var i = 0; i < group.length; i++) {
    if (group[i].checked) break;
  }
  if (i == group.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu("#smtp_enable").show();
    weu("#smtp_enable1").show();
    weu("#smtp_enable2").show();
    weu("#smtp_enable3").show();
    weu("#smtp_enable4").show();
    weu("#smtp_enable5").show();
    weu("#smtp_enable6").show();
    weu("#smtp_enable7").show();
    //weu('#temp_name_req').prop('required', false);
    return false;
  } else {
    weu("#smtp_enable").hide();
    weu("#smtp_enable1").hide();
    weu("#smtp_enable2").hide();
    weu("#smtp_enable3").hide();
    weu("#smtp_enable4").hide();
    weu("#smtp_enable5").hide();
    weu("#smtp_enable6").hide();
    weu("#smtp_enable7").hide();
  }
}

function checkFunction() {
  var group = document.myform.toggler;
  for (var i = 0; i < group.length; i++) {
    if (group[i].checked) break;
  }
  if (i == group.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu("#blk-1").show();
    weu("#blk-2").hide();
    weu("#blk-3").hide();
    weu("#save_temp").val("1");
    //weu('#temp_name_req').prop('required', false);
    return false;
  } else if (radio_value == 2) {
    weu("#blk-1").hide();
    weu("#blk-2").show();
    weu("#blk-3").hide();
    weu("#save_temp").val("2");

    setTimeout(function () {
      tinymce.activeEditor.setContent("");
    }, 500);
    //weu('#temp_name_req').prop('required', true);
  } else {
    weu("#blk-1").hide();
    weu("#blk-2").hide();
    weu("#blk-3").show();
    //weu('#temp_name_req').prop('required', false);
    weu("#save_temp").val("3");
    weu("#sub_valid").val("");

    setTimeout(function () {
      tinymce.activeEditor.setContent("");
    }, 500);
  }
}
weu(document).ready(function () {
  weu(".dt-buttons").prepend("<span class='export-text'>Export To: </span>");
});

function weu_smtp_enable() {
  weu("form12").submit();
}

function radioFunction() {
  var group = document.myform.rbtn;
  for (var i = 0; i < group.length; i++) {
    if (group[i].checked) break;
  }
  if (i == group.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu("#example4_wrapper").show();
    weu(".group_toggle").hide();
    weu(".wau_user_toggle").show();
    weu("#Mail_user_table_wrapper").show();
    weu("#list_user_table_wrapper").hide();
    weu("#wau_user_role").hide();
    weu("#nickname").show();
    weu("#lname").show();
    weu("#cron").show();
    weu("#dname").show();
    weu("#slink").show();
    weu(".example").hide();
    weu("#list_bcc_wrapper").hide();
    weu(".wau_user_toggle1").hide();
    weu("#User_email_bcc_table_wrapper").hide();
    weu("#wau_user_role1").hide();
    weu("#group_bcc_wrapper").hide();
    // return false;
  } else if (radio_value == 2) {
    weu(".group_toggle").hide();
    weu(".wau_user_toggle").hide();
    weu("#wau_user_role").show();
    weu("#nickname").show();
    weu("#lname").show();
    weu("#cron").hide();
    weu("#dname").show();
    weu("#slink").show();
    weu("#list_bcc_wrapper").hide();
    weu(".wau_user_toggle1").hide();
    weu("#User_email_bcc_table_wrapper").hide();
    weu("#list_user_table_wrapper").hide();
    weu("#wau_user_role1").hide();
    weu(".example").hide();
    weu("#group_bcc_wrapper").hide();
  } else if (radio_value == 3) {
    weu(".group_toggle").hide();
    weu(".example4_wrapper").hide();
    weu("#Mail_user_table_wrapper").hide();
    weu("#wau_user_role").hide();
    weu(".wau_user_toggle").show();
    weu("#list_user_table_wrapper").show();
    weu("#nickname").hide();
    weu("#cron").hide();
    weu("#lname").hide();
    weu("#dname").hide();
    weu("#slink").hide();
    weu(".list_bcc").hide();
    weu("#list_bcc_wrapper").hide();
    weu("#wau_user_role1").hide();
    weu("#group_bcc_wrapper").hide();
    weu("#User_email_bcc_table_wrapper").hide();
  } else if (radio_value == 4) {
    weu(".group_toggle").show();
    weu("#example4_wrapper").show();
    weu("#wau_user_role").hide();
    weu("#Mail_user_table_wrapper").hide();
    weu(".wau_user_toggle").hide();
    weu("#list_user_table_wrapper").hide();
    weu("#nickname").hide();
    weu("#lname").hide();
    weu("#cron").hide();
    weu("#dname").hide();
    weu("#slink").hide();
    weu("#list_bcc_wrapper").hide();
    weu(".wau_user_toggle1").hide();
    weu("#User_email_bcc_table_wrapper").hide();
    weu("#wau_user_role1").hide();
    weu(".Mail_user_table").hide();
    weu("#group_bcc_wrapper").hide();
  }
}

function radioFunction1() {
  var group = document.myform.rbtn1;
  for (var i = 0; i < group.length; i++) {
    if (group[i].checked) break;
  }
  if (i == group.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu("#list_bcc_wrapper").hide();
    weu(".wau_user_toggle1").show();
    weu("#User_email_bcc_table_wrapper").show();
    weu("#list_user_table_wrapper").hide();
    weu("#wau_user_role1").hide();
    weu("#nickname").show();
    weu("#lname").show();
    weu("#dname").show();
    weu("#slink").show();
    weu(".Mail_user_table").hide();
    weu(".group_toggle").hide();
    weu(".wau_user_toggle").hide();
    weu("#wau_user_role").hide();
    weu("#group_bcc_wrapper").hide();
    // return false;
  } else if (radio_value == 2) {
    weu(".wau_user_toggle_1").hide();
    weu(".wau_user_toggle1").hide();
    weu("#User_email_bcc_table_wrapper").hide();
    weu(".group_toggle_bcc").hide();
    weu("#list_bcc_wrapper").hide();
    weu(".list_bcc").hide();
    weu("#wau_user_role1").show();
    weu("#nickname").show();
    weu("#lname").show();
    weu("#dname").show();
    weu("#slink").show();
    weu("#group_bcc_wrapper").hide();
    weu(".group_toggle").hide();
    weu(".wau_user_toggle").hide();
    weu("#wau_user_role").hide();
  } else if (radio_value == 3) {
    weu(".wau_user_toggle_1").hide();
    weu("#User_email_bcc_table_wrapper").hide();
    weu("#wau_user_role1").hide();
    weu(".list_bcc").show();
    weu("#list_bcc_wrapper").show();
    weu("#nickname").hide();
    weu("#lname").hide();
    weu("#dname").hide();
    weu("#slink").hide();
    weu(".group_toggle_bcc").hide();
    weu("#example4_wrapper_bcc").hide();
    weu(".group_toggle").hide();
    weu(".wau_user_toggle").hide();
    weu("#wau_user_role").hide();
    weu("#group_bcc_wrapper").hide();
  } else if (radio_value == 4) {
    weu(".group_toggle_bcc").show();
    weu(".list_bcc").hide();
    weu(".wau_user_toggle_1").hide();
    weu("#group_bcc_wrapper").show();
    weu("#wau_user_role").hide();
    weu("#list_bcc_wrapper").hide();
    weu("#User_email_bcc_table_wrapper").hide();
    weu("#list_user_table_wrapper").hide();
    weu("#nickname").hide();
    weu("#lname").hide();
    weu("#dname").hide();
    weu("#slink").hide();
    weu(".group_toggle").hide();
    weu(".wau_user_toggle").hide();
  }
}
/*csv Page*/
function radioFunction_csv() {
  weu("#wau_role_csv").hide();
  var group_csv = document.export_form.rbtn_csv;
  for (var i = 0; i < group_csv.length; i++) {
    if (group_csv[i].checked) break;
  }
  if (i == group_csv.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu("#example3_wrapper").show();
    return false;
  } else if (radio_value == 2) {
    weu("#example3_wrapper").hide();
    weu("#wau_role_csv").show();
  }
}
/*Autoresponder Email Page Radio Button function*/
function radioFunction_responder() {
  weu("#wau_user_responder").hide();
  var group_csv = document.autoresponder.rbtn_respond;
  for (var i = 0; i < group_csv.length; i++) {
    if (group_csv[i].checked) break;
  }
  if (i == group_csv.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu(".wau_user_toggle").show();
    return false;
  } else if (radio_value == 2) {
    weu(".wau_user_toggle").hide();
    weu("#wau_user_responder").show();
  }
}
/*----------End-------------*/
/* onsubmit validate function-main Page  */
function myFunction2() {
  if (document.layers || document.getElementById || document.all)
    return checkname();
  else document.getElementById("errfn").innerHTML = "";
  return true;
}
var testresults3;

function checkname() {
  var str = document.myform.wau_from_name.value;
  if (str != "") {
    // document.getElementById('errfn').innerHTML = "";
    weu("#wau_from_name").css("border-color", "#80ffdf");
    testresults = true;
  } else {
    // $(":button").hide();
    swal("Empty From Name", "Please enter From Name !", "error");
    weu("#wau_from_name").css("border-color", "red");
    testresults3 = false;
  }
  return testresults3;
}
var testresults;

function checkemail() {
  var str = document.myform.wau_from_email.value;
  var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
  if (filter.test(str)) {
    // document.getElementById('errfn').innerHTML = "";
    weu("#wau_from").css("border-color", "#80ffdf");
    testresults = true;
  } else {
    //weu('#weu_send').hide();
    swal("Wrong Email", "Please enter a valid email address!", "error");
    weu("#wau_from").css("border-color", "red");
    testresults = false;
  }
  return testresults;
}
weu("#sub_valid").on("input", function () {
  var input = weu(this);
  var is_subject = input.val();
  if (is_subject) {
    input.removeClass("invalid").addClass("valid");
  } else {
    input.removeClass("valid").addClass("invalid");
  }
});

function focus_function() {
  var wau_smtp_status1 = document.getElementById("wau_smtp_status12");
  if (wau_smtp_status1.value == "no") {
    weu("#wau_smtp_form input").attr("readonly", true);
    weu("#wau_smtp_form input[type=radio]").attr("disabled", true);
    swal(
      "Wait",
      "Enable SMTP Settings From Setting Page To Use SMTP Configurations!",
      "error"
    );
    return false;
  }
}

function myFunction() {
  if (document.layers || document.getElementById || document.all)
    return checkemail();
  else document.getElementById("errfn").innerHTML = "";

  return true;
}
var testresults1;

function checkemail2() {
  var str = document.wau_smtp_form.weu_smtp_mail.value;
  // var Name_sender = document.forms["myform"]["wau_from_name"].value;
  var filter = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
  if (filter.test(str) && Name_sender != "") {
    document.getElementById("errfn").innerHTML = "";
    testresults1 = true;
  } else {
    swal("Wrong Email", "Please enter a valid email address!", "error");
    //    setTimeout(function(){},2000);
    //
    testresults1 = false;
  }
  return testresults1;
}

function validation_smtp() {
  if (document.layers || document.getElementById || document.all)
    return checkemail2();
  else document.getElementById("errfn").innerHTML = "";
  // return true;
  var wau_smtp_from_name = document.getElementById("weu_smtp_name");
  if (wau_smtp_from_name.value == "") {
    swal("From Name Missing.", "Please Enter Name!", "error");
    return false;
  }
  var wau_smtp_host = document.getElementById("weu_smtp_host");
  if (wau_smtp_host.value == "") {
    swal("Host Is Missing.", "Please Enter Host!", "error");
    return false;
  }
  var wau_port = document.getElementById("wau_port");
  if (wau_port.value == "") {
    swal("Port Is Missing.", "Please Enter Port!", "error");
    return false;
  }
  var wau_uname = document.getElementById("wau_uname");
  if (wau_uname.value == "") {
    swal("User Name Is Missing.", "Please Enter User Name!", "error");
    return false;
  }
  var wau_pass = document.getElementById("wau_pass");
  if (wau_pass.value == "") {
    swal("Password Is Missing.", "Please Enter Password!", "error");
    return false;
  }
  var wau_limit = document.getElementById("wau_limit");
  if (wau_limit.value == "") {
    swal(
      "Daily Limit Is Missing.",
      "Please enter daily limit that you want to send through this configuration!",
      "error"
    );
    return false;
  }
}

function validation_list() {
  if (document.list_form.new_list_name.value == "") {
    document.getElementById("errors").innerHTML = "Please enter a list name!";
    return false;
  }
}

function validation_member() {
  var letters = /[a-zA-Z][^#&<>\"~;$^%{}?]{1,20}$/;
  if (member_name.value.match(letters)) {
  } else {
    document.getElementById("errors").innerHTML =
      "Please enter valid Member Name!";
    return false;
  }
  if (document.add_member_form.member_name.value == "") {
    document.getElementById("errors").innerHTML =
      "Please enter valid Member Name!";
    return false;
  } else if (document.add_member_form.member_email.value == "") {
    document.getElementById("errors").innerHTML =
      "Please enter a Member Email!";
    return false;
  } else {
    var mailformat = /^[a-z0-9](\.?[a-z0-9_-]){0,}@[a-z0-9-]+\.([a-z]{1,6}\.)?[a-z]{2,6}$/;
    if (document.add_member_form.member_email.value.match(mailformat)) {
      return true;
    } else {
      document.getElementById("errors").innerHTML =
        "Please enter a valid Member Email!";
      return false;
    }
  }
}
weu(function () {
  weu("li").click(function () {
    weu("li").removeClass("active");
    weu(this).addClass("active");
  });
});

function openCity(cityName) {
  var i;
  var x = document.getElementsByClassName("city");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";
  }

  document.getElementById(cityName).style.display = "block";
}

function validation() {
  if (weu("#r_role").prop("checked") == false) {
    var flag_checkbox_checked = 0;
    weu("input[name='ea_user_name[]']").each(function () {
      if (weu(this).prop("checked") == true) {
        flag_checkbox_checked = 1;
      }
    });
    weu("input[name='csv_file_name[]']").each(function () {
      if (weu(this).prop("checked") == true) {
        flag_checkbox_checked = 1;
      }
    });
    weu("input[name='ea_user_group[]']").each(function () {
      if (weu(this).prop("checked") == true) {
        flag_checkbox_checked = 1;
      }
    });
    if (flag_checkbox_checked != 1) {
      swal("Please Choose 'Send Email To' Field!", "", "error");
      return false;
    }
  } else if (weu("#r_role").prop("checked") == true) {
    weu("select[name='user_role[]'] option:selected").each(function () {
      if (weu(this).prop("disabled") == true) {
        swal("Please Choose 'Send Email To' Field!", "", "error");

        return false;
      }
    });
  }

  var radio_val = weu("input[name=toggler]:checked", "#myForm").val();
  if (radio_val == 2) {
    var wau_template = document.getElementById("wau_template");
    if (wau_template.value == "") {
      setTimeout(function () {
        swal(
          "Email Template Missing.",
          "Please choose email template!",
          "error"
        );
      }, 2000);
      return false;
    }
  } else if (radio_val == 1) {
    var temp_name_req = document.getElementById("temp_name_req");
    if (temp_name_req.value == "") {
      swal("Template Name Missing.", "Please enter template name!", "error");
      return false;
    }
  }
  var cron = document.getElementById("cron_btn");
  if (cron_btn.value == "" && weu("#user_role").prop("checked") == true) {
    swal("Cron Value Empty", "Please choose cron number!", "error");
    weu("#cron_btn").css("border-color", "red");
    return false;
  }

  /*-------------------Role-------------------*/
  var role_val = weu("#r_role").prop("checked");
  var wau_role = weu("#wau_role option:selected").index();
  if (role_val == true && wau_role == 0) {
    swal("No Role Chosen", "Please choose atleast oneUser role!", "error");
    return false;
  }
  /*-------------------End role-------------------*/
  /*-------------------List-------------------*/
  var wau_list = weu("#check_list").prop("checked");
  var csv_val = weu(".chk_list:checked").index();
  if (csv_val == -1 && wau_list == true) {
    swal(
      "No List Chosen",
      "Please choose atleast one Subscribers list!",
      "error"
    );
    return false;
  }
  /*-------------------End List-------------------*/
  var x = document.forms["myform"]["wau_sub"].value;
  if (x == "") {
    swal("No Subject", "Please fill subject!", "error");
    return false;
  }
}
/* Autoresponder Email send Page */
function validation_responder() {
  var user_val = weu("#user_role_email").prop("checked");
  var chk_val = weu(".select-all:checked").index();
  if (chk_val == -1 && user_val == true) {
  }
  var role_val = weu("#r_role_email").prop("checked");
  var wau_role = weu("#wau_role option:selected").index();
  if (role_val == true && wau_role == 0) {
  }
}

function checktemplate() {
  var group = document.myform.template_radio;
  for (var i = 0; i < group.length; i++) {
    if (group[i].checked) break;
  }
  if (i == group.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    weu("#old_template").hide();
    weu("#save_as_new").hide();
    //var message = 'content';
    //tinymce.get(#msg).setContent('');

    setTimeout(function () {
      tinymce.activeEditor.setContent("");
    }, 500);
    weu("#temp_name_id").show();
    document.getElementById("weu_temp_name").disabled = false;
  } else if (radio_value == 2) {
    weu("#old_template").show();
    weu("#save_as_new").show();
  }
}

function check_new_template() {
  var group = document.myform.new_template;
  for (var i = 0; i < group.length; i++) {
    if (group[i].checked) break;
  }
  if (i == group.length) return swal("No radio button is checked");
  var radio_value = i + 1;
  if (radio_value == 1) {
    document.getElementById("weu_temp_name").disabled = false;
    weu("#temp_name_id").show();
  } else if (radio_value == 2) {
    document.getElementById("weu_temp_name").disabled = true;
    weu("#temp_name_id").hide();
    //swal("Existing template will be updated after save. Template name field disabled!");
  }
}

function validation_template_manager() {
  var x = document.forms["myform"]["template_radio"].value;
  if (x == "2") {
    var message = document.forms["myform"]["weu_show_area"].value;
    var message = tinyMCE.get("weu_show_area").getContent();
    var temp = document.forms["myform"]["template_id"].value;
    if (temp == "") {
      swal("No Template Selected", "Please select template!", "error");
      return false;
    }
    var new_template = document.forms["myform"]["new_template"].value;
    if (new_template == 1) {
      var temp_name = document.forms["myform"]["wau_temp"].value;
      if (temp_name == "") {
        swal("No Template Name", "Please enter template name!", "error");
        return false;
      }
      var message = tinyMCE.get("weu_show_area").getContent();
      if (message == "") {
        swal("No Content", "Please enter content for template!", "error");
        return false;
      }
    } else {
      var message = tinyMCE.get("weu_show_area").getContent();
      if (message == "") {
        swal("No Content", "Please enter content for template!", "error");
        return false;
      }
    }
  } else {
    var temp_name = document.forms["myform"]["wau_temp"].value;
    if (temp_name == "") {
      swal("No Template Name", "Please enter template name!", "error");
      return false;
    }
    var message = tinyMCE.get("weu_show_area").getContent();
    if (message == "") {
      swal("No Content", "Please enter content for template!", "error");
      return false;
    }
  }
}

function jsFunction() {
  weu("#wau_template_single").change(function () {
    var template_id = weu(":selected", this).data("id");
    weu(".criteria_rate").val(template_id);
    var data = {
      template_id: template_id,
      action: "weu_send_mail_subject_temp_page",
      // 'nonce_ajax': nonce,
    };
    weu.post(ajaxurl, data, function (response) {
      weu("#weu_temp_sub").val(response);
    });
  });
}
weu(function () {
  //----- OPEN
  weu("[data-popup-open]").on("click", function (e) {
    var targeted_popup_class = jQuery(this).attr("data-popup-open");
    weu('[data-popup="' + targeted_popup_class + '"]').fadeIn(350);
    e.preventDefault();
  });
  //----- CLOSE
  weu("[data-popup-close]").on("click", function (e) {
    var targeted_popup_class = jQuery(this).attr("data-popup-close");
    weu('[data-popup="' + targeted_popup_class + '"]').fadeOut(350);
    e.preventDefault();
  });
  weu(".popup").on("click", function (e) {
    this.style.setProperty("display", "none", "important");
    e.preventDefault();
  });
});

function popup_template(attr) {
  jQuery.ajax({
    type: "POST",
    url: ajaxurl,
    data: {
      action: "tempFunction",
      newValue: attr,
      // 'nonce_ajax': nonce,
    },
    success: function (data) {
      weu("#template_value").html(data);
    },
    error: function (data) {
      alert("error");
    },
  });
}

function onchangeload() {
  weu("#wau_template").change(function () {
    var template_id = weu(":selected", this).data("id");
    weu(".criteria_rate").val(template_id);
    var data = {
      template_id: template_id,
      action: "weu_send_mail_subject",
      // 'nonce_ajax': nonce,
    };
    weu.post(ajaxurl, data, function (response) {
      weu("#sub_valid").val(response);
    });
  });
}

function validation_auto() {
  var wau_template_single = document.getElementById("email_role");
  if (
    !(
      wau_template_single.value == "4-Password Reset" ||
      wau_template_single.value == "5-User Role Changed"
    )
  ) {
    if (weu("#r_role_email").prop("checked") == false) {
      var flag_checkbox_checked = 0;
      weu("input[name='ea_user_name[]']").each(function () {
        if (weu(this).prop("checked") == true) {
          flag_checkbox_checked = 1;
        }
      });
      weu("input[name='ea_user_group[]']").each(function () {
        if (weu(this).prop("checked") == true) {
          flag_checkbox_checked = 1;
        }
      });
      if (flag_checkbox_checked != 1) {
        swal("Please Choose Send Email To Field!", "", "error");
        return false;
      }
    } else if (weu("#r_role_email").prop("checked") == true) {
      weu("select[name='user_role[]'] option:selected").each(function () {
        if (weu(this).prop("disabled") == true) {
          swal("Please Choose 'Send Email To' Field!!", "", "error");
          return false;
        }
      });
    }
  }
  var wau_template_single = document.getElementById("email_role");
  if (wau_template_single.value == "") {
    swal("Empty Send Email For .", "Please choose Send Email For!", "error");
    return false;
  }
  var radio_val = weu(
    "input[name=wau_template_single:checked",
    "#wau-template-selector"
  ).val();
  if (radio_val == 2) {
    var wau_template = document.getElementById("wau_template_single");
    if (wau_template.value == "") {
      swal("Email Template Missing.", "Please choose email template!", "error");
      return false;
    }
  } else if (radio_val == 1) {
    var temp_name_req = document.getElementById("temp_name_req");
    if (temp_name_req.value == "") {
      swal("Template Name Missing.", "Please enter template name!", "error");
      return false;
    }
  }
  var wau_template_single = document.getElementById("wau_template_single");

  if (wau_template_single.value == "") {
    swal("Email Template Missing.", "Please choose email template!", "error");
    return false;
  }
  var x = document.getElementById("weu_temp_sub");
  if (x.value == "") {
    swal("No Subject", "Please fill subject!", "error");
    return false;
  }
}
/*end*/
