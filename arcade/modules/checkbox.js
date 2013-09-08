var ie  = document.all  ? 1 : 0;
var del_amount = 0;
var a = 0;
var how_many_lang = "";

function hl(cb)
{
	if (ie)
	{
		while (cb.tagName != "TR")
		{
			cb = cb.parentElement;
		}
	}
	else
	{
		while (cb.tagName != "TR")
		{
			cb = cb.parentNode;
		}
	}

	cb.className = 'darkrow1';
	del_amount++;
	document.score_result_form.how_many.value = how_many_lang + " (" + del_amount + ")";
}

function dl(cb)
{
	if (ie)
	{
		while (cb.tagName != "TR")
		{
			cb = cb.parentElement;
		}
	}
	else
	{
		while (cb.tagName != "TR")
		{
			cb = cb.parentNode;
		}
	}

	cb.className = 'dlight';

	if( del_amount > 0)
	{
		del_amount--;
	}

	if( del_amount == 0 )
	{
		document.score_result_form.how_many.value = how_many_lang;
	}
	else
	{
		document.score_result_form.how_many.value = how_many_lang + " (" + del_amount + ")";
	}
}

function cca(cb)
{
	if( a == 0)
	{
		how_many_lang = document.score_result_form.how_many.value;
		a++;
	}
	if (cb.checked)
	{
		hl(cb);
	}
	else
	{
		dl(cb);
	}
}

function unselect_all()
{
	if( a == 0)
	{
		how_many_lang = document.score_result_form.how_many.value;
		a++;
	}

	var fmobj = document.score_result_form;
	for (var i=0;i<fmobj.elements.length;i++)
	{
		var e = fmobj.elements[i];
		if (e.type=='checkbox')
		{
			e.checked=false;
			dl(e);
		}
	}
}

function CheckAll()
{
	if( a == 0)
	{
		how_many_lang = document.score_result_form.how_many.value;
		a++;
	}

	var fmobj = document.score_result_form;
	del_amount = 0;
	for (var i=0;i<fmobj.elements.length;i++)
	{
		var e = fmobj.elements[i];
		if (e.type=='checkbox')
		{
			e.checked=true;
			hl(e);
		}
	}
}
