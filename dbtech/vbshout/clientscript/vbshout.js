
var jQueryDupe=$;$.noConflict(true)(function($)
{var paused={},editorContents={},shoutDelay={},idleTime={},hasFetched={},tab={},tabList={},countDown={},pmUserId={},userIds={},pmTime={},shoutId={},chatroom={},aopFile={},aopTime={},menuId={},smilieWindows={};if(typeof vBShout=='undefined')
{return false;}
if(typeof vBShout.instanceOptions=='undefined'||fetchElem('form',false,'form').length==0)
{return false;}
if(!AJAX_Compatible)
{setMessage(vbphrase['dbtech_vbshout_ajax_disabled'],'error');return false;}
var timenow=parseInt(new Date().getTime()/1000);for(var instanceId in vBShout.instanceOptions)
{tab[instanceId]='shouts';menuId[instanceId]=0;aopTime[instanceId]={};aopTime[instanceId][tab[instanceId]]=timenow;pmTime[instanceId]=vBShout.userOptions.pmtime;tabList[instanceId]={'shouts':true,'activeusers':true};fetchElem('colorrow',instanceId,'ul',' > li[class="colorbutton"] > div').on('click',{'instanceId':instanceId},function(e)
{saveStyleProps(e.data.instanceId,'color',rgbToHex($(this).css('backgroundColor')));});fetchElem('fontrow',instanceId,'ul',' > li[class="fontname"] > a').on('click',{'instanceId':instanceId},function(e)
{saveStyleProps(e.data.instanceId,'font',$(this).text());});fetchElem('sizerow',instanceId,'ul',' > li[class="fontsize"] > a').on('click',{'instanceId':instanceId},function(e)
{saveStyleProps(e.data.instanceId,'size',$(this).text());});for(var i in vBShout.tabs[instanceId])
{createTab(i,instanceId,vBShout.tabs[instanceId][i].text,vBShout.tabs[instanceId][i].canclose,vBShout.tabs[instanceId][i].extraparams);}}
var tmp={0:'color',1:'font',2:'size'};for(var i in tmp)
{fetchElem(tmp[i]+'row',false,'ul').each(function(index,element)
{var thisRow=$(this),instanceId=thisRow.attr('data-instanceid');if(vBShout.editorOptions[instanceId][tmp[i]])
{saveStyleProps(instanceId,tmp[i],vBShout.editorOptions[instanceId][tmp[i]],true);}});}
fetchElem('editor').on('keyup focus',function(e)
{var thisEditor=$(this),instanceId=thisEditor.attr('data-instanceid');if(vBShout.instanceOptions[instanceId].maxchars==0)
{if(thisEditor.val().length)
{editorContents[instanceId]=thisEditor.val();}
return true;}
if(thisEditor.val().length>vBShout.instanceOptions[instanceId].maxchars)
{thisEditor.val(thisEditor.val().substring(0,vBShout.instanceOptions[instanceId].maxchars));}
if(thisEditor.val().length)
{editorContents[instanceId]=thisEditor.val();}
fetchElem('remainingchars',instanceId).text((vBShout.instanceOptions[instanceId].maxchars-thisEditor.val().length));}).on('keyup',function(e)
{var thisEditor=$(this),instanceId=thisEditor.attr('data-instanceid');e.preventDefault();if(e.which==27&&shoutId[instanceId])
{fetchElem('cancelbutton',instanceId,'input').trigger('click');}
if(e.which==13)
{fetchElem('savebutton',instanceId,'input').trigger('click');}});fetchElem('savebutton',false,'input').on('click',function()
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid');if(shoutId[instanceId])
{if(fetchElem('editor',instanceId).val()==fetchElem('shout_raw',instanceId,'input','[data-shoutid="'+shoutId[instanceId]+'"]').val())
{fetchElem('cancelbutton',instanceId,'input').trigger('click');}
else
{saveShout(instanceId);}}
else
{saveShout(instanceId);}});fetchElem('lookupbutton',false,'input').on('click',function()
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid'),lookupArea=fetchElem('lookup',instanceId,'input')
userName=lookupArea.val();if(userName.length==0)
{return false;}
console.log(timeStamp()+'Attempting to lookup username: %s in instance %s...',userName,instanceId);paused[instanceId]=true;vBShout_unIdle(instanceId,false,'lookup');if(typeof userIds[instanceId]!='undefined')
{var userId=0;for(var i in userIds[instanceId])
{if(userIds[instanceId][i]!=userName)
{continue;}
userId=i;}
if(userId>0)
{createTab('pm_'+userId+'_'+instanceId,instanceId,userName,'1',{'userid':userId});lookupArea.val('');return true;}}
ajaxCall('lookup',instanceId,{'username':PHP.urlencode($.trim(userName))},'GET');});fetchElem('resettarget',false,'a').on('click',function()
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid');console.log(timeStamp()+'Resetting shout target in instance %s...',instanceId);pmUserId[instanceId]=0;setShoutTarget(false,instanceId);fetchElem('tab',instanceId,'div','[data-tabid="shouts"]').trigger('click');});fetchElem('createchatbutton',false,'input').on('click',function()
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid'),chatroomName=fetchElem('roomname',instanceId,'input').val();if(chatroomName.length==0)
{return false;}
console.log(timeStamp()+'Attempting to create chatroom: %s in instance %s...',chatroomName,instanceId);paused[instanceId]=true;vBShout_unIdle(instanceId,false,'createchat');ajaxCall('createchat',instanceId,{'title':PHP.urlencode($.trim(chatroomName))});});fetchElem('soundbutton',false,'img').on('click',function()
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid');vBShout.userOptions.soundSettings=vBShout.userOptions.soundSettings||{};vBShout.userOptions.soundSettings[instanceId]=vBShout.userOptions.soundSettings[instanceId]||{};vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]||'1';vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=(vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=='1'?'0':'1');setMuteButton(instanceId);console.log(timeStamp()+'Toggle mute in instance %s...',instanceId);paused[instanceId]=true;vBShout_unIdle(instanceId,false,'savesounds');ajaxCall('sounds',instanceId,{'tabs':vBShout.userOptions.soundSettings[instanceId]});}).each(function(index,element)
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid');setMuteButton(instanceId);});fetchElem('active',false,'img').on('click',function()
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid');vBShout.userOptions.invisible=vBShout.userOptions.invisible||{};vBShout.userOptions.invisible[instanceId]=vBShout.userOptions.invisible[instanceId]||'0';vBShout.userOptions.invisible[instanceId]=(vBShout.userOptions.invisible[instanceId]=='1'?'0':'1');setInvisibleButton(instanceId);console.log(timeStamp()+'Toggle invisible in instance %s...',instanceId);paused[instanceId]=true;ajaxCall('invisible',instanceId,{'invisibility':vBShout.userOptions.invisible[instanceId]});}).each(function(index,element)
{var thisButton=$(this),instanceId=thisButton.attr('data-instanceid');setInvisibleButton(instanceId);});$('li.fontname,li.fontsize,li.colorbutton').on('mouseover mouseout',function(e)
{var thisButton=$(this),name=thisButton.attr('data-button'),instanceId=thisButton.attr('data-instanceid');if(thisButton.hasClass('imagebutton_disabled'))
{return false;}
thisButton.removeClass('imagebutton_selected imagebutton_hover imagebutton_down');switch(e.type)
{case'mouseover':thisButton.addClass('imagebutton_hover');break;case'mouseout':thisButton.removeClass('imagebutton_hover');break;}});fetchElem('imagebutton',false,'img').on('click mouseover mouseout',function(e)
{var thisButton=$(this),name=thisButton.attr('data-button'),instanceId=thisButton.attr('data-instanceid');if(thisButton.hasClass('imagebutton_disabled'))
{return false;}
thisButton.removeClass('imagebutton_selected imagebutton_hover imagebutton_down');switch(vBShout.editorOptions[instanceId][name])
{case null:case false:case'':case'0':case'false':case'null':switch(e.type)
{case'mouseover':thisButton.addClass('imagebutton_hover');break;case'mouseout':thisButton.removeClass('imagebutton_hover');break;case'click':thisButton.addClass('imagebutton_down');saveStyleProps(instanceId,name,name);break;}
break;default:switch(e.type)
{case'mouseover':thisButton.addClass('imagebutton_hover').addClass('imagebutton_down');break;case'mouseout':thisButton.removeClass('imagebutton_hover').addClass('imagebutton_selected');break;case'click':thisButton.addClass('imagebutton_hover');saveStyleProps(instanceId,name,'');break;}
break;}}).each(function(index,element)
{var thisButton=$(this),name=thisButton.attr('data-button'),instanceId=thisButton.attr('data-instanceid');if(vBShout.editorOptions[instanceId][name]!='0')
{thisButton.mouseout();saveStyleProps(instanceId,name,vBShout.editorOptions[instanceId][name],true);}});fetchElem('clearbutton',false,'input').on('click',function()
{fetchElem('editor',$(this).attr('data-instanceid')).val('');});fetchElem('smilies').on('click',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid');if(typeof smilieWindows[instanceId]!='undefined'&&!smilieWindows[instanceId].closed)
{smilieWindows[instanceId].focus();}
else
{smilieWindows[instanceId]=openWindow('misc.php?'+SESSIONURL+'do=getsmilies&editorid=dbtech_vbshout_editor'+instanceId,440,480,'smilie_window'+instanceId);window.onunload=function()
{if(typeof smilieWindows[instanceId]!='undefined'&&!smilieWindows[instanceId].closed)
{smilieWindows[instanceId].close();}};}});fetchElem('cancelbutton',false,'input').on('click',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid');paused[instanceId]=false;shoutId[instanceId]=0;fetchElem('editor',$(this).attr('data-instanceid')).val('');fetchElem('editor',instanceId).trigger('keyup');fetchElem('delete',instanceId,'span').fadeOut('fast');fetchElem('cancel',instanceId,'span').fadeOut('fast');fetchElem('editing',instanceId,'span').fadeOut('fast').promise().done(function()
{fetchElem('target',instanceId,'span').fadeIn('fast');});});fetchElem('deletebutton',false,'input').on('click',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid');if(!shoutId[instanceId]||shoutId[instanceId]=='0')
{return false;}
deleteShout(instanceId,shoutId[instanceId]);});fetchElem('setcommand',false,'a').on('click',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid');fetchElem('editor',instanceId).val(cmd.attr('data-command'));});fetchElem('form',false,'form').on('dblclick','div[name="dbtech_vbshout_frame_sticky"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid');if(vBShout.instancePermissions[instanceId]['cansticky']=='0')
{return true;}
console.log(timeStamp()+'Fetching sticky for instance %s...',instanceId);paused[instanceId]=true;vBShout_unIdle(instanceId,false,'fetchsticky');ajaxCall('fetchsticky',instanceId,{},'GET');}).on('click','a[name="dbtech_vbshout_togglemenu"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid'),userId=cmd.attr('data-userid'),shoutid=cmd.attr('data-shoutid');closeMenu(instanceId);if(menuId[instanceId]==userId)
{return true;}
menuId[instanceId]=userId;fetchElem('menu',instanceId,'ul','[data-shoutid="'+shoutid+'"]').fadeIn('fast').css({position:'absolute'}).offset({top:(cmd.offset().top+cmd.height()),left:cmd.offset().left});}).on('click','a[name="dbtech_vbshout_setcommand"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid');fetchElem('editor',instanceId).val(cmd.attr('data-command'));closeMenu(instanceId);}).on('click','a[name="dbtech_vbshout_usermanage"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid'),action=cmd.attr('data-command'),userId=cmd.attr('data-userid');if(confirm(vbphrase['dbtech_vbshout_are_you_sure_'+action]))
{console.log(timeStamp()+'Attempting to perform action %s on userid %s, instance id %s...',action,userId,instanceId);var extraParams={};if(pmUserId[instanceId])
{extraParams['type']='pm_'+pmUserId[instanceId]+'_';}
else if(chatroom[instanceId])
{extraParams['type']='chatroom_'+chatroom[instanceId]+'_';}
extraParams['manageaction']=action;extraParams['userid']=userId;paused[instanceId]=true;vBShout_unIdle(instanceId,true,'usermanage');ajaxCall('usermanage',instanceId,extraParams);}
closeMenu(instanceId);}).on('click','a[name="dbtech_vbshout_createpm"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid'),userName=cmd.attr('data-username'),userId=cmd.attr('data-userid');userIds[userId]=userName;createTab('pm_'+userId+'_'+instanceId,instanceId,userName,'1',{'userid':userId});fetchElem('tab',instanceId,'div','[data-tabid="pm_'+userId+'_'+instanceId+'"]').trigger('click');closeMenu(instanceId);}).on('click','a[name="dbtech_vbshout_closetab"]',function(e)
{var closeLink=$(this),instanceId=closeLink.attr('data-instanceid'),tabId=closeLink.attr('data-tabid'),chatroomId=fetchElem('tab',instanceId,'div','[data-tabid="'+tabId+'"]').attr('data-chatroomid');e.preventDefault();if(chatroomId)
{if(!confirm(vbphrase['dbtech_vbshout_are_you_sure_chatleave']))
{return false;}
console.log(timeStamp()+'Closing tab: %s in instance %s...',tabId,instanceId);paused[instanceId]=true;vBShout_unIdle(instanceId,false,'leavechat');ajaxCall('leavechat',instanceId,{'chatroomid':chatroomId});}
closeTab(tabId,instanceId);}).on('click','div[name="dbtech_vbshout_tab"]',function(e)
{var thisTab=$(this),instanceId=thisTab.attr('data-instanceid'),tabId=thisTab.attr('data-tabid'),userId=thisTab.attr('data-userid'),chatroomId=thisTab.attr('data-chatroomid');if(tab[instanceId]==tabId||e.isDefaultPrevented())
{return false;}
console.log(timeStamp()+"Switching from %s to %s for instance %s.",tab[instanceId],tabId,instanceId);if(thisTab.attr('data-loadurl'))
{window.location.href=thisTab.attr('data-loadurl');return false;}
pmUserId[instanceId]=(userId!=''?userId:0);chatroom[instanceId]=(chatroomId!=''?chatroomId:0);setShoutTarget((userId!=''?userIds[userId]:false),instanceId);chatroom[instanceId]?fetchElem('chatinvite',instanceId).fadeIn('fast'):fetchElem('chatinvite',instanceId).fadeOut('fast');fetchElem('tab',instanceId,'div','[data-tabid="'+tab[instanceId]+'"]').removeClass('alt').addClass('alt2');thisTab.removeClass('alt2 dbtech_vbshout_highlight').addClass('alt');tab[instanceId]=tabId;setMuteButton(instanceId);fetchElem('content',instanceId).text('');paused[instanceId]=true;vBShout_unIdle(instanceId,false,'tabswitch');fetchShouts(instanceId,tab[instanceId],true);return false;}).on('dblclick','span[name="dbtech_vbshout_shout"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid')
shoutid=cmd.attr('data-shoutid');if(!shoutid)
{return true;}
beginShoutEdit(instanceId,shoutid);}).on('click','a[name="dbtech_vbshout_editshout"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid')
shoutid=cmd.attr('data-shoutid');if(!shoutid)
{return false;}
beginShoutEdit(instanceId,shoutid);closeMenu(instanceId);}).on('click','a[name="dbtech_vbshout_deleteshout"]',function()
{var cmd=$(this),instanceId=cmd.attr('data-instanceid')
shoutid=cmd.attr('data-shoutid');if(!shoutid)
{return false;}
deleteShout(instanceId,shoutid);closeMenu(instanceId);});fetchElem('archive_managebutton').on('click',function()
{var btn=$(this),instanceId=btn.attr('data-instanceid'),shoutid=btn.attr('data-shoutid'),cmd=btn.attr('data-command');console.log(timeStamp()+'Attempting to %s Archive shout: %s...',cmd,shoutid);if(cmd=='edit')
{fetchElem('archive_message',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeOut('fast');fetchElem('archive_wrapper',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeIn('fast');fetchElem('archive_editor',instanceId).filter('[data-shoutid="'+shoutid+'"]').val(PHP.unhtmlspecialchars(fetchElem('archive_message_raw',instanceId).filter('[data-shoutid="'+shoutid+'"]').val()));}
else if(cmd=='cancel')
{fetchElem('archive_message',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeIn('fast');fetchElem('archive_wrapper',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeOut('fast');}
else if(cmd=='save')
{var editor=fetchElem('archive_editor',instanceId).filter('[data-shoutid="'+shoutid+'"]');fetchElem('archive_message',instanceId).filter('[data-shoutid="'+shoutid+'"]').text(editor.val());fetchElem('archive_message_raw',instanceId).filter('[data-shoutid="'+shoutid+'"]').val(editor.val());fetchElem('archive_message',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeIn('fast');fetchElem('archive_wrapper',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeOut('fast');ajaxCall('save',instanceId,{'shoutid':shoutid,'message':PHP.urlencode($.trim(editor.val())),'source':'archive'});}
else if(cmd=='delete')
{if(!confirm(vbphrase['dbtech_vbshout_are_you_sure_shoutdelete']))
{return false;}
fetchElem('archive_shout',instanceId).filter('[data-shoutid="'+shoutid+'"]').fadeOut('fast');ajaxCall('delete',instanceId,{'shoutid':shoutid,'source':'archive'});}});$('.dbtech-vbshout-popupmenu > ul > li').click(function(e)
{var $container=$(this),$subList=$container.parent();$subList.slideUp('fast');return $subList.hasClass('submenu');});$('.dbtech-vbshout-popupmenu').click(function(e)
{var $container=$(this),$subList=$('> ul',$container);if($container.hasClass('dbtech-vbshout-popupmenu-active'))
{e.target.parentNode.click();$subList.slideUp('fast');return $subList.hasClass('submenu');}
$container.addClass('dbtech-vbshout-popupmenu-active');$subList.css('top',$container.outerHeight()).slideDown('fast');return false;});$('body').click(function(e)
{if($(e.target).closest('.dbtech-vbshout-popupmenu').size()==0)
{$('.dbtech-vbshout-popupmenu > ul').slideUp('fast');$('.dbtech-vbshout-popupmenu').removeClass('dbtech-vbshout-popupmenu-active');}});fetchElem('editor').trigger('keyup');if(!vBShout.userOptions.archive)
{for(var instanceId in vBShout.instanceOptions)
{idleTime[instanceId]=0;shoutDelay[instanceId]=0;countDown[instanceId]=1;}
setInterval(function()
{for(var instanceId in vBShout.instanceOptions)
{if(!fetchElem('frame_notice',instanceId,'div').length)
{continue;}
if(shoutDelay[instanceId]>0)
{shoutDelay[instanceId]--;}
if(vBShout.userOptions.idle[instanceId].unIdle)
{console.log(timeStamp()+"Removing idle for instance %s.",instanceId);fetchElem('frame_notice',instanceId,'div').fadeOut('fast');var imgElem=fetchElem('active',instanceId,'img'),imgElemSrc=imgElem.attr('src');if(imgElem.length)
{imgElem.attr('src',imgElemSrc.replace('offline','online'));}
if(vBShout.userOptions.invisible[instanceId]=='0'&&vBShout.userOptions.idle[instanceId].forceUnIdle)
{ajaxCall('unidle',instanceId,{});}
idleTime[instanceId]=0;if(vBShout.userOptions.idle[instanceId].unPause)
{paused[instanceId]=false;countDown[instanceId]=1;}
vBShout.userOptions.idle[instanceId].unIdle=false;vBShout.userOptions.idle[instanceId].unPause=false;}
if(paused[instanceId]==true)
{continue;}
idleTime[instanceId]++;if(idleTime[instanceId]>=vBShout.instanceOptions[instanceId].idletimeout&&vBShout.instanceOptions[instanceId].idletimeout>0||(!hasFetched[instanceId]&&vBShout.instancePermissions[instanceId].autoidle))
{paused[instanceId]=true;setMessage(vbphrase['dbtech_vbshout_flagged_idle'].replace('%link%','return vBShout_unIdle(\''+instanceId+'\', false, \'unidle\');'),'notice',instanceId);var imgElem=fetchElem('active',instanceId,'img'),imgElemSrc=imgElem.attr('src');if(imgElem.length)
{imgElem.attr('src',imgElemSrc.replace('offline','online'));}
if(!hasFetched[instanceId])
{fetchShouts(instanceId,'shouts',true);}
continue;}
if(--countDown[instanceId]>0)
{}
else
{if(!hasFetched[instanceId])
{fetchShouts(instanceId,'shouts',true);hasFetched[instanceId]=true;}
else
{fetchShouts(instanceId);}}}},1000);}
function createTab(tabId,instanceId,tabText,canClose,extraParams)
{if(tabList[instanceId][tabId])
{fetchElem('tab',instanceId,'div','[data-tabid="'+tabId+'"]').trigger('click');return false;}
tabList[instanceId][tabId]=true;fetchElem('tabs',instanceId,'table',' > tbody > tr > td').filter(':last').before('<td class="dbtech_vbshout_tabcontainer"><div name="dbtech_vbshout_tab" data-instanceid="'+instanceId+'" data-tabid="'+tabId+'" class="dbtech_vbshout_tabs alt2">'+tabText+(canClose=='1'?' [<a href="javascript://" name="dbtech_vbshout_closetab" data-instanceid="'+instanceId+'" data-tabid="'+tabId+'">X</a>]':'')+'</div></td>');if(typeof extraParams=='undefined')
{return true;}
var newTab=fetchElem('tab',instanceId,'div','[data-tabid="'+tabId+'"]');for(var i in extraParams)
{newTab.attr('data-'+i,extraParams[i]);}
return true;};function closeTab(tabId,instanceId)
{tabList[instanceId][tabId]=false;if(tab[instanceId]==tabId)
{console.log(timeStamp()+"Attempting to switching from %s to %s for instance %s.",tab[instanceId],'shouts',instanceId);fetchElem('tab',instanceId,'div','[data-tabid="shouts"]').trigger('click');}
fetchElem('tab',instanceId,'div','[data-tabid="'+tabId+'"]').remove();return true;};function setShoutTarget(username,instanceId)
{var everyone=fetchElem('everyone',instanceId,'span');if(everyone)
{if(!username)
{everyone.html(vbphrase['dbtech_vbshout_everyone']);}
else
{everyone.html($.trim(username));}}};function closeMenu(instanceId)
{if(menuId[instanceId])
{fetchElem('menu',instanceId,'ul','[data-userid="'+menuId[instanceId]+'"]').fadeOut('fast');menuId[instanceId]=0;}};function beginShoutEdit(instanceId,shoutid)
{paused[instanceId]=true;shoutId[instanceId]=shoutid;fetchElem('editor',instanceId).val(fetchElem('shout_raw',instanceId,'input','[data-shoutid="'+shoutid+'"]').val());fetchElem('editor',instanceId).trigger('keyup');fetchElem('target',instanceId,'span').fadeOut('fast').promise().done(function()
{fetchElem('delete',instanceId,'span').fadeIn('fast');fetchElem('cancel',instanceId,'span').fadeIn('fast');fetchElem('editing',instanceId,'span').fadeIn('fast');});};function deleteShout(instanceId,shoutid)
{console.log(timeStamp()+'Attempting to delete shout: %s... in instance %s',shoutid,instanceId);var extraParams={};if(pmUserId[instanceId])
{extraParams['type']='pm_'+pmUserId[instanceId]+'_';}
else if(chatroom[instanceId])
{extraParams['type']='chatroom_'+chatroom[instanceId]+'_';}
extraParams['shoutid']=shoutid;paused[instanceId]=true;vBShout_unIdle(instanceId,true,'deleteshout');ajaxCall('delete',instanceId,extraParams);fetchElem('cancelbutton',instanceId,'input').trigger('click');};function saveShout(instanceId)
{var editor=fetchElem('editor',instanceId);if(!editor.val().length)
{return false;}
vBShout.instanceOptions[instanceId].floodchecktime=parseInt(vBShout.instanceOptions[instanceId].floodchecktime);if(shoutDelay[instanceId])
{setMessage(vbphrase['dbtech_vbshout_must_wait_x_seconds'].replace('%time%',vBShout.instanceOptions[instanceId].floodchecktime).replace('%time2%',(vBShout.instanceOptions[instanceId].floodchecktime-shoutDelay[instanceId])),'error',instanceId);return false;}
var extraParams={};if(shoutId[instanceId])
{console.log(timeStamp()+'Attempting to save shout: %s to instance: %s...',shoutId[instanceId],instanceId);}
else
{console.log(timeStamp()+'Attempting to insert shout to instance: %s...',instanceId);}
if(pmUserId[instanceId])
{extraParams['type']='pm_'+pmUserId[instanceId]+'_';}
else if(chatroom[instanceId])
{extraParams['type']='chatroom_'+chatroom[instanceId]+'_';}
extraParams['message']=PHP.urlencode($.trim(editor.val()));paused[instanceId]=true;vBShout_unIdle(instanceId,true);shoutDelay[instanceId]=vBShout.instanceOptions[instanceId].floodchecktime;ajaxCall('save',instanceId,extraParams);if(shoutId[instanceId])
{fetchElem('cancelbutton',instanceId).trigger('click');}
else
{editor.val('');}
return false;};function saveStyleProps(instanceId,type,value,noupdate)
{var property=type;var setValue=value;switch(type)
{case'bold':property='fontWeight';setValue=(value?type:'');break;case'italic':property='fontStyle';setValue=(value?type:'');break;case'underline':property='textDecoration';setValue=(value?type:'');break;case'font':property='fontFamily';var fontfield=fetchElem('fontbar',instanceId,'div'),fontrow=fetchElem('fontrow',instanceId,'ul');if(fontfield.text()!=value)
{fontfield.text(value);fontrow.val(value);}
break;case'size':property='fontSize';var sizefield=fetchElem('sizebar',instanceId,'div'),sizerow=fetchElem('sizerow',instanceId,'ul');if(sizefield.text()!=value)
{sizefield.text(value);sizerow.val(value);}
break;case'color':var colorfield=fetchElem('colorbar',instanceId,'img'),colorrow=fetchElem('colorrow',instanceId,'ul');if(rgbToHex(colorfield.css('backgroundColor'))!=value)
{colorfield.css('backgroundColor',value);}
break;}
console.log(timeStamp()+"Style property %s set. Value: %s. Instance ID: %s",type,setValue,instanceId);if(type=='size')
{fetchElem('frame',instanceId,'div').css(property,setValue);}
else
{fetchElem('editor',instanceId).css(property,setValue);}
if(vBShout.editorOptions[instanceId][type]!=value&&!noupdate)
{console.log(timeStamp()+"Style property %s changed. Old value: %s - New value: %s. Instance ID: %s",type,vBShout.editorOptions[instanceId][type],value,instanceId);vBShout.editorOptions[instanceId][type]=value;var extraParams={};extraParams['editor']=vBShout.editorOptions[instanceId];if(pmUserId[instanceId])
{extraParams['type']='pm_'+pmUserId[instanceId]+'_';}
else if(chatroom[instanceId])
{extraParams['type']='chatroom_'+chatroom[instanceId]+'_';}
paused[instanceId]=true;vBShout_unIdle(instanceId,true,'savestyles');ajaxCall('styleprops',instanceId,extraParams);}};function fetchShouts(instanceId,type,force)
{paused[instanceId]=true;if(!type)
{type=tab[instanceId];}
if(!(idleTime[instanceId]>=vBShout.instanceOptions[instanceId].idletimeout&&vBShout.instanceOptions[instanceId].idletimeout>0)||force==true)
{var extraParams={};if(typeof aopTime[instanceId][tab[instanceId]]=='undefined')
{force=true;}
switch(type)
{case'shout':extraParams['shoutid']=shoutId[instanceId];break;}
for(var i in tabList[instanceId])
{if(tabList[instanceId])
{extraParams['tabs['+i+']']=1;}}
extraParams['type']=type;if((vBShout.instanceOptions[instanceId].optimisation&&!force)&&type!='activeusers')
{if(type=='shouts'||type=='aop')
{type='shouts'+instanceId;}
if(type=='shoutnotifs'||type=='systemmsgs')
{type=type+instanceId;}
console.log(timeStamp()+'Fetching '+type+'...');aopFile[instanceId]='dbtech/vbshout/aop/'+type+'.txt';$.ajax({type:'GET',url:aopFile[instanceId]+'?v='+Math.random()*99999999999999,complete:function(xhr,statusText){},success:function(data,statusText,xhr)
{var d=new Date();var dateline=data;var timenow=parseInt(d.getTime()/1000);if(dateline>aopTime[instanceId][tab[instanceId]])
{console.log(timeStamp()+tab[instanceId]+" AOP file returned new shouts: \n"+dateline+"\n"+aopTime[instanceId][tab[instanceId]]);fetchShouts(instanceId,tab[instanceId],true);return false;}
if(dateline==0)
{console.log(timeStamp()+"AOP file returned 0");fetchShouts(instanceId,tab[instanceId],true);return false;}
if((timenow-dateline)>60)
{console.log(timeStamp()+"AOP file hasn't been modified for 60 seconds: "+(timenow-dateline));aopTime[instanceId][tab[instanceId]]=(timenow+5);fetchShouts(instanceId,tab[instanceId],true);return false;}
else
{paused[instanceId]=false;countDown[instanceId]=vBShout.instanceOptions[instanceId]['refresh'];}},error:function(data,statusText,error)
{fetchShouts(instanceId,tab[instanceId],true);}});}
else
{ajaxCall('fetch',instanceId,extraParams,'GET');}}};function ajaxCall(varname,instanceId,extraParams,type)
{if(typeof type=='undefined')
{type='POST';extraParams['securitytoken']=SECURITYTOKEN;}
extraParams['do']='ajax';extraParams['action']=varname;extraParams['instanceid']=instanceId;extraParams['tabid']=tab[instanceId];if(vBShout.userOptions.is_detached=='1')
{extraParams['detached']='1';}
if(pmUserId[instanceId])
{extraParams['pmuserid']=pmUserId[instanceId];}
if(chatroom[instanceId])
{extraParams['chatroomid']=chatroom[instanceId];}
if(shoutId[instanceId])
{extraParams['shoutid']=shoutId[instanceId];}
extraParams['shoutorder']=vBShout.instanceOptions[instanceId].shoutorder;extraParams['pmtime']=vBShout.userOptions.pmtime;if(type=='GET')
{extraParams['v']=Math.random()*99999999999999;}
$.ajax({type:type,url:'vbshout.php',data:(SESSIONURL?SESSIONURL+'&':'')+$.param(extraParams),complete:function(xhr,statusText){},success:function(data,statusText,xhr)
{var tagData=$(data);data={};paused[instanceId]=false;countDown[instanceId]=vBShout.instanceOptions[instanceId]['refresh'];if(!hasFetched[instanceId]&&vBShout.instancePermissions[instanceId].autoidle)
{paused[instanceId]=hasFetched[instanceId]=true;}
var arrVals=['aoptimes','chatrooms','shouts'],singleVals=['ajax','activereports','activeusers2','content','editor','error','pmtime','pmuserid','sticky'];if(tagData.find('activeusers').length)
{var tagData2=tagData.find('activeusers');data['activeusers']={'count':tagData2.attr('count'),usernames:tagData2.text()};}
if(tagData.find('chatroom').length)
{var tagData2=tagData.find('chatroom');if(typeof tagData2.attr('chatroomid')!=undefined)
{data['chatroom']={'chatroomid':tagData2.attr('chatroomid'),'title':tagData2.text()};}}
for(var i in singleVals)
{if(tagData.find(singleVals[i]).length)
{data[singleVals[i]]=tagData.find(singleVals[i]).text();}}
for(var i in arrVals)
{if(tagData.find(arrVals[i]).length)
{data[arrVals[i]]={};tagData.find(arrVals[i]).children().each(function(j)
{data[arrVals[i]][j]={};$(this).children().each(function()
{data[arrVals[i]][j][$(this).prop('tagName')]=$(this).text();});});}}
if(data.error)
{var tmp=data.error.split('_');if(tmp[0]=='disband')
{var chatroomid=parseInt(tmp[1]);if(chatroomid)
{if(chatroom[instanceId]==chatroomid)
{chatroom[instanceId]=0;fetchElem('tab',instanceId,'div','[data-tabid="shouts"]').trigger('click');}
closeTab(fetchElem('tab',instanceId,'div','[data-tabid^="chatroom_'+chatroomid+'_"]').attr('data-tabid'),instanceId);return false;}}
setMessage(data.error,'error',instanceId);fetchElem('editor',instanceId).val(editorContents[instanceId]);console.error(timeStamp()+"AJAX Error: %s",data.error);return true;}
if(typeof data.sticky!='undefined')
{if(!data.sticky)
{fetchElem('frame_sticky',instanceId,'div').fadeOut('fast');}
else
{setMessage(data.sticky,'sticky',instanceId);}}
if(typeof data.activeusers!='undefined')
{fetchElem('activeusers',instanceId,'span').text(data.activeusers.count);fetchElem('sidebar',instanceId,'div').html(data.activeusers.usernames);}
if(typeof data.activereports!='undefined')
{fetchElem('shoutreports',instanceId,'span').text(data.activereports);}
if(typeof data.editor!='undefined')
{fetchElem('editor',instanceId).val(data.editor);fetchElem('editor',instanceId).trigger('keyup');}
if(typeof data.content!='undefined')
{fetchElem('content',instanceId,'div').html(data.content);}
if(typeof data.archive!='undefined'&&typeof data.shoutid!='undefined')
{fetchElem('message_'+data.shoutid,instanceId).html(data.archive);}
if(typeof data.pmuserid!='undefined')
{var tmp=fetchElem('lookup',instanceId,'input');userIds[data.pmuserid]=tmp.val();createTab('pm_'+data.pmuserid+'_'+instanceId,instanceId,tmp.val(),'1',{'userid':data.pmuserid});fetchElem('tab',instanceId,'div','[data-tabid="pm_'+data.pmuserid+'_'+instanceId+'"]').trigger('click');tmp.val('');}
if(typeof data.chatroom!='undefined')
{fetchElem('roomname',instanceId,'input').val('');createTab('chatroom_'+data.chatroom.chatroomid+'_'+instanceId,instanceId,data.chatroom.title,'1',{'chatroomid':data.chatroom.chatroomid});fetchElem('tab',instanceId,'div','[data-tabid="chatroom_'+data.chatroom.chatroomid+'_'+instanceId+'"]').trigger('click');}
if(typeof data.aoptime!='undefined')
{if(aopTime[instanceId][tab[instanceId]]<data.aoptime||!aopTime[instanceId][tab[instanceId]])
{aopTime[instanceId][tab[instanceId]]=data.aoptime;}}
if(typeof data.pmtime!='undefined')
{if(pmTime[instanceId]<data.pmtime)
{console.log(timeStamp()+"Playing pm sound for tab %s in instance %s",tab[instanceId],instanceId);playSound('pm',instanceId);pmTime[instanceId]=data.pmtime;}}
if(typeof data.aoptimes!='undefined')
{for(var i in data.aoptimes)
{var aoptime=data.aoptimes[i].aoptime;var tabid=data.aoptimes[i].tabid;var nosound=data.aoptimes[i].nosound;if(!aopTime[instanceId][tabid])
{aopTime[instanceId][tabid]=aoptime;continue;}
if(aopTime[instanceId][tabid]>=aoptime)
{continue;}
console.log(timeStamp()+"Tab: %s\nAOP: %s\nPrevious AOP: %s\nInstance: %s",tabid,aoptime,aopTime[instanceId][tabid],instanceId);aopTime[instanceId][tabid]=aoptime;if(nosound=='0')
{console.log(timeStamp()+"Playing shout sound for tab %s in instance %s",tabid,instanceId);playSound('shout',instanceId);}
if(tabid!=tab[instanceId]&&nosound=='0')
{fetchElem('tab',instanceId,'div','[data-tabid="'+tabid+'"]').addClass('dbtech_vbshout_highlight');}}}
if(typeof data.chatrooms!='undefined')
{for(var i in data.chatrooms)
{if(!tabList[instanceId]['chatroom_'+data.chatrooms[i].chatroomid+'_'+data.chatrooms[i].instanceid])
{playSound('invite',instanceId);if(confirm(vbphrase['dbtech_vbshout_are_you_sure_chatjoin'].replace(/%roomname%/igm,data.chatrooms[i].title).replace(/%username%/igm,data.chatrooms[i].username)))
{createTab('chatroom_'+data.chatrooms[i].chatroomid+'_'+data.chatrooms[i].instanceid,instanceId,data.chatrooms[i].title,'1',{'chatroomid':data.chatrooms[i].chatroomid});paused[instanceId]=true;vBShout_unIdle(instanceId,false,'joinchat');ajaxCall('joinchat',instanceId,{'chatroomid':data.chatrooms[i].chatroomid});fetchElem('tab',instanceId,'div','[data-tabid="chatroom_'+data.chatrooms[i].chatroomid+'_'+data.chatrooms[i].instanceid+'"]').trigger('click');}
else
{paused[instanceId]=true;vBShout_unIdle(instanceId,false,'leavechat');ajaxCall('leavechat',instanceId,{'chatroomid':data.chatrooms[i].chatroomid,'status':1});}}}}
if(typeof data.shouts!='undefined')
{fetchElem('menucode',instanceId,'div').children('ul').remove();menuId[instanceId]=0;var shoutIds=[],shoutsById=[];for(var i in data.shouts)
{data.shouts[i].hidenewshout=!(fetchElem('shoutwrapper',instanceId,'[data-shoutid="'+data.shouts[i].shoutid+'"]').length);shoutIds[i]=data.shouts[i];shoutsById[data.shouts[i].shoutid]=true;}
fetchElem('shoutwrapper',instanceId,'div').each(function(index,element)
{if(!shoutsById[$(this).attr('data-shoutid')])
{$(this).fadeOut('fast');}});if(shoutIds.length)
{fetchElem('shoutwrapper',instanceId,'div').promise().done(function()
{var contentObj=fetchElem('content',instanceId);contentObj.html('');for(var i in shoutIds)
{var shout=shoutIds[i];shout.permissions=$.parseJSON(shout.permissions);fetchElem('shouttype_'+shout.template,instanceId,'script').tmpl(shout).appendTo(contentObj);fetchElem('memberaction_dropdown',instanceId,'script').tmpl(shout).appendTo(fetchElem('menu',instanceId,'ul','[data-userid="'+shout.userid+'"][data-shoutid="'+shout.shoutid+'"]'));fetchElem('menucode',instanceId,'div').append(fetchElem('menu',instanceId,'ul','[data-userid="'+shout.userid+'"][data-shoutid="'+shout.shoutid+'"]'));}
fetchElem('memberurl',instanceId,'a').each(function(index,element)
{$(this).attr('href','member.php?'+SESSIONURL+'u='+$(this).attr('data-userid'));});fetchElem('shoutwrapper',instanceId,'div').filter(':hidden').fadeIn('fast');});fetchElem('shoutwrapper',instanceId,'div').promise().done(function()
{if(vBShout.instanceOptions[instanceId]['shoutorder']=='ASC')
{fetchElem('frame',instanceId).scrollTop(99999999);}});}}},error:function(data,statusText,error)
{paused[instanceId]=false;countDown[instanceId]=vBShout.instanceOptions[instanceId]['refresh'];try
{if(data.statusText=='communication failure'||data.statusText=='transaction aborted'||data.status==0)
{return false;}
fetchElem('editor',instanceId).val(editorContents[instanceId]);setMessage(data.status+' '+textStatus,'error',instanceId);console.error(timeStamp()+"AJAX Error: Status = %s: %s",data.status,data.statusText);}
catch(e)
{console.error(timeStamp()+"AJAX Error: %s",data.responseText);}}});};function playSound(sound,instanceId)
{vBShout.userOptions.soundSettings=vBShout.userOptions.soundSettings||{};vBShout.userOptions.soundSettings[instanceId]=vBShout.userOptions.soundSettings[instanceId]||{};vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]||'1';if(vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=='0')
{return true;}
fetchElem('sound_'+sound,instanceId).trigger('play');};function setMuteButton(instanceId)
{vBShout.userOptions.soundSettings=vBShout.userOptions.soundSettings||{};vBShout.userOptions.soundSettings[instanceId]=vBShout.userOptions.soundSettings[instanceId]||{};vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]||'1';var thisButton=fetchElem('soundbutton',instanceId,'img');if(thisButton.length)
{thisButton.attr('src',thisButton.attr('src').replace('sound_'+(vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=='1'?'off':'on')+'.png','sound_'+(vBShout.userOptions.soundSettings[instanceId][tab[instanceId]]=='0'?'off':'on')+'.png'));}};function setInvisibleButton(instanceId)
{vBShout.userOptions.invisible=vBShout.userOptions.invisible||{};vBShout.userOptions.invisible[instanceId]=vBShout.userOptions.invisible[instanceId]||'0';var thisButton=fetchElem('active',instanceId,'img'),thisButtonSrc=thisButton.attr('src');if(thisButton.length);{if(vBShout.userOptions.invisible[instanceId]=='1')
{thisButtonSrc=thisButtonSrc.replace('online','invisible').replace('offline','invisible');}
else
{thisButtonSrc=thisButtonSrc.replace('invisible','offline');}
thisButton.attr('src',thisButtonSrc);}};function setMessage(msg,type,instanceId)
{console.log(timeStamp()+"Setting %s: %s",type.charAt(0).toUpperCase()+''+type.substr(1),msg);if(fetchElem('frame',instanceId,'div').length)
{fetchElem('message_'+type,instanceId,'span').html(msg);if(type!='sticky'&&type!='notice')
{var notice=fetchElem('frame_'+type,instanceId,'div');notice.fadeIn('fast').promise().done(function()
{if(vBShout.instanceOptions[instanceId]['shoutorder']=='ASC')
{fetchElem('frame',instanceId).scrollTop(99999999);}});setTimeout(function()
{notice.fadeOut('fast');},5000);}
else
{fetchElem('frame_'+type,instanceId,'div').show();if(vBShout.instanceOptions[instanceId]['shoutorder']=='ASC')
{fetchElem('frame',instanceId).scrollTop(99999999);}}}};function timeStamp()
{var d=new Date();return'['+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds()+'] ';};function fetchElem(elemName,instanceId,el,extraFilter)
{return $((el?el:'')+'[name="dbtech_vbshout_'+elemName+'"]'+(instanceId?'[data-instanceid='+instanceId+']':'')+(extraFilter?extraFilter:''));};function rgbToHex(colorStr)
{var hex='#';$.each(colorStr.substring(4).split(','),function(i,str)
{var h=($.trim(str.replace(')',''))*1).toString(16);hex+=(h.length==1)?"0"+h:h;});return hex;};});function vBShout_unIdle(instanceId,keepPause,unIdleAction)
{vBShout.userOptions.idle[instanceId].unIdle=true;vBShout.userOptions.idle[instanceId].unPause=(keepPause?false:true);vBShout.userOptions.idle[instanceId].forceUnIdle=true;return false;};function vBShout_initSmilies(smilie_container,instanceId)
{if(smilie_container!=null)
{var editdoc=window.opener.document.getElementById('dbtech_vbshout_editor'+instanceId);jQueryDupe('img[id^="smilie_"]',smilie_container).each(function(index,element)
{var thisSmiley=jQueryDupe(this);thisSmiley.css('cursor','pointer');thisSmiley.attr('unselectable','on');thisSmiley.on('click',function(e)
{var text=' '+this.alt;if(!editdoc.hasfocus||(is_moz&&is_mac))
{editdoc.focus();if(is_opera)
{editdoc.focus();}}
if(typeof(editdoc.selectionStart)!='undefined')
{var opn=editdoc.selectionStart+0;var scrollpos=editdoc.scrollTop;editdoc.value=editdoc.value.substr(0,editdoc.selectionStart)+text+editdoc.value.substr(editdoc.selectionEnd);if(movestart===false)
{}
else if(typeof movestart!='undefined')
{editdoc.selectionStart=opn+movestart;editdoc.selectionEnd=opn+text.vBlength()-moveend;}
else
{editdoc.selectionStart=opn;editdoc.selectionEnd=opn+text.vBlength();}
editdoc.scrollTop=scrollpos;}
else if(document.selection&&document.selection.createRange)
{var sel=document.selection.createRange();sel.text=text.replace(/\r?\n/g,'\r\n');if(movestart===false)
{}
else if(typeof movestart!='undefined')
{if((movestart-text.vBlength())!=0)
{sel.moveStart('character',movestart-text.vBlength());selection_changed=true;}
if(moveend!=0)
{sel.moveEnd('character',-moveend);selection_changed=true;}}
else
{sel.moveStart('character',-text.vBlength());selection_changed=true;}
if(selection_changed)
{sel.select();}}
else
{editdoc.value+=text;}
return false;});});}};