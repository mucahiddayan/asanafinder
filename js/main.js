/**
 * @author Mücahid Dayan	
 */
 var require = function(){
 	var google = document.createElement('script');
 	google.src = 'https://www.google.com/jsapi';
 	document.getElementsByTagName('head')[0].appendChild(google);
 }

 var MessageBox = (function(){
 	var that__ = this;
 	var instance__;	
 	var branches__ = 0;
 	var defaults__ = {
 		wait : 5000,
 		msg : '',
 		pos: {
 			top : 10,
 			right: 20,
 		},
 		opacity: .5
 	};

 	var boxes__ = {		
 		error:0,
 		warn:0,
 		success:0,
 		info:0,
 	};

 	var settings__,top__;

 	var construct = function(settings){
 		if (typeof jQuery == 'undefined' && typeof $ == 'undefined') {
 			console.warn('jquery undefined! Please add jquery lib to your header.');
 			return;
 		}
 		if (typeof $ == 'undefined' && typeof jQuery == 'function') {
 			$ = jQuery;
 		}
 		settings__ = $.extend({},defaults__,settings);		

 		if (!instance__) {
 			instance__ = that__;
			// var pos = $.extend({},defaults__.pos.split(' '),settings__.pos.split(' ')),
			if (typeof settings__.pos != 'object') {
				var pos = settings__.pos.split(' '),
				hor = pos[1],
				ver = pos[0],			
				style = {};
				style[hor] = 10;
				style[ver] = 10;
			}else{
				style = settings__.pos;
				var keys = Object.keys(settings__.pos);
				top__ = keys[0] == 'top' || (keys.indexOf('top') == -1 && keys.indexOf('bottom') == -1) ? true:false;
				console.log(keys[0],top__);
			}
			$.extend(style,{'position':'fixed','pointer-events':'none','z-index':99999999});
			$('<div id="message-boxes-wrapper"></div>').prependTo('body').css(style);
			
			// this.info('MessageBox instance created!');
		}else{
			branches__++;	
		}
		return instance__;
	};

	

	var box = function(cl,msg,pos,id,color){
		return '<div id="'+id+'" class="message-box '+cl+'" style="background-color:'+color+';width:100%;padding:5px;margin:3px;">'+msg+'</div>';
	}

	this.getBranches = function(){
		return branches__;
	};

	this.getBoxes = function(total){
		if(total){
			var tot = 0;
			for (var i = boxes__.length - 1; i >= 0; i--) {
				tot += boxes__[i];
			}
			return tot;
		}else{
			return boxes__;	
		}		
	}

	that__.error = function(msg){
	if(msg == '' || typeof msg == 'undefined'){return;}		
		var id = boxes__.error++,timestamp = ~~(Date.now()/100),bx = box('error '+timestamp,msg,settings__.pos,id,'rgba(255,0,0,'+settings__.opacity+')');
		if (top__) {
			$('#message-boxes-wrapper').prepend(bx);
		}else{
			$('#message-boxes-wrapper').append(bx);
		}
		setTimeout(function() {
			$('.error#'+id).remove();
			$('.'+timestamp).remove();
			boxes__.error--;
		}, settings__.wait);
		return that__;
	};

	that__.warn = function(msg){
		if(msg == '' || typeof msg == 'undefined'){return;}
		var id = boxes__.warn++,timestamp = ~~(Date.now()/100),bx = box('warn '+timestamp,msg,settings__.pos,id,'rgba(255,140,0,'+settings__.opacity+')');
		if (top__) {
			$('#message-boxes-wrapper').prepend(bx);
		}else{
			$('#message-boxes-wrapper').append(bx);
		}
		setTimeout(function() {
			$('.warn#'+id).remove();
			$('.'+timestamp).remove();
			boxes__.warn--;
		}, settings__.wait);
		return that__;
	}

	that__.success = function(msg){
		if(msg == '' || typeof msg == 'undefined'){return;}
		var id = boxes__.success++,timestamp = ~~(Date.now()/100),bx = box('success '+timestamp,msg,settings__.pos,id,'rgba(65,230,85,'+settings__.opacity+')');
		if (top__) {
			$('#message-boxes-wrapper').prepend(bx);
		}else{
			$('#message-boxes-wrapper').append(bx);
		}
		setTimeout(function() {
			$('.success#'+id).remove();
			$('.'+timestamp).remove();
			boxes__.success--;
		}, settings__.wait);
		return that__;
	};

	that__.info = function(msg){
		if(msg == '' || typeof msg == 'undefined'){return;}
		var id = boxes__.info++,timestamp = ~~(Date.now()/100),bx = box('info '+timestamp,msg,settings__.pos,id,'rgba(30,95,240,'+settings__.opacity+')');
		if (top__) {
			$('#message-boxes-wrapper').prepend(bx);
		}else{
			$('#message-boxes-wrapper').append(bx);
		}
		setTimeout(function() {
			$('.info#'+id).remove();
			$('.'+timestamp).remove();
			boxes__.info--;
		}, settings__.wait);
		return that__;
	}
	that__.custom = function(msg,color){
		var color = $.extend(true,{},[30,95,40,settings__.opacity],color);		
		var id = boxes__.info++,timestamp = ~~(Date.now()/100),bx = box('info '+timestamp,msg,settings__.pos,id,'rgba('+color[0]+','+color[1]+','+color[2]+','+color[3]+')');
		if (top__) {
			$('#message-boxes-wrapper').prepend(bx);
		}else{
			$('#message-boxes-wrapper').append(bx);
		}
		setTimeout(function() {
			$('.info#'+id).remove();
			$('.'+timestamp).remove();
			boxes__.info--;
		}, settings__.wait);
		return that__;
	}

	//returns
	return {
		getInstance : function(settings){			
			return construct(settings);
		},
	}
})();

var isEmpty = function($var) {
	var result;
	result = (Array.isArray($var)) ? ($var.length < 1 ? true : false)
	: ($var == 'undefined' || typeof $var == 'undefined'
		|| $var == null || $var == '') ? true : false;
	return result;
}
var urls=[];
function goToAsanaApp(type,val){
	console.group('goToAsanaApp');
	var loc = '';	
	urls.push({type:type,val:val});		
	console.log(jQuery.unique(urls));
	if(!event.ctrlKey){
		if(urls.length>1){
			for(let i =0;i<urls.length;i++){
				if (i==0) {
					loc += '?'+urls[i].type+'='+urls[i].val;
				}else{
					loc+= '&'+urls[i].type+'='+urls[i].val;
				}
			}
			console.log(loc);
			window.location = '../../asana-finder/app/'+loc;

			urls=[];
			oc = '';	
		}else{
			loc += '?showAll&'+type+'='+val;
			console.log(loc);
			window.location = '../../app/'+loc;
			loc = '';
		}
	}
	console.groupEnd();			
}

if (typeof $ != 'undefined' || typeof jQuery != 'undefined') {
	$ = jQuery;
	(function ($) {
		$.fn.focusTextToEnd = function () {
			this.focus();
			var $thisVal = this.val();
			this.val('').val($thisVal);
			return this;
		}
	}(jQuery));
	var __mb = new MessageBox.getInstance({pos:'bottom left 10px 20px'});
	$(document).ready(function(){		
		$('.lists li a').hover(function(e){
			if (e.ctrlKey) {
				$(this).css('cursor','alias');
			}else{
				$(this).css('cursor','pointer');
			}
		});

		

		if(window.history.state == null){
			window.history.replaceState('called','local', window.location.href);
			console.log(history.state);
		}


		//UPDATE LABEL BUDDYPRESS ACCOUNT
		function updateLabel($params){
			$params.elementToUpdate.addClass('progressing');
			jQuery.ajax({
				url: pluginSettings.homeURL+'/wp-json/yj/v2/user-asanas-update',
				type: 'post',
				headers:{'X-WP-Nonce':pluginSettings.nonce},
				data: {
					userID:$params.userID,
					key : $params.key,
					type:$params.type,
					label:$params.label,
					func:'update_label'
				},
			})
			.done(function(data) {
				console.log('success');
				console.log(data);
				$params.elementToUpdate.removeClass('progressing');
				__mb.success('Label wurde umbenannt');
			})
			.fail(function() {
				console.log("error");				
				$params.elementToUpdate.removeClass('progressing');
				$params.elementToUpdate.addClass('failt');
			})
			.always(function() {
				console.log("complete");
			});
		}

		//UPDATE LABEL BUDDYPRESS ACCOUNT
		function deleteSequence($params){
			$params.elementToRemove.addClass('deleting');
			jQuery.ajax({
				url: pluginSettings.homeURL+'/wp-json/yj/v2/user-asanas',
				type: 'delete',	
				headers:{'X-WP-Nonce':pluginSettings.nonce},
				data: {
					userID:$params.userID,
					key : $params.key,
					type:$params.type,
				},
			})
			.done(function(data) {
				console.log('success');
				console.log(data);
				__mb.success('Sequenz wurde gelöscht');
				$params.elementToRemove.remove();

			})
			.fail(function() {
				console.log("error");
			})
			.always(function() {
				console.log("complete");
			});
		}

		var splitToDate = function(str){
			var ret = '';

			var helper = function(strd){
				var d = new Date(parseInt(strd));
				var newLabel;
				newLabel = (d.getDate()>9) ?  d.getDate(): '0' + d.getDate();
				newLabel += '.';
				newLabel += (d.getMonth()+1>9) ? d.getMonth()+1:'0'+(d.getMonth()+1);
				newLabel += '.';
				newLabel += d.getFullYear();
				newLabel += ' ';						
				newLabel += (d.getHours()>9) ? d.getHours():'0'+d.getHours();
				newLabel += ':';
				newLabel += (d.getMinutes()>9) ? d.getMinutes():'0'+d.getMinutes();
				newLabel += ':';
				newLabel += (d.getSeconds()>9) ? d.getSeconds():'0'+d.getSeconds();
				return newLabel;
			}

			var checkToDate = function(tt){
				
				if(!isNaN(tt) && parseInt(tt) >= new Date(16,3,15,10,30,0).getTime()){
					if (new Date(parseInt(tt)).getTime() > 0) {
						ret += helper(tt);
						ret += ' ';
					}else{
						console.log(tt+new Date(parseInt(str)));
					}							
				}
				else{
					var sp = typeof tt == 'string'?tt.split(' '):'';
					if (Array.isArray(sp) && sp.length > 1) {
						for(spl in sp){
							checkToDate(sp[spl]);
						}
					}else{
						ret += sp+' ';	
					}							
				}
			}
			checkToDate(str);
			console.log('return '+ret);
			return ret;
		}

		$('.edit').click(function(event) {
			var tr = $(this).parent().parent(),
			userID = tr.data('user'),
			key = tr.data('key'),
			type = tr.data('type'),				
			editable = tr.find('.editable'),
			oldVal = editable.text();
			if (tr.find('.edit-input').length) {
				tr.find('.edit-input').remove();
				editable.show();
			}else{
				editable.hide();
				$('<input type="text" placeholder="Name für die Sequenz eingeben und Enter drücken" title="Name für die Sequenz eingeben und Enter drücken" value="'+oldVal+'" class="edit-input">').insertAfter(editable).focusTextToEnd();	
			}

			tr.find('.edit-input').keypress(function(event) {
				if (event.which == 13) {
					if ($(this).val() != oldVal) {
						var newLabel = splitToDate($(this).val());
						console.log(newLabel,'key:'+key,'type:'+type);
						updateLabel({
							userID : userID,
							key : key,
							type: type,
							label: newLabel,
							elementToUpdate:tr
						});
						editable.text(newLabel);
						console.debug('changed');
					}else{
						console.debug('not changed');
					}					
					
					$(this).remove();
					editable.show();
				}				
			});			
		});

		$('.delete').click(function(event) {
			var tr = $(this).parent().parent(),
			userID = tr.data('user'),
			key = tr.data('key'),
			type = tr.data('type');

			var conf = confirm('Willst du wirklich die Sequenz löschen');
			if (conf) {
				deleteSequence({
					userID : userID,
					key : key,
					type: type,
					elementToRemove:tr
				});				
			}
		});

		$('.share').click(function(event) {
			var tr = $(this).parent().parent(),				
			shareLink = encodeURIComponent(tr.data('share')),
			socials = {
				facebook : {
					icon: '<i class="fa fa-facebook" aria-hidden="true"></i>',
					link: 'https://www.facebook.com/sharer/sharer.php?u='
				},
				google_plus:{
					icon: '<i class="fa fa-google-plus" aria-hidden="true"></i>',
					link: 'https://plus.google.com/share?url='
				},
				whatsapp:{
					icon: '<i class="fa fa-whatsapp" aria-hidden="true"></i>',
					link: 'whatsapp://send?text='
				},
				as_link:{
					icon: '<i class="fa fa-link" aria-hidden="true"></i>',
					link: ''
				},
				as_email:{
					icon: '<i class="fa fa-envelope-o" aria-hidden="true"></i>',
					link: 'mailto:&subject=Asanafinder - Share Sequence &body='
				}
			};

			var socbox = '<div id="social-share-box"><ul>';
			for(social in socials){
				if ( socials[social].link == '') {
					socbox += '<li><a target="_blank" onclick="prompt(\'Kopiere den Link\',\''+socials[social].link+location.origin+'/asana-finder/app/?'+shareLink+'\')">'+socials[social].icon+'</a></li>';
				}else if(social == 'as_email'){
					socbox += '<li><a target="_blank" href="'+socials[social].link+'Bitte ersetzen Sie den Zeichen ´#´ durch Fragezeichen '+location.origin+'/asana-finder/app/#'+shareLink+'">'+socials[social].icon+'</a></li>';
				}
				else{
					socbox += '<li><a target="_blank" href="'+socials[social].link+location.origin+'/asana-finder/app/?'+shareLink+'">'+socials[social].icon+'</a></li>';
				}

			}
			socbox += '</ul></div>';
			if ($('#social-share-box').length) {
				$('#social-share-box').remove();
			}else{
				$(socbox).appendTo('body').css({
					'position':'absolute',
					'top':event.pageY,
					'left':event.pageX,					
				});
			}
		});

		$(document).mouseup(function(e) {
			var container = $('#social-share-box');
			if (!container.is(e.target) && container.has(e.target).length === 0){
				container.remove();
			}
		});
	});
}else{
	console.warn('No jQuery Library');
}