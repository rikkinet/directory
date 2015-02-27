$(document).ready(function() {
 $('#new_dir').click(function(){
	window.location.href = 'config.php?type=<?php echo $type ?>&display=directory&action=add';
	})
});

$(document).ready(function(){
	//show/hide add button/dropdown
	$('#addbut').click(function(){
		$('#addusersel').val('none');//reset select box
		$(this).fadeOut(250,
		function(){
			$('#addrow').fadeIn(250);
		});
		return false;
	});

	//add row button
	$('#addrow').change(function(){
		$(this).fadeOut(250,
		function(){
			$('#addbut').not("span").fadeIn(250).find("span").hide();
		});
		if($('#addusersel').val() != 'none'){
			var rownum=$('[class^=entrie]').length+1;
			//increment id untill we find one that isnt being used
			while($('.entrie' + rownum).length == 1){
				rownum++;
			}
			addrow($('#addusersel').val() + '|' + rownum);
		}
		return false;
	});

	//set toggle value for text-box hint text
	$("form").submit(function() {
    $(".dpt-title").each(function() {
      if($(this).val() == $(this).data("defText")) {
        $(this).val("");
      }
    });
	});

	//delete row when trash can is clicked
	$('.trash-tr').live('click', function(){
	$(this).parents('tr').fadeOut(500,
		function(){
			$(this).remove()
		})
	});


});


//add a new entry to the table
function addrow(user){
	$.ajax({
		type: 'POST',
	  url: location.href,
	  data: 'ajaxgettr='+encodeURIComponent(user)+'&quietmode=1',
	  success: function(data) {
	    $('#dir_entries_tbl > tbody:last').append(data);
	  },
	  error: function(XMLHttpRequest, textStatus, errorThrown) {
      var msg = "An Error occurred trying to contact the server adding a row, no reply.";
      alert(msg);
    }
  });
}

$(document).ready(function(){
	$("#dirgrid").bootstrapTable({
		method: 'get',
		url: '?display=directory&action=getJSON&jdata=grid&quietmode=1',
		cache: false,
		striped: true,
		showColumns: false,
		columns: [
			{
				title: _("Direcrory"),
				field: 'name',
			},
			{
				title: _("Actions"),
				field: 'link',
				formatter: linkFormatter,

			},
			{
				title: _("Default Direcrory"),
				field: 'default',
				formatter: defaultFormatter,
			}
			]
	});
});

function linkFormatter(value){
	var html = '<a href="?display=directory&view=form&id='+value['id']+'"><i class="fa fa-pencil"></i></a>';
	html += '&nbsp;<a href="?display=directory&action=delete&id='+value['id']+'" class="delAction"><i class="fa fa-trash"></i></a>';
	return html;
}
function defaultFormatter(value){
	var sy = '';
	var sn = '';
	if(value['default']){
		sy = ' CHECKED';
	}
	var html;
		html = '<span class="radioset">';
		html += '<input type="radio" name="default" id="default'+value['id']+'" value="V'+value['id']+'" '+sy+' onChange="dirUpdateDefault(this)">';
		html += '<label for="default'+value['id']+'">'+_("Select")+'</label>';
		html += '</span>';
	return html;
}
function dirUpdateDefault(item){
	var id = $(item).val().replace('V','');
	$.get("?display=directory&Submit=Submit&def_dir="+id);
}