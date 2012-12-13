/*======================================================================*\
|| #################################################################### ||
|| # vBulletin 3.6.7 PL1
|| # ---------------------------------------------------------------- # ||
|| # Copyright ©2000-2007 Jelsoft Enterprises Ltd. All Rights Reserved. ||
|| # This file may not be redistributed in whole or significant part. # ||
|| # ---------------- VBULLETIN IS NOT FREE SOFTWARE ---------------- # ||
|| # http://www.vbulletin.com | http://www.vbulletin.com/license.html # ||
|| #################################################################### ||
\*======================================================================*/

/**
* Adds onclick events to appropriate elements for submitting the form
* Each form to be activated should be specified as a separate argument, eg: vB_AJAX_Userlist_Init('form1_id', 'form2_id', 'form3_id');
*
* @param	string	form elements to attach vB_AJAX_Userlist to.
*/
function vB_AJAX_Userlist_Init(forms)
{
	// this can count as a "problematic" AJAX function, as usernames won't be found without iconv
	if (AJAX_Compatible && (typeof vb_disable_ajax == 'undefined' || vb_disable_ajax == 0))
	{
		for (var i = 0; i < arguments.length; i++)
		{
			var form = document.getElementById(arguments[i]);
			if (form)
			{
				form.onsubmit = vB_AJAX_Userlist.prototype.form_click;
			}
		}
	}
};

/**
* Class to handle userlist modifications
*
* @param	object	The form object containing the list elements
*/
function vB_AJAX_Userlist(formobj)
{
	// AJAX handler
	this.xml_sender = null;

	// vB_Hidden_Form object to handle form variables
	this.pseudoform = new vB_Hidden_Form('');
	this.pseudoform.add_variable('ajax', 1);
	this.pseudoform.add_variables_from_object(formobj);

	this.list = formobj.id.replace(/userlist_(\w+)form/, '$1');

	// Closure
	var me = this;

	/**
	* OnReadyStateChange callback. Uses a closure to keep state.
	* Remember to use me instead of this inside this function!
	*/
	this.handle_ajax_response = function()
	{
		if (me.xml_sender.handler.readyState == 4 && me.xml_sender.handler.status == 200)
		{
			if (fetch_object('userfield_' + me.list + '_progress'))
			{
				fetch_object('userfield_' + me.list + '_progress').style.display = 'none';
			}
			if (me.xml_sender.handler.responseXML)
			{
				// check for error first
				var error = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'error')[0]);
				if (error)
				{
					// show error
					fetch_object('userfield_' + me.list + '_errortext').innerHTML = error;
					fetch_object('userfield_' + me.list + '_error').style.display = '';
				}
				else
				{
					// hide error
					fetch_object('userfield_' + me.list + '_error').style.display = 'none';

					fetch_object('userfield_' + me.list + '_txt').value = '';
					fetch_object(me.list + 'list1').innerHTML = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'listbit1')[0]);
					fetch_object(me.list + 'list2').innerHTML = me.xml_sender.fetch_data(fetch_tags(me.xml_sender.handler.responseXML, 'listbit2')[0]);
				}
			}

			if (is_ie)
			{
				me.xml_sender.handler.abort();
			}
		}
	}
};

/**
* Submit the form
*/
vB_AJAX_Userlist.prototype.submit_form = function(action)
{
	if (fetch_object('userfield_' + this.list + '_progress'))
	{
		fetch_object('userfield_' + this.list + '_progress').style.display = '';
	}
	this.xml_sender = new vB_AJAX_Handler(true);
	this.xml_sender.onreadystatechange(this.handle_ajax_response);
	this.xml_sender.send(
		action,
		this.pseudoform.build_query_string()
	);
};

/**
* Handles the form 'submit' action
*/
vB_AJAX_Userlist.prototype.form_click = function()
{
	var AJAX_Userlist = new vB_AJAX_Userlist(this);
	AJAX_Userlist.submit_form(this.action);
	return false;
};

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 18:52, Sat Jul 14th 2007
|| # CVS: $RCSfile$ - $Revision: 16602 $
|| ####################################################################
\*======================================================================*/