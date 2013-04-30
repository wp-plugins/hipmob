jQuery(function($) {
    var sel = jQuery("#id_hipmob_theme");
    var sync_theme_settings = function(){
	if(sel.val() == 'gmail'){
	    jQuery("#id_hipmob_tab_background_color").val("#222222");
	    jQuery("#id_hipmob_tab_text_color").val("#FFFFFF");
	}else if(sel.val() == 'fb'){
	    jQuery("#id_hipmob_tab_background_color").val("#EBEEF3");
	    jQuery("#id_hipmob_tab_text_color").val("#333333");
	}else if(sel.val() == 'fbactive'){
	    jQuery("#id_hipmob_tab_background_color").val("#6e87b1");
	    jQuery("#id_hipmob_tab_text_color").val("#FFFFFF");
	}else{
	    jQuery("#id_hipmob_tab_background_color").val("");
	    jQuery("#id_hipmob_tab_text_color").val("");
	}
    };
    sel.on('change', sync_theme_settings);
    sync_theme_settings();       

    var dlg = false;
    
    var close_dialog = function()
    {
        dlg.dialog('close');
        dlg.dialog('destroy');
	dlg = false;
    };

    var use_app_id = function(appid)
    {
	if(dlg) setTimeout(close_dialog, 5000);
	jQuery("#id_hipmob_app_id").val(appid);
    };

    // waits for the hipmob object to be defined
    var linktarget = false;
    var complete_link = function()
    {
	if(window.hipmob){
	    if('on' in window.hipmob){
		window.hipmob.on('jsonmessagereceived', function(self, from, data, props){
		    if('linktarget' in data){
			linktarget = data.linktarget;
			btn.show();
		    }else if('choice' in data){
			use_app_id(data.choice);
		    }
		});
	    }else{
		setTimeout(complete_link, 100);
	    }
	    return;
	}
	setTimeout(complete_link, 100);
    };
    
    var btn = jQuery("#hipmob_get_appid");
    btn.on('click', function(event){
        event.preventDefault();

	var url = "https://manage.hipmob.com/account/chooseapp?"+jQuery.param({ cb: new Date().getTime(), target: linktarget });
	dlg = jQuery('<div style="padding:0px; width: 460px; height: 435px"><iframe style="width: 460px; height: 370px" src="'+url+'" width="460px" height="370px"></iframe></div>');
	dlg.dialog({
            'dialogClass'   : 'wp-dialog',           
            'modal'         : true,
            'autoOpen'      : false, 
            'closeOnEscape' : true,
	    'width'         : 460,
 	    'height'        : 445,
            'buttons'       : {
		"Close": function() {
                    close_dialog();
		}
            }
	});
        dlg.dialog('open');
    });
    
    complete_link();
});    