/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 4.2.1
|| # ---------------------------------------------------------------- # ||
|| # Copyright �2000-2013 vBulletin Solutions Inc. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/
function display_post(B,A){if(AJAX_Compatible){vB_PostLoader[B]=new vB_AJAX_PostLoader(B,A);vB_PostLoader[B].init()}else{pc_obj=fetch_object("postcount"+this.postid);openWindow("showthread.php?"+(SESSIONURL?"s="+SESSIONURL:"")+(pc_obj!=null?"&postcount="+PHP.urlencode(pc_obj.name):"")+"&p="+B+"#post"+B)}return false}var vB_PostLoader=new Array();function vB_AJAX_PostLoader(B,A){this.postid=B;this.prefix=(A?true:false);this.selector=(this.prefix?"comments_":"post_");this.post=YAHOO.util.Dom.get(this.selector+this.postid)}vB_AJAX_PostLoader.prototype.init=function(){if(this.post){postid=this.postid;pc_obj=fetch_object("postcount"+this.postid);YAHOO.util.Connect.asyncRequest("POST",fetch_ajax_url("showpost.php?p="+this.postid),{success:this.display,failure:this.handle_ajax_error,timeout:vB_Default_Timeout,scope:this},SESSIONURL+"securitytoken="+SECURITYTOKEN+"&ajax=1&postid="+this.postid+(this.prefix?"&prefix=1":"")+(pc_obj!=null?"&postcount="+PHP.urlencode(pc_obj.name):""))}};vB_AJAX_PostLoader.prototype.handle_ajax_error=function(A){vBulletin_AJAX_Error_Handler(A)};vB_AJAX_PostLoader.prototype.display=function(B){if(B.responseXML){var C=B.responseXML.getElementsByTagName("postbit");if(C.length){var A=string_to_node(C[0].firstChild.nodeValue);if(this.prefix){container=this.post.getElementsByTagName("ol");container[0].innerHTML="";container[0].appendChild(A)}else{this.post.parentNode.replaceChild(A,this.post)}PostBit_Init(A,this.postid)}else{openWindow("showthread.php?"+(SESSIONURL?"s="+SESSIONURL:"")+(pc_obj!=null?"&postcount="+PHP.urlencode(pc_obj.name):"")+"&p="+this.postid+"#post"+this.postid)}}};