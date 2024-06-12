var wt_iew_ftp = (function ( $ ) {
    // 'use strict';
    var wt_iew_ftp = {
        onEdit:false,
        poPupCrud:0,
        poPupPage:'export', /* popup is called from export/import page */
        useProfileId:0,
        test_ftp_xhr:null,
        import_profile:0,
        import_path:'',
        import_file:'',
        Set:function () {
            if ($('.wt_iew_ftp_settings_page').length > 0 && $('.wt_iew_popup_ftp_crud').length == 0) {
                this.loadPage();
            }

            this.popUpCrud();
        },
        importer_set_validate_file_info:function (file_from) {
            if (file_from == 'ftp') { /* file is from FTP */
                this.import_profile = jQuery('[name="wt_iew_ftp_profile"]').val();
                this.import_path    = jQuery('[name="wt_iew_import_path"]').val();
                this.import_file    = jQuery('[name="wt_iew_import_file"]').val();
            }
        },
        importer_reset_form_data:function () {
            this.import_profile = 0;
            this.import_path    = '';
            this.import_file    = '';
        },
        validate_import_ftp_fields:function (is_continue, action, action_type, is_previous_step) {
            if (jQuery('[name="wt_iew_file_from"]').length > 0 && !is_previous_step && jQuery('[name="wt_iew_file_from"]').is(':visible')) {
                if (jQuery('select[name="wt_iew_file_from"]').length > 0) {  /* select box */
                    var file_from = jQuery('[name="wt_iew_file_from"]').val();
                } else {
                    var file_from = jQuery('[name="wt_iew_file_from"]:checked').val();
                }

                if (file_from == 'ftp') { /* file is from FTP */
                    if (parseInt(jQuery('[name="wt_iew_ftp_profile"]').val()) == 0) {
                         wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.choose_a_profile);
                         is_continue = false;
                    }

                    if (is_continue) {
                        if (jQuery.trim(jQuery('[name="wt_iew_import_path"]').val()) == '') {
                            wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.enter_an_import_path);
                            is_continue = false;
                        }
                    }

                    if (is_continue) {
                        if (jQuery.trim(jQuery('[name="wt_iew_import_file"]').val()) == '') {
                            wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.enter_an_import_file);
                            is_continue = false;
                        }
                    }

                    /* all fields are okay then check any chnages made to these fields. This is for revalidating the input file */
                    if (is_continue) {
                         var is_changed = false;
                        if (jQuery('[name="wt_iew_ftp_profile"]').val() != this.import_profile) {
                            is_changed = true;
                        }

                        if (!is_changed) {
                            if (jQuery('[name="wt_iew_import_path"]').val() != this.import_path) {
                                is_changed = true;
                            }
                        }

                        if (!is_changed) {
                            if (jQuery('[name="wt_iew_import_file"]').val() != this.import_file) {
                                is_changed = true;
                            }
                        }

                        if (is_changed) {
                            wt_iew_import.is_valid_file = false;
                        } else {
                            wt_iew_import.is_valid_file = true;
                        }
                    }//end if
                }//end if
            }//end if

            return is_continue;
        },
        validate_export_ftp_fields:function (is_continue, action, action_type, is_previous_step) {
            var validate_it = false;
            if (jQuery('[name="wt_iew_file_into"]').length > 0) {
                if (action_type == 'step') {
                    if (!is_previous_step && jQuery('[name="wt_iew_file_into"]').is(':visible')) {
                        validate_it = true;
                    }
                } else {
                    if (action != 'export_image') {
                        validate_it = true;
                    }
                }
            }

            if (validate_it) {
                if (jQuery('select[name="wt_iew_file_into"]').length > 0) {  /* select box */
                    var file_into = jQuery('[name="wt_iew_file_into"]').val();
                } else {
                    var file_into = jQuery('[name="wt_iew_file_into"]:checked').val();
                }

                if (file_into == 'ftp') {
                    if (parseInt(jQuery('[name="wt_iew_ftp_profile"]').val()) == 0) {
                        wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.choose_a_profile);
                        is_continue = false;
                    }

                    if (is_continue) {
                        if (jQuery.trim(jQuery('[name="wt_iew_export_path"]').val()) == '') {
                            wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.enter_an_export_path);
                            is_continue = false;
                        }
                    }
                }
            }//end if

            return is_continue;
        },
        ftp_custom_path_toggle:function () {
            if ($('[name="wt_iew_use_default_path"]:checked').val() == 'Yes') {
                $('.wt_iew_ftp_path').prop('readonly', true).css({'background':'#efefef'});
                var ftp_path = $('[name="wt_iew_ftp_profile"]').find('option:selected').attr('data-path');
                jQuery('.wt_iew_ftp_path').val(ftp_path);
            } else {
                $('.wt_iew_ftp_path').prop('readonly', false).css({'background':'#ffffff'});
                jQuery('.wt_iew_ftp_path').focus().select();
            }
        },
        toggle_ftp_path:function (file_into) {
            wt_iew_ftp.ftp_custom_path_toggle();

            $('[name="wt_iew_use_default_path"]').unbind('click').click(
                function () {
                    wt_iew_ftp.ftp_custom_path_toggle();
                }
            );

            jQuery('[name="wt_iew_ftp_profile"]').unbind('change').on(
                'change',
                function () {
                    var ftp_path = jQuery(this).find('option:selected').attr('data-path');
                    if (jQuery('[name="wt_iew_use_default_path"]:checked').val() == 'Yes') {
                           jQuery('.wt_iew_ftp_path').val(ftp_path);
                    } else {
                        jQuery('.wt_iew_ftp_path').focus().select();
                    }
                }
            );
        },
        popUpCrud:function (page) {
            $('.wt_iew_ftp_profiles').unbind('click').click(
                function () {
                    var pop_elm = $('.wt_iew_popup_ftp_crud');
                    var ww      = $(window).width();
                    pop_w       = ((ww < 1200 ? ww : 1200) - 200);
                    pop_w       = (pop_w < 200 ? 200 : pop_w);
                    pop_elm.width(pop_w);

                    wh    = $(window).height();
                    pop_h = (wh >= 400 ? (wh - 200) : wh);
                    $('.wt_iew_ftp_settings_page').css({'max-height':pop_h + 'px','overflow':'auto'});
                    var target_tab       = $(this).attr('data-tab');
                    wt_iew_ftp.poPupCrud = 1;
                    wt_iew_ftp.poPupPage = page;
                    wt_iew_popup.showPopup(pop_elm);
                    wt_iew_ftp.loadPage(target_tab);
                }
            );
        },
        loadPage:function (target_tab) {
            $('.wt_iew_ftp_settings_page').html('<div class="wt_iew_ftp_loader">' + wt_iew_params.msgs.loading + '</div>');
            $.ajax(
                {
                    url:wt_iew_params.ajax_url,
                    data:{'action':'iew_ftp_ajax', _wpnonce:wt_iew_params.nonces.main, 'iew_ftp_action':'settings_page', 'popup_page':wt_iew_ftp.poPupCrud},
                    type:'post',
                    dataType:"html",
                    success:function (data) {
                         $('.wt_iew_ftp_settings_page').html(data);
                         wt_iew_ftp.regMainEvents(target_tab);
                    },
                    error:function () {
                           wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                           $('.wt_iew_ftp_settings_page').html('<div class="wt_iew_ftp_loader">' + wt_iew_params.msgs.error + '</div>');
                    }
                }
            );
        },
        loadList:function () {
            $('.wt_iew_ftp_list').html('<div class="wt_iew_ftp_loader">' + wt_iew_params.msgs.loading + '</div>');
            $.ajax(
                {
                    url:wt_iew_params.ajax_url,
                    data:{'action':'iew_ftp_ajax', _wpnonce:wt_iew_params.nonces.main, 'iew_ftp_action':'ftp_list','popup_page':wt_iew_ftp.poPupCrud},
                    type:'post',
                    dataType:"html",
                    success:function (data) {
                         $('.wt_iew_ftp_list').html(data);
                        if (wt_iew_ftp.poPupCrud == 1) {
                            wt_iew_ftp.updateSelectBox(); /* update select box options with new data */
                        }

                        wt_iew_ftp.regEvents();
                    },
                    error:function () {
                           wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                           $('.wt_iew_ftp_list').html('<div class="wt_iew_ftp_loader">' + wt_iew_params.msgs.error + '</div>');
                    }
                }
            );
        },
        regMainEvents:function (target_tab) {
            this.subTab($('.wt_iew_ftp_settings_page'));
            if (target_tab) {
                $('.wt_iew_sub_tab li[data-target="' + target_tab + '"]').trigger('click');
            }

            this.saveData();
            this.testFtp();
            this.abortFtpTest();
            this.regEvents();
        },
        regEvents:function () {
            $('.wt_iew_ftp_edit').click(
                function () {
                    wt_iew_ftp.switchTab(wt_iew_ftp_params.msgs.edit, wt_iew_ftp_params.msgs.edit_hd, true);
                    var form_data_dv = $(this).parents('td').find('.wt_iew_data_dv');
                    form_data_dv.find('span').each(
                        function () {
                            var cls            = $(this).attr('class');
                            var val            = $.trim($(this).text());
                            var frm_input      = $('[name="' + cls + '"]');
                            var frm_input_type = frm_input.attr('type');
                            if (frm_input_type == 'text' || frm_input_type == 'hidden' || frm_input_type == 'password' || frm_input_type == 'number') {
                                frm_input.val(val);
                            } else if (frm_input_type == 'radio') {
                                frm_input.prop('checked',false).filter(
                                    function () {
                                        return $(this).val() == val;
                                    }
                                ).prop('checked',true);
                            }
                        }
                    );
                }
            );

            $('.wt_iew_sub_tab li[data-target="ftp-profiles"]').click(
                function () {
                    wt_iew_ftp.switchTab(wt_iew_ftp_params.msgs.add_new, wt_iew_ftp_params.msgs.add_new_hd,false);
                }
            );

            $('.wt_iew_sub_tab li[data-target="add-new-ftp"]').click(
                function () {
                    if (!wt_iew_ftp.onEdit) {
                           $('#wt_iew_ftp_form').trigger('reset');
                           $('[name="wt_iew_ftp_id"]').val(0);
                    }
                }
            );

            $('.wt_iew_ftp_delete').click(
                function () {
                    if (confirm(wt_iew_ftp_params.msgs.sure)) {
                           wt_iew_ftp.deleteData($(this));
                    }
                }
            );
    
            $('.wt_iew_sftp_download').click(
                function () {
                           wt_iew_ftp.dowloadSftp($(this));
                }
            );
    
            if ($('.wt_iew_ftp_add').length > 0) { /* no profile exists then show an extra add new button */
                $('.wt_iew_ftp_add').click(
                    function () {
                        $('.wt_iew_sub_tab li[data-target="add-new-ftp"]').trigger('click');
                    }
                );
            }

            if (this.poPupCrud == 1) {
                $('.wt_iew_ftp_use').unbind('click').click(
                    function () {
                        wt_iew_ftp.useData($(this));
                        wt_iew_popup.hidePopup();
                    }
                );

                /* check any pending `use` requests and execute */
                if (parseInt(wt_iew_ftp.useProfileId) > 0) {
                     var trget_elm = $('.wt_iew_ftp_use[data-id="' + wt_iew_ftp.useProfileId + '"]');
                    if (trget_elm.length > 0) {
                        trget_elm.trigger('click');
                    } else {
                        wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                    }

                    wt_iew_ftp.useProfileId = 0; /* reset pending request */
                }
            }
        },
        subTab:function (wf_prnt_obj) {
            wf_prnt_obj.find('.wt_iew_sub_tab li').click(
                function () {
                    var trgt = $(this).attr('data-target');
                    var prnt = $(this).parent('.wt_iew_sub_tab');
                    var ctnr = prnt.siblings('.wt_iew_sub_tab_container');
                    prnt.find('li a').css({'color':'#0073aa','cursor':'pointer'});
                    $(this).find('a').css({'color':'#000','cursor':'default'});
                    ctnr.find('.wt_iew_sub_tab_content').hide();
                    ctnr.find('.wt_iew_sub_tab_content[data-id="' + trgt + '"]').show();
                }
            );
            wf_prnt_obj.find('.wt_iew_sub_tab').each(
                function () {
                    var elm = $(this).children('li').eq(0);
                    elm.click();
                }
            );
        },
        useData:function (elm) {
            var id = elm.attr('data-id');
            $('[name="wt_iew_ftp_profile"]').val(id).trigger('change');
        },
        updateSelectBox:function () {
            var ftp_list_sele_html = '';
            var vl_bckup           = $('[name="wt_iew_ftp_profile"] option:selected').val();
            if ($('.ftp_list_tb').length > 0) {
                ftp_list_sele_html += '<option value="0" data-path="">' + wt_iew_ftp_params.msgs.select_one + '</option>';
                $('.ftp_list_tb').find('.wt_iew_data_dv').each(
                    function () {
                        var id       = $.trim($(this).find('.wt_iew_ftp_id').text());
                        var ftp_path = $.trim($(this).find('.wt_iew_ftp' + wt_iew_ftp.poPupPage + '_path').text());
                        var ftp_profilename = $.trim($(this).find('.wt_iew_profilename').text());
                        ftp_list_sele_html += '<option value="' + id + '" data-path="' + ftp_path + '">' + ftp_profilename + '</option>';
                    }
                );
                $('[name="wt_iew_ftp_profile"]').html(ftp_list_sele_html);
                vl_bckup = ($('[name="wt_iew_ftp_profile"]').find('option[value="' + vl_bckup + '"]').length == 0 ? 0 : vl_bckup);
                $('[name="wt_iew_ftp_profile"]').val(vl_bckup).trigger('change');
                $('.wt_iew_ftp_profiles').html($('.wt_iew_ftp_profiles').attr('data-label-ftp-profiles')).attr('data-tab', 'ftp-profiles');
            } else {
                ftp_list_sele_html = '<option value="0" data-path="">' + wt_iew_ftp_params.msgs.no_ftp_prfile_found + '</option>';
                $('[name="wt_iew_ftp_profile"]').html(ftp_list_sele_html).trigger('change');
                $('.wt_iew_ftp_profiles').html($('.wt_iew_ftp_profiles').attr('data-label-add-ftp-profile')).attr('data-tab', 'add-new-ftp');
            }
        },
        deleteData:function (elm) {
            var id = elm.attr('data-id');
            elm.html(wt_iew_ftp_params.msgs.wait);
            $.ajax(
                {
                    url:wt_iew_params.ajax_url,
                    data:{'action':'iew_ftp_ajax', _wpnonce:wt_iew_params.nonces.main, 'iew_ftp_action':'delete_ftp','wt_iew_ftp_id':id},
                    type:'post',
                    dataType:"json",
                    success:function (data) {
                        if (data.status == true) {
                            wt_iew_ftp.loadList();
                            wt_iew_notify_msg.success(wt_iew_params.msgs.success);
                        } else {
                            wt_iew_notify_msg.error(data.msg);
                            elm.html(wt_iew_ftp_params.msgs.delete);
                        }
                    },
                    error:function () {
                           wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                           elm.html(wt_iew_ftp_params.msgs.delete);
                    }
                }
            );
        },
        dowloadSftp:function (elm) {
            elm.html(wt_iew_ftp_params.msgs.wait);
            $.ajax(
                {
                    url:wt_iew_params.ajax_url,
                    data:{'action':'iew_sftp_download', _wpnonce:wt_iew_params.nonces.main, 'iew_ftp_action':'sftp_download'},
                    type:'post',
                    dataType:"json",
                    success:function (data) {
                        if (data.status == true) {
                            wt_iew_ftp.loadList();
                            wt_iew_notify_msg.success(data.msg);
                            elm.hide();
                            $('#is_sftp_radio').show();
                        } else {
                            wt_iew_notify_msg.error(data.msg);
                            elm.html(wt_iew_ftp_params.msgs.dowbload_sftp);
                        }
                    },
                    error:function () {
                           wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                           elm.html(wt_iew_ftp_params.msgs.dowbload_sftp);
                    }
                }
            );
        },
        abortFtpTest:function () {
            $('.wt_iew_abort_test_ftp').unbind('click').click(
                function () {
                    if (wt_iew_ftp.test_ftp_xhr != null) {
                           wt_iew_ftp.test_ftp_xhr.abort();
                    }

                    $(this).hide();
                }
            );
        },
        testFtp:function () {
            $('.wt_iew_test_ftp_form').unbind('click').click(
                function () {
                    var ftp_form = $('#wt_iew_ftp_form');
                    var er       = 0;
                    ftp_form.find('input[type="text"], input[type="number"], input[type="password"]').each(
                        function () {

                            if ($.inArray($(this).attr('name'), ['wt_iew_profilename', 'wt_iew_ftpexport_path', 'wt_iew_ftpimport_path']) == -1) {
                                if ($(this).val() == "") {
                                         er = 1;
                                }
                            }
                        }
                    );

                    if (er == 0) {
                        ftp_form.find('input[type="radio"]').each(
                            function () {
                                var nme = $(this).attr('name');
                                if ($('[name="' + nme + '"]:checked').length == 0) {
                                           er = 1;
                                }
                            }
                        );
                    }

                    if (er == 1) {
                           wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.some_mandatory);
                           return false;
                    }

                    $('[name="iew_ftp_action"]').val('test_ftp');
                    wt_iew_ftp.setFormLoader();
                    $('.wt_iew_abort_test_ftp').css({'opacity':'1','cursor':'pointer'}).prop('disabled', false).show();

                    wt_iew_ftp.test_ftp_xhr = $.ajax(
                        {
                            url:wt_iew_params.ajax_url,
                            data:ftp_form.serialize(),
                            type:'post',
                            dataType:"json",
                            success:function (data) {
                                $('.wt_iew_abort_test_ftp').hide();
                                wt_iew_ftp.removeFormLoader();
                                if (data.status == true) {
                                    wt_iew_notify_msg.success(data.msg);
                                } else {
                                    wt_iew_notify_msg.error(data.msg);
                                }
                            },
                            error:function (jqXHR, textStatus, errorThrown) {
                                $('.wt_iew_abort_test_ftp').hide();
                                wt_iew_ftp.removeFormLoader();
                                if (textStatus == 'abort') {
                                    wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.aborted);
                                } else {
                                    wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                                }
                            }
                        }
                    );
                }
            );
        },
        removeFormLoader:function () {
            var submit_btn = $('#wt_iew_ftp_form').find('input[type="submit"], input[type="button"]');
            var spinner    = submit_btn.siblings('.spinner');
            spinner.css({'visibility':'hidden'});
            submit_btn.css({'opacity':'1','cursor':'pointer'}).prop('disabled',false);
        },
        setFormLoader:function () {
            var submit_btn = $('#wt_iew_ftp_form').find('input[type="submit"], input[type="button"]');
            var spinner    = submit_btn.siblings('.spinner');
            spinner.css({'visibility':'visible'});
            submit_btn.css({'opacity':'.5','cursor':'default'}).prop('disabled',true);
        },
        saveData:function () {
            $('#wt_iew_ftp_form').submit(
                function (e) {
                    e.preventDefault();
                    $('.wt_iew_ftp_warn').hide();
                    var er = 0;
                    $(this).find('input[type="text"], input[type="number"], input[type="password"]').each(
                        function () {
                            if ($(this).val() == "") {
                                er = 1;
                            }
                        }
                    );

                    if (er == 0) {
                        $(this).find('input[type="radio"]').each(
                            function () {
                                var nme = $(this).attr('name');
                                if ($('[name="' + nme + '"]:checked').length == 0) {
                                           er = 1;
                                }
                            }
                        );
                    }

                    if (er == 1) {
                           wt_iew_notify_msg.error(wt_iew_ftp_params.msgs.mandatory);
                           return false;
                    }

                    $('[name="iew_ftp_action"]').val('save_ftp');
                    wt_iew_ftp.setFormLoader();

                    $.ajax(
                        {
                            url:wt_iew_params.ajax_url,
                            data:$(this).serialize(),
                            type:'post',
                            dataType:"json",
                            success:function (data) {
                                wt_iew_ftp.removeFormLoader();
                                if (data.status == true) {
                                    $('.wt_iew_sub_tab li[data-target="ftp-profiles"]').trigger('click');
                                    wt_iew_ftp.loadList();
                                    /* add/edit and use enabled for currently added/edited profile */
                                    if (wt_iew_ftp.poPupCrud == 1 && $('[name="wt_iew_add_and_use_ftp"]').is(':checked')) {
                                        wt_iew_ftp.useProfileId = data.id;
                                    }

                                    wt_iew_notify_msg.success(wt_iew_params.msgs.success);
                                } else {
                                    wt_iew_notify_msg.error(data.msg);
                                }
                            },
                            error:function () {
                                wt_iew_ftp.removeFormLoader();
                                wt_iew_notify_msg.error(wt_iew_params.msgs.error);
                            }
                        }
                    );
                }
            );
        },
        switchTab:function (new_txt, new_hd_txt, change_tab) {
            var elm = $('.wt_iew_sub_tab li[data-target="add-new-ftp"]');
            if (change_tab) {
                this.onEdit = true;
                elm.trigger('click');
            } else {
                this.onEdit = false;
            }

            elm.find('a').html(new_txt);
            $('.wt_iew_form_title').text(new_hd_txt);
        }
    }
    return wt_iew_ftp;
})(jQuery);

jQuery(
    function () {
        wt_iew_ftp.Set();
    }
);