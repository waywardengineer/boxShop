		$(document).ready(function(){
			jQuery("#eventsedit").validate({
				rules: {
					title: "required",
					category: "required",
					location: "required",
					description: "required",
					start: {
						required: true
					},
					length: {
						required: true,
						number: true
					},
					newcategory: {
						required: function(element) {
							return jQuery("#category").val()=='new';
						}
					},
					newlocation: {
						required: function(element) {
							return jQuery("#location").val()=='new';
						}
					},
					day: {
						required: function(element) {
							return (jQuery("#formsubmitted").val()=='edit' || jQuery("#formsubmitted").val()=='single') ;
						},
						date: true
					},
					repeatcount: {
						required: function(element) {
							return jQuery("#formsubmitted").val()=='multi' ;
						},
						number: true
					},
					}
  			});
 		});

		function getParams(){
			var selects = new Array('locationID', 'categoryID');
			var params = '';
			var obj;
			var i;
			for (i=0; i<selects.length; i++) {
				obj = document.getElementById(selects[i]);
				if (params){
					params += '&';
				}
				params += selects[i] + '=' + obj.value;
			}
			return params;
		}
//{}{}{}{/fuck you dw!!!!

		function updateSelects(){
			updateCalendar();
			var obj, idstr, url, i, menuItems;
			url = siteurl + '/cal_getSelects.php?' + getParams();
			menuItems=getElementsByClass('calMenu',document,'a');
			for (i=0; i<menuItems.length; i++) {
				idstr='#' + menuItems[i].id;
				jQuery(idstr).css({'display':'none'});
			}
			jQuery.get(url, items = function(data){
				var items = data.split('|');
				for (i=0; i<items.length; i++) {
					idstr='#' + items[i];
					jQuery(idstr).css({'display':'inline'});
				}
			});
			menuItems=getElementsByClass('calMenuSelected',document,'a');
			for (i=0; i<menuItems.length; i++) {
				idstr='#' + menuItems[i].id;
				jQuery(idstr).removeClass('calMenuSelected');
			}
			obj=document.getElementById('locationID');
			idstr = '#location' + obj.value;
			jQuery(idstr).addClass('calMenuSelected');
			obj=document.getElementById('categoryID');
			idstr = '#category' + obj.value;
			jQuery(idstr).addClass('calMenuSelected');

		}
		var stopit=false;
		function updateCalendar(){
			var str= siteurl + '/cal_calendar.php?' + getParams();
			if (!stopit) {jQuery('#calendardiv').load(str);}
			stopit=true;
			setTimeout ( 'stopit=false;', 500 );
			
		}
		
		function expandCalEvent(id, left, width, charcount){
			var sizefactor=2 + charcount / 120;
			var newWidth=parseInt(50*sizefactor);
			newWidth = newWidth>350?350:newWidth
			var newHeight=parseInt(25*sizefactor);
			newHeight = newHeight>400?400:newHeight
			var maxLeft = (7*width)-newWidth;
			var idstr = '#' + id;
			var newLeft = left - parseInt((newWidth-width)/2);
			newLeft = newLeft<0?0:newLeft;
			newLeft = newLeft>maxLeft?maxLeft:newLeft;
			newLeftStr = newLeft + 'px';
			newWidthStr = newWidth + 'px';
			newHeightStr = newHeight + 'px';
			jQuery(idstr).css({
						 'left':newLeftStr,
						 'width':newWidthStr,
						 'height':newHeightStr,
						 'z-index':2
						 });
			jQuery(idstr).addClass('calendarEventMouseover');
		}
		function restoreCalEvent(id, left, width, height){
			idstr = '#' + id;
			newLeftStr = left + 'px';
			newWidthStr = width + 'px';
			newHeightStr = height + 'px';
			jQuery(idstr).css({
						 'left':newLeftStr,
						 'width':newWidthStr,
						 'height':newHeightStr,
						 'z-index':1
						 });
			jQuery(idstr).removeClass('calendarEventMouseover');
		}
		function changeCategory(id){
			obj=document.getElementById('categoryID');
			obj.value = id;
			updateSelects();
		}
		function changeLocation(id){
			obj=document.getElementById('locationID');
			obj.value = id;
			updateSelects();
		}
		function getElementsByClass(searchClass,node,tag) {
			var classElements = new Array();
			if ( node == null )
				node = document;
			if ( tag == null )
				tag = '*';
			var els = node.getElementsByTagName(tag);
			var elsLen = els.length;
			var pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)");
			for (i = 0, j = 0; i < elsLen; i++) {
				if ( pattern.test(els[i].className) ) {
					classElements[j] = els[i];
					j++;
				}
			}
			return classElements;
		}
		function doNew(what){
			obj=document.getElementById(what);
			idstr= '#new' + what + 'div';
			if (obj.value=='new'){
				display='inline';
			}
			else{
				display='none';
			}
			jQuery(idstr).css({'display':display});
		}
		function deleteevent(){
			var theform=document.forms["eventsedit"];
			var answer = confirm  ("Delete this event?");
			if (answer){
				obj=document.getElementById("formsubmitted");
				obj.value='delete';
				theform.submit();
			}
		}


			
					
