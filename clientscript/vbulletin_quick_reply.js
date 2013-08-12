/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.8.7 Patch Level 3
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2013 vBulletin Solutions, Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
var qr_repost=false;var qr_errors_shown=false;var qr_active=false;var qr_ajax=null;var clickedelm=false;function qr_init(){qr_disable_controls();qr_init_buttons(fetch_object("posts"))}function qr_init_buttons(C){var B=fetch_tags(C,"a");for(var A=0;A<B.length;A++){if(B[A].id&&B[A].id.substr(0,3)=="qr_"){B[A].onclick=function(D){return qr_activate(this.id.substr(3))}}}}function qr_disable_controls(){if(require_click){fetch_object("qr_postid").value=0;vB_Editor[QR_EditorID].disable_editor(vbphrase.click_quick_reply_icon);var A=fetch_object("cb_signature");if(A!=null){A.disabled=true}active=false;qr_active=false}else{vB_Editor[QR_EditorID].write_editor_contents("");qr_active=true}if(threaded_mode!=1){fetch_object("qr_quickreply").disabled=true}}function qr_activate(C){var B=fetch_object("collapseobj_quickreply");if(B&&B.style.display=="none"){toggle_collapse("quickreply")}fetch_object("qr_postid").value=C;if(fetch_object("qr_specifiedpost")){fetch_object("qr_specifiedpost").value=1}fetch_object("qr_quickreply").disabled=false;var A=fetch_object("cb_signature");if(A){A.disabled=false;A.checked=true}if(qr_active==false){vB_Editor[QR_EditorID].enable_editor("")}if(!is_ie&&vB_Editor[QR_EditorID].wysiwyg_mode){fetch_object("qr_scroll").scrollIntoView(false)}vB_Editor[QR_EditorID].check_focus();qr_active=true;return false}function qr_prepare_submit(D,A){if(qr_repost==true){return true}if(!allow_ajax_qr||!AJAX_Compatible){return qr_check_data(D,A)}else{if(qr_check_data(D,A)){if(typeof vb_disable_ajax!="undefined"&&vb_disable_ajax>0){return true}if(is_ie&&userAgent.indexOf("msie 5.")!=-1){if(PHP.urlencode(D.message.value).indexOf("%u")!=-1){return true}}if(YAHOO.util.Connect.isCallInProgress(qr_ajax)){return false}if(clickedelm==D.preview.value){return true}else{var E="ajax=1";if(typeof ajax_last_post!="undefined"){E+="&ajax_lastpost="+PHP.urlencode(ajax_last_post)}for(var C=0;C<D.elements.length;C++){var F=D.elements[C];if(F.name&&!F.disabled){switch(F.type){case"text":case"textarea":case"hidden":E+="&"+F.name+"="+PHP.urlencode(F.value);break;case"checkbox":case"radio":E+=F.checked?"&"+F.name+"="+PHP.urlencode(F.value):"";break;case"select-one":E+="&"+F.name+"="+PHP.urlencode(F.options[F.selectedIndex].value);break;case"select-multiple":for(var B=0;B<F.options.length;B++){E+=(F.options[B].selected?"&"+F.name+"="+PHP.urlencode(F.options[B].value):"")}break}}}fetch_object("qr_posting_msg").style.display="";document.body.style.cursor="wait";qr_ajax_post(D.action,E);return false}}else{return false}}}function qr_resubmit(){qr_repost=true;var A=document.createElement("input");A.type="hidden";A.name="ajaxqrfailed";A.value="1";fetch_object("qrform").appendChild(A);fetch_object("qrform").submit()}function qr_check_data(B,A){switch(fetch_object("qr_postid").value){case"0":alert(vbphrase.click_quick_reply_icon);return false;case"who cares":if(typeof B.quickreply!="undefined"){B.quickreply.checked=false}break}if(clickedelm==B.preview.value){A=0}return vB_Editor[QR_EditorID].prepare_submit(0,A)}function qr_ajax_post(B,A){if(YAHOO.util.Connect.isCallInProgress(qr_ajax)){YAHOO.util.Connect.abort(qr_ajax)}qr_repost=false;qr_ajax=YAHOO.util.Connect.asyncRequest("POST",B,{success:qr_do_ajax_post,failure:qr_handle_error,timeout:vB_Default_Timeout},SESSIONURL+"securitytoken="+SECURITYTOKEN+"&"+A)}function qr_handle_error(A){vBulletin_AJAX_Error_Handler(A);fetch_object("qr_posting_msg").style.display="none";document.body.style.cursor="default";qr_resubmit()}function qr_do_ajax_post(G){if(G.responseXML){document.body.style.cursor="auto";fetch_object("qr_posting_msg").style.display="none";var D;if(fetch_tag_count(G.responseXML,"postbit")){ajax_last_post=G.responseXML.getElementsByTagName("time")[0].firstChild.nodeValue;qr_disable_controls();qr_hide_errors();var C=G.responseXML.getElementsByTagName("postbit");for(D=0;D<C.length;D++){var I=document.createElement("div");I.innerHTML=C[D].firstChild.nodeValue;var E=fetch_object("lastpost");var B=E.parentNode;var A=B.insertBefore(I,E);PostBit_Init(A,C[D].getAttribute("postid"))}if(fetch_object("qr_submit")){fetch_object("qr_submit").blur()}}else{if(!is_saf){var H=G.responseXML.getElementsByTagName("error");if(H.length){var F="<ol>";for(D=0;D<H.length;D++){F+="<li>"+H[D].firstChild.nodeValue+"</li>"}F+="</ol>";qr_show_errors("<ol>"+F+"</ol>");return false}}qr_resubmit()}}else{qr_resubmit()}}function qr_show_errors(A){qr_errors_shown=true;fetch_object("qr_error_td").innerHTML=A;fetch_object("qr_error_tbody").style.display="";vB_Editor[QR_EditorID].check_focus();return false}function qr_hide_errors(){if(qr_errors_shown){qr_errors_shown=true;fetch_object("qr_error_tbody").style.display="none";return false}}var vB_QuickReply=true;