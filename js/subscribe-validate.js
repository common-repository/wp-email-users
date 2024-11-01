function weu_submit_page(url) {

  var weu = jQuery.noConflict();

  weuSubemail = document.getElementById("weu_txt_email");

  weuSubname = document.getElementById("weu_txt_name");

  weuSubgroup = document.getElementById("weu_txt_group");

  var subemail = weuSubemail.value;

  var subname = weuSubname.value;

  var subList = weuSubgroup.value;

  var ajax_url = weu_widget_notices.weu_ajax_url;

    if( weuSubemail.value == "" ) {

        swal(weu_widget_notices.weu_email_notice);

        weuSubemail.focus();

        return false;    

    }

    if( weuSubemail.value!="" && ( weuSubemail.value.indexOf("@",0) == -1 || weuSubemail.value.indexOf(".",0) == -1 )) {

        swal(weu_widget_notices.weu_incorrect_email);

        weuSubemail.focus();

        weuSubemail.select();

        return false;

    }

      var data = {

        'action': 'weu_subscribe_users_nl',

        'sub_email' :subemail,

        'sub_name': subname,

        'sub_list': subList

      };

      weu.post(ajax_url,data, function(response){

            var congMsg = response.trim();

            if(congMsg==="success"){

              document.getElementById("weu_msg").innerHTML = weu_widget_notices.weu_success_message;

            }

            else if(congMsg=="exist"){

              document.getElementById("weu_msg").innerHTML = weu_widget_notices.weu_email_exists;//weuSubemail_exists;

            }

            else if(congMsg=="fail"){

              document.getElementById("weu_msg").innerHTML = weu_widget_notices.weu_error;

            }

      });

}