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
* TODO:
* Language independence for DatePicker (hard coded months/days)
*/

// #############################################################################
// vB_DatePicker
// #############################################################################

vBulletin.events.systemInit.subscribe(function()
{
	if (vBulletin.elements["vB_DatePicker"])
	{
		for (var i = 0; i < vBulletin.elements["vB_DatePicker"].length; i++)
		{
			new vB_DatePicker(vBulletin.elements["vB_DatePicker"][i][0].id, vBulletin.elements["vB_DatePicker"][i][1]);
		}
		vBulletin.elements["vB_DatePicker"] = null;
	}
});

// =============================================================================

/**
* vBulletin form elements with pop-up calendar
*
* @param	string	Element next to which the popup button will be placed
* @param	string	Arguments in the form of "int:week start day,string:element basename"
*/
function vB_DatePicker(button_sibling, argstring)
{
	var args = argstring.split(",");

	// Common base name for all HTML elements used by this object
	var base_id = (typeof(args[1]) == "undefined" ? "" : args[1]);
	this.button_sibling = YAHOO.util.Dom.get(button_sibling);

	if (!this.button_sibling)
	{
		vBulletin.console("Button sibling missing from vB_DatePicker (%s)", base_id);
		return false;
	}

	// Work out whether we are using <select> or <input type="text"> based output
	this.datestring = YAHOO.util.Dom.get(base_id + "datestring");
	if (!this.datestring)
	{
		this.month_element = YAHOO.util.Dom.get(base_id + "month");
		this.date_element  = YAHOO.util.Dom.get(base_id + "date");
		this.year_element  = YAHOO.util.Dom.get(base_id + "year");

		if (!this.month_element || !this.date_element || !this.year_element)
		{
			vBulletin.console("Elements missing from vB_DatePicker (%s)", base_id);
			return false;
		}
	}

	this.hidden_selects = new Array();

	// Date object representing the currently-selected date
	this.selected_date = this.read_input();

	// Date object representing the currently-displayed month
	this.current_month = new Date(this.selected_date);
	this.current_month.setFullYear(this.selected_date.getFullYear(), this.selected_date.getMonth(), 1);

	// Get today
	var tmp = new Date();
	this.today = new Date(0);
	this.today.setFullYear(tmp.getFullYear(), tmp.getMonth(), tmp.getDate());
	this.today.setHours(0, 0, 0);

	// Week start day stuff
	var week_start_day = (parseInt(args[0]) - 1) % 7;
	if (week_start_day < 0)
	{
		week_start_day = 0;
	}

	this.userweek = new Array();
	while (this.userweek.length < 7)
	{
		this.userweek[this.userweek.length] = week_start_day++;
		if (week_start_day >= 7)
		{
			week_start_day = 0;
		}
	}

	this.daynames = new Array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
	this.monthnames = new Array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

	if (this.button_sibling)
	{
		YAHOO.util.Event.on(document, "click", this.close_popup, this, true);
		if (!is_ie)
		{
			YAHOO.util.Event.on(window, "resize", this.close_popup, this, true);
		}

		// controls menu popup
		this.button = this.button_sibling.parentNode.insertBefore(document.createElement("a"), this.button_sibling.nextSibling);
		this.button.href = "#";
		this.buttonimg = this.button.appendChild(document.createElement("img"));
		this.buttonimg.src = IMGDIR_MISC + "/calendar_popup.png";
		if (is_ie)
		{
			this.buttonimg.style.verticalAlign = "text-bottom";
		}
		else
		{
			this.buttonimg.style.verticalAlign = "bottom";
		}
		this.buttonimg.border = "0";
		YAHOO.util.Event.on(this.button, "click", this.toggle_calendar, this, true);

		if (is_ie)
		{
			this.div = this.button_sibling.parentNode.insertBefore(document.createElement("div"), this.button_sibling);
			this.div.style.position = "absolute";
		}

		// the popup calendar
		this.popup = (is_ie ? this.div : document.body).appendChild(document.createElement("div"));
		this.popup.style.position = "absolute";
		this.popup.style.display = "none";
		this.popup.style.top = this.button_sibling.offsetHeight + "px";

		this.popup_state = false;

		this.build_calendar();
	}
};

/**
* Initial creation of the calendar popup element
*/
vB_DatePicker.prototype.build_calendar = function()
{
	// Remove existing calendar if there is one
	if (this.table && this.table.parentNode)
	{
		this.table.parentNode.removeChild(this.table);
	}

	// create a temp element that is to be used to get the fore-
	// and background colors of the page class
	var tmp_span = document.createElement("span");
	tmp_span.className = "page";
	tmp_span.innerHTML = '&nbsp;';
	document.body.appendChild(tmp_span);
	var page_back = YAHOO.util.Dom.getStyle(tmp_span, "backgroundColor");
	var page_front = YAHOO.util.Dom.getStyle(tmp_span, "color");
	document.body.removeChild(tmp_span);

	// Containing table for calendar
	this.table = document.createElement("table");
	this.table.cellSpacing = 1;
	this.table.className = "tborder vB_DatePicker page";
	this.table.style.background = page_back;
	var thead = this.table.appendChild(document.createElement("thead"));

	// Month name and next/prev controls
	var tr = thead.appendChild(document.createElement("tr"));
	var prevbutton = tr.appendChild(document.createElement("th"));
	this.tabletitle = tr.appendChild(document.createElement("th"));
	var nextbutton = tr.appendChild(document.createElement("th"));

	tr.align = "center";
	this.tabletitle.className = "tcat smallfont";
	this.tabletitle.colSpan = 5;
	this.tabletitle.innerHTML = "&nbsp;";
	prevbutton.className = "tcat smallfont";
	prevbutton.innerHTML = "&lt;";
	prevbutton.style.cursor = "pointer";
	prevbutton.increment = -1;
	YAHOO.util.Event.on(prevbutton, "click", this.change_month, this, true);
	nextbutton.className = "tcat smallfont";
	nextbutton.innerHTML = "&gt;";
	nextbutton.style.cursor = "pointer";
	nextbutton.increment = 1;
	YAHOO.util.Event.on(nextbutton, "click", this.change_month, this, true);

	// Day name headers
	var tr = thead.appendChild(document.createElement("tr"));
	tr.align = "center";
	tr.className = "page smallfont";
	for (var i in this.userweek)
	{
		var td = tr.appendChild(document.createElement("td"));
		td.className = "smallfont";
		td.appendChild(document.createTextNode(this.daynames[this.userweek[i]].substring(0, 1)));
	}

	var tr = thead.appendChild(document.createElement("tr"));
	var td = tr.appendChild(document.createElement("td"));
		td.colSpan = 7;
		td.className = "page"
	var div = td.appendChild(document.createElement("div"));
		div.style.background = page_front;
	var img = div.appendChild(document.createElement("img"));
		img.src = (typeof(CLEARGIFURL) != 'undefined' ? CLEARGIFURL : 'clear.gif'); // defined in script header of print_cp_header()

	this.tbody = this.table.appendChild(document.createElement("tbody"));

	this.draw_date_cells(this.selected_date.getMonth() + 1, this.selected_date.getFullYear());

	this.popup.appendChild(this.table);
};

/**
* Draws (or redraws) the cells of the date picker table that contain the individual dates
*
* @param	integer	Month (1-12)
* @param	integer	Year (1970-2037)
*/
vB_DatePicker.prototype.draw_date_cells = function(month, year)
{
	// Work out month start date
	this.current_month = new Date(0);
	this.current_month.setFullYear(year, month - 1, 1);

	// Set table title
	this.tabletitle.innerHTML = this.monthnames[this.current_month.getMonth()] + " " + this.current_month.getFullYear();

	// Remove existing date cells if there are any
	while (this.tbody.hasChildNodes())
	{
		this.tbody.removeChild(this.tbody.firstChild);
	}

	// Work out what day is the first shown in the calendar
	var monthstartday = this.current_month.getDay();
	var beforedays = 0;
	for (i in this.userweek)
	{
		if (monthstartday == this.userweek[i])
		{
			break;
		}
		else
		{
			beforedays++;
		}
	}

	var curday = new Date(0);
	curday.setFullYear(this.current_month.getFullYear(), this.current_month.getMonth(), 1 - beforedays);

	// Populate the calendar with dates
	for (var row = 0; row < 6; row++)
	{
		var tr = this.tbody.appendChild(document.createElement("tr"));
			tr.align = "center";

		for (i in this.userweek)
		{
			var td = tr.appendChild(document.createElement("td"))
			td.innerHTML = (curday.getDate() < 10 ? "&nbsp;" : "") + curday.getDate();
			td.dateobj = new Date(curday);
			td.title = td.dateobj.toString();//this.daynames[td.dateobj.getDay()] + ", " + this.monthnames[td.dateobj.getMonth()] + " " + td.dateobj.getDate() + " " + td.dateobj.getFullYear();
			td.style.cursor = "pointer";
			YAHOO.util.Event.on(td, "click", this.date_click, this, true);
			YAHOO.util.Event.on(td, "mouseover", this.date_mouseover, this, true);
			YAHOO.util.Event.on(td, "mouseout", this.date_mouseover, this, true);

			curday.setDate(curday.getDate() + 1);
			curday.setHours(0, 0, 0);
		}
	}

	// Apply classes to all the date cells
	this.apply_date_classes();
}

/**
* Applies or re-applies the CSS classes to the date elements to indicate selection, current month etc.
*/
vB_DatePicker.prototype.apply_date_classes = function()
{
	var tds = this.tbody.getElementsByTagName("td");
	for (var i = 0; i < tds.length; i++)
	{
		if (tds[i].dateobj.valueOf() == this.selected_date.valueOf())
		{
			tds[i].className = "tfoot smallfont";
		}
		else if (tds[i].dateobj.getMonth() == this.current_month.getMonth())
		{
			tds[i].className = "smallfont";
		}
		else
		{
			tds[i].className = "time smallfont";
		}

		if (tds[i].dateobj.valueOf() == this.today.valueOf())
		{
			tds[i].className += " today";
		}
	}
}

/**
* Selects the appropriate date in the picker and redraws or restyles cells accordingly
*
* @param	date	Javascript Date object representing the day to be selected
*/
vB_DatePicker.prototype.select_date = function(dateobj)
{
	this.selected_date = new Date(dateobj);

	if (dateobj.getMonth() != this.current_month.getMonth() || dateobj.getFullYear() != this.current_month.getFullYear())
	{
		this.draw_date_cells(this.selected_date.getMonth() + 1, this.selected_date.getFullYear());
	}
	else
	{
		this.apply_date_classes();
	}

	this.set_input();
}

/**
* Reads the values of the associated form elements and returns the selected date
*
* @return	date	Javascript Date object - returns date object for 'NOW' if fields are unreadable or missing
*/
vB_DatePicker.prototype.read_input = function()
{
	if (this.datestring)
	{
		var new_date = Date.parse(this.datestring.value);
	}
	else
	{
		var new_date = new Date(0);
		new_date.setFullYear(parseInt(this.year_element.value), (parseInt(this.month_element.value) - 1), parseInt(this.date_element.value)).valueOf();
	}

	if (isNaN(new_date))
	{
		if (this.selected_date)
		{
			return this.selected_date;
		}
		else
		{
			return new Date();
		}
	}
	else
	{
		return new Date(new_date);
	}
}

/**
* Updates the associated form elements to reflect the currently selected date
*/
vB_DatePicker.prototype.set_input = function()
{
	if (this.datestring)
	{
		this.datestring.value = this.monthnames[this.selected_date.getMonth()] + " " + this.selected_date.getDate() + " " + this.selected_date.getFullYear();
	}
	else
	{
		this.month_element.value = this.selected_date.getMonth() + 1;
		this.date_element.value = this.selected_date.getDate();
		if (this.year_element.tagName == "SELECT")
		{
			var new_year = this.selected_date.getFullYear();
			for (var i = 0; i < this.year_element.options.length; i++)
			{
				if (this.year_element.options[i].value == new_year)
				{
					this.year_element.selectedIndex = i;
					return;
				}
			}

			var opt = this.year_element.appendChild(document.createElement("option"));
				opt.value = new_year;
				opt.appendChild(document.createTextNode(new_year));
			this.year_element.selectedIndex = this.year_element.options.length - 1;
		}
		else
		{
			this.year_element.value = this.selected_date.getFullYear();
		}
	}
}

/**
* Opens the date picker popup
*/
vB_DatePicker.prototype.open_popup = function()
{
	this.selected_date = this.read_input();
	this.select_date(this.selected_date);
	this.popup.style.display = "block";

	if (is_ie)
	{
		this.popup.style.top = this.button_sibling.offsetHeight + "px";
	}
	else
	{
		var offset = this.fetch_offset(this.button_sibling);
		this.popup.style.left = offset["left"] + "px";
		this.popup.style.top  = (offset["top"] + this.button_sibling.offsetHeight) + "px";
	}

	if (document.getElementsByTagName("html")[0].getAttribute("dir").toLowerCase() == "ltr")
	{
		this.popup.style.left = (
			 (is_ie ? 0 : parseInt(this.popup.style.left)) +
			 parseInt(this.button_sibling.offsetWidth) +
			 parseInt(this.button.offsetWidth) -
			 parseInt(this.popup.offsetWidth)
			 ) +
		"px";
		vBulletin.console("DatePicker LTR: %s", this.popup.style.left);
	}
	else if (is_ie)
	{
		this.popup.style.left = parseInt(this.button_sibling.offsetWidth) + parseInt(this.button.offsetWidth) + parseInt(this.popup.offsetWidth) * -1 + "px";
		vBulletin.console("DatePicker RTL: %s", this.popup.style.left);
	}
	else
	{
		this.popup.style.left = parseInt(this.popup.style.left) - parseInt(this.button.offsetWidth) + "px";
		vBulletin.console("DatePicker RTL: %s", this.popup.style.left);
	}

	this.popup_state = true;
	this.handle_overlaps(true);
}

/**
* Closes the date picker popup
*/
vB_DatePicker.prototype.close_popup = function()
{
	this.popup.style.display = "none";
	this.popup_state = false;
	this.handle_overlaps(false);
}

/**
* Toggles the open/closed state of the date picker popup depending on its current state
*
* @param	event	Javascript event object
*/
vB_DatePicker.prototype.toggle_calendar = function(e)
{
	YAHOO.util.Event.stopEvent(e);

	if (this.popup_state)
	{
		this.close_popup();
	}
	else
	{
		this.open_popup();
	}
}

/**
* Changes the displayed month in the date picker
*
* @param	event	Javascript event object
*/
vB_DatePicker.prototype.change_month = function(e)
{
	YAHOO.util.Event.stopEvent(e);

	this.draw_date_cells(this.current_month.getMonth() + YAHOO.util.Event.getTarget(e).increment + 1, this.current_month.getFullYear());
};

/**
* Handles a date being clicked in the date picker and selects that date
*
* @param	event	Javascript event object
*/
vB_DatePicker.prototype.date_click = function(e)
{
	YAHOO.util.Event.stopEvent(e);

	this.select_date(YAHOO.util.Event.getTarget(e).dateobj);
	this.close_popup();

	this.button_sibling.focus();
	try { this.button_sibling.select(); } catch(e) {}
}

/**
* Handles a date being passed over with the mouse cursor and highlights (or reverts) the cell's CSS
*
* @param	event	Javascript event object
*/
vB_DatePicker.prototype.date_mouseover = function(e)
{
	var td = YAHOO.util.Event.getTarget(e);

	if (e.type == "mouseover")
	{
		YAHOO.util.Dom.replaceClass(td, "page", "alt2");
	}
	else
	{
		YAHOO.util.Dom.replaceClass(td, "alt2", "page");
	}
}

/**
* Sets the selected date in the picker to be today/now
*
* @param	event	Javascript event object
*/
vB_DatePicker.prototype.set_today = function(e)
{
	this.select_date(this.today);
}

/**
* Determines the absolute position of an element
*
* @param	object	HTML element to query
*
* @return	array	Array containing 'left' and 'top' integers
*/
vB_DatePicker.prototype.fetch_offset = function(obj)
{
	var left_offset = obj.offsetLeft;
	var top_offset = obj.offsetTop;
	var divcount = 0;

	while ((obj = obj.offsetParent) != null)
	{
		left_offset += obj.offsetLeft;
		top_offset += obj.offsetTop;
	}

	return { "left" : left_offset, "top" : top_offset };
};

/**
* Detect an overlap of an object and the popup
*
* @param	object	Object to be tested for overlap
* @param	array	Array of dimensions for menu object
*
* @return	boolean	True if overlap
*/
vB_DatePicker.prototype.overlaps = function(obj, m)
{
	var s = new Array();
	var pos = this.fetch_offset(obj);
	s['L'] = pos['left'];
	s['T'] = pos['top'];
	s['R'] = s['L'] + obj.offsetWidth;
	s['B'] = s['T'] + obj.offsetHeight;


	if (s['L'] > m['R'] || s['R'] < m['L'] || s['T'] > m['B'] || s['B'] < m['T'])
	{
		return false;
	}
	return true;
};

/**
* Handle IE overlapping <select> elements
*
* @param	boolean	Hide (true) or show (false) overlapping <select> elements
*/
vB_DatePicker.prototype.handle_overlaps = function(dohide)
{
	if (is_ie && !is_ie7)
	{
		var selects = fetch_tags(document, 'select');

		if (dohide)
		{
			var pos = this.fetch_offset(this.popup);
			var menuarea = new Array(); menuarea = {
				'L' : pos['left'],
				'R' : pos['left'] + this.popup.offsetWidth,
				'T' : pos['top'],
				'B' : pos['top'] + this.popup.offsetHeight
			};

			for (var i = 0; i < selects.length; i++)
			{
				if (this.overlaps(selects[i], menuarea) && this.month_element && selects[i].id != this.month_element.id)
				{
					selects[i].style.visibility = 'hidden';
					array_push(this.hidden_selects, i);
				}
			}
		}
		else
		{
			while (true)
			{
				var i = array_pop(this.hidden_selects);
				if (typeof i == 'undefined' || i == null)
				{
					break;
				}
				else
				{
					selects[i].style.visibility = 'visible';
				}
			}
		}
	}
};

/*======================================================================*\
|| ####################################################################
|| # Downloaded: 18:52, Sat Jul 14th 2007
|| # CVS: $RCSfile$ - $Revision: 15951 $
|| ####################################################################
\*======================================================================*/