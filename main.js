/**
 * @author Mücahid Dayan <mucahiddayan@hotmail.de>
 */

//globale variablen prefix: '__'
var __local= /wordpress/.test(location.pathname)?"/wordpress":'';
var __imgSrc = appSettings.pluginDirUrl+'angularjs/img/matte.png';
var __defaultImg = appSettings.pluginDirUrl+'angularjs/img/default.jpg';
var __ls = localStorage;
var __url = __local+'/wp-json/yj/v2/asanas';
var __sharedAsanasUrl = __local+'/wp-json/yj/v2/user-asanas/'
var __seperator = ';';
var __debug = true;
var __sudoUrl = appSettings.pluginDirUrl+'angularjs/sudo.php';
var __mb = MessageBox.getInstance({wait:3000,pos:'bottom left',opacity:.8});

/*__ng variable to */
var __ng = {};

//IS EMPTY
function isEmpty($var) {
	var result;
	result = (Array.isArray($var)) ? ($var.length < 1 ? true : false)
	: ($var == 'undefined' || typeof $var == 'undefined'
		|| $var == null || $var == '') ? true : false;
	if(typeof $var === 'object'){
		for(var prop in $var) {
			if($var.hasOwnProperty(prop))
				result = false;
		}
		
		result = JSON.stringify($var) === JSON.stringify({}) || $var == null || $var.length < 1;
	}
	return result;
}

// INFOBOX
//asanafinder infobox
function infoBox(){
	var $ = jQuery,
	icons = {
		'arrows-alt': 'Sequenz-Box vergrößern',
		'print':'Sequenz als PDF speichern',
		'trash':'Sequenz löschen',
		'floppy-o':'Sequenz speichern',
		'undo':'Gespeicherte Sequenz wiederherstellen',
		// 'arrow-down':'Ausgehählte Asanas exportieren als Zeichenkette',
		// 'arrow-up':'Sequenz importieren',
		'bars':'Asanas als List anzeigen',
		'share-alt':'Sequenz teilen',
		'info':'Infobox öffnen/schliessen',
		'eye': 'Sequenz-Box öffnen/schliessen',
		'__Doppelklick auf Filterleiste':'Fixierung an/aus'

	},
	box = '<div id="infobox-wrapper" style="position:fixed;top:0;left:0;width:100%;min-height:2000px;background-color:rgba(255,255,255,.8);">'; //infobox-wrapper open
	box += '<span class="infobox-close" onclick="$(\'#infobox-wrapper\').hide();"><i class="fa fa-times" aria-hidden="true"></i></span>'; // infobox open
	box += '<div id="infobox">'; // infobox open
	box += '<div id="text">Mit dieser Funktion des Asanafinders können Sie per Drag-and-Drop-Funktion Ihre eigenen Übungsreihen zusammenstellen – mit so vielen Asanas, wie Sie möchten – und sich das Ergebnis zum Üben oder Unterrichten ausdrucken. Klicken Sie einfach auf die Asanas, die Sie integrieren möchten und ziehen Sie diese in das leere Fenster auf der linken Seite. Dort können Sie sie dann selbst in die gewünschte Reihenfolge bringen. Nutzen Sie die Filterfunktion, um Ihr Level zu wählen - Basic, Medium und Advanced. Ist Ihnen heute nach armgestützen Haltungen oder möchten Sie sich mehr auf Ihre Balance konzentrieren? Dann grenzen Sie ihre Suche über die Filterfunktion nach Typen ein. Zu allen Haltungen finden Sie Vorschläge für vorbereitende und anschließende Asanas - nutzen Sie sie als Inspiration und Hilfestellung für Ihre Sequenz. Viel Spaß beim Üben!</div>'; // infobox open
	box += '<table>'; // infobox open
	box += '<tbody>'; // infobox open
	box += '<tr><th>Symbol</th><th>Beschreibung</th></tr>'; // infobox open
	for(icon in icons){
		box += '<tr><td class="info-icons">';
		box += (icon.indexOf('__')==-1)?'<i class="fa fa-'+icon+'"></i></td><td class="info-desc">'+icons[icon]:'<span>'+icon.replace('__','')+'</span></td><td class="info-desc">'+icons[icon];
		box += '</td></tr>';
    }
	box += '</div>'; // infobox close
	box += '</div>'; //infobox-wrapper close
	if (!($('#infobox-wrapper').length)) {
		$('body').append(box);
	}
	else{
		$('#infobox-wrapper').toggle();
	}
	
}

//CUSTOM ERROR LOG
var __cE = function(){
	var msg = '%c%s',color='red';
	if (!window.console || !console.log) {
		return;
	}
	for (var i = 0; i < arguments.length; i++) {
		msg +=arguments[i]+' ';
	}
	return window.console.log.bind( window.console, msg, 'color:'+color);
}();

//CUSTOM WARNING LOG
var __cW = function(){
	var msg = '%c%s',color='orange';
	if (!window.console || !console.log) {
		return;
	}
	for (var i = 0; i < arguments.length; i++) {
		msg +=arguments[i]+' ';
	}
	return window.console.log.bind( window.console, msg,'color:'+color );
}();

//CUSTOM LOG
var __cL = function(){
	if (!window.console || !console.log) {
		return;
	}
	var msg = '%c%s',color = 'green';
	for (var i = 0; i < arguments.length; i++) {
		if(typeof arguments[i] == 'object'){
			console.table(arguments[i]);
			return;
		}
			msg +=arguments[i]+' ';
	}	
	// return console.log(msg,'color:'+color);	
	return window.console.log.bind( window.console, msg,'color:'+color );
}();

//OBJECT TO STRING
function objectToString(obj,seperator){
	var seperator = isEmpty(seperator)?__seperator:seperator,
	str = '',
	counter = 0;

	for(key in obj){
		counter++;
		str += key+' : ´'+obj[key]+'` ';
		if (counter != Object.keys(obj).length ) {
			str+= seperator+' '
		}
	}
	return str;
}

//COPT TO CB
function copyToCB(el,cpy){
	el.select();
	cpy = !isEmpty(cpy)?cpy:false;
	if (cpy) {
		var copysuccess;
		try{
			copysuccess = document.execCommand("copy")
		} catch(e){
			copysuccess = false
		}
		return copysuccess;
	}
}

//UNIQUE STRING ARRAY
function uniqueStringArray(array,strict){
	strict = isEmpty(strict)?false:strict;
	return array.filter(function(val,pos){
		if (strict) {return array.indexOf(val.toLowerCase()) == pos;}
		else{return array.indexOf(val) == pos;}
	});	
}

//CUSTOM SPLIT
function customSplit(tags,strict){
	strict = isEmpty(strict)?false:strict;
	if (typeof tags == 'string') {
		try{
			var tags = tags.indexOf(__seperator)>-1?tags.split(__seperator):tags;
			if(Array.isArray(tags)){
				tags = uniqueStringArray(tags,strict);	
			}
		}catch(e){
			console.warn('Error:',e);
		}
		return tags;
	}else{
		console.warn('Error: type of tags is not string');
		return;
	}	
}

//CAPITALIZE STRING
String.prototype.capitalizeString = function(){
	if (isEmpty(this)) {if(__debug){console.debug('String is empty')};return this;}
	var ret = this.charAt(0).toUpperCase()+this.slice(1).toLowerCase();
	return ret;
}


var __helper = {
	assign: function(obj, str, value) {
		if (str.indexOf('_') == -1) {obj[str] = value;}
		var keyPath = str.split('_');
		lastKeyIndex = keyPath.length-1;
		for (var i = 0; i < lastKeyIndex; ++ i) {
			key = keyPath[i];
			if (!(key in obj))
				obj[key] = {}
			obj = obj[key];
		}
		obj[keyPath[lastKeyIndex]] = value;
	},

	is : {
		android : /Android/i.test(navigator.userAgent),
		apple : /Android/i.test(navigator.userAgent),
		visible:function(selector){
			return jQuery(selector).is(':visible');
		}
	},

	recompose :function(obj, string) {
		var parts = (typeof string != 'function') ? string.split('_') : '';
		var newObj = obj[parts[0]];
		if (parts[1]) {
			parts.splice(0, 1);
			var newString = parts.join('_');
			return __helper.recompose(newObj, newString);
		}		
		return newObj;
	},

	toBoolean : function(val){
		return typeof val == 'boolean'?val:val === 'true';
	},

	//CLOSES BOX FROM GIVEN CLOSE BUTTON WHICH IS CHILD/ELDER ELEMENT FROM BOX
	closeBox : function(box){
		box.parentNode.parentNode.style.display = 'none';
	},

	// CLEAR STRING FROM DUPLICATES
	clearFromDuplicates : function(str,strict){
		strict = isEmpty(strict)?false:strict;
		try{
			if(Array.isArray(str)){			
				str = uniqueStringArray(str,strict);
				return str.join(__seperator);			
			}
			else{
				return (Array.isArray(customSplit(str)))?customSplit(str).join(__seperator):customSplit(str);
			}		
		}catch(e){
			console.warn('Error:',e);
		}
	},

	closeOtherBoxes: function(el){
		jQuery('.custom-boxes:not('+el+')').hide();
	},

	createBox: function($options,$scope,$compile){
		settings =  jQuery.extend({id :'id',title : '',extras : '',textarea:true,append:'.app-container'},$options);
		$html =  '<div id="'+settings.id+'" class="custom-boxes"><h6>'+settings.title+'</h6><span class="close-custom-box"><i class="fa fa-times" onclick="__helper.closeBox(this);"></i></span>';
		if(settings.textarea){
			$html+='<textarea onfocus="copyToCB(this);" class="inputfield"></textarea>';
		}
		$html+=settings.extras+'</div>';
		$html = $compile($html)($scope);
		jQuery(settings.append).append($html);
	},

	open : {
		shareBox : function($scope,$compile){
			__helper.closeOtherBoxes('#export-box');
			if(jQuery('#export-box').length){
				jQuery('#export-box').toggle();
			}else{
				__helper.createBox({textarea:false,
									extras:'<div id="social-links">'+
												'<div id="wa-share" ng-show="isMobile"><a href=""><i class="fa fa-whatsapp" aria-hidden="true"></i></a></div>'+
												'<div id="fb-share"><a href="" target="_blank"><i class="fa fa-facebook-square" aria-hidden="true"></i></a></div>'+
											   	'<div id="gp-share"><a href="" onclick="javascript:window.open(this.href,\'\', \'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=600,width=600\');return false;"><i class="fa fa-google-plus" aria-hidden="true"></i></a></div>'+
											   	'<div id="al-share"><a href="" onclick="prompt(\'Kopiere den Link und teile \',this.href)"><i class="fa fa-link" aria-hidden="true"></i></a></div>'+
											'</div>',
									id :'export-box',
									title:'Teile deine Sequenz mit Freunden'},$scope,$compile);
			}
			return 	jQuery('#export-box #social-links');
		},

		restoreFilterBox : function($scope,$compile){
			if(jQuery('#filter-restore-box').length){
				jQuery('#filter-restore-box').show();
			}else{
				__helper.createBox({id :'filter-restore-box',title:'Filter auswählen und anwenden',textarea:false,extras: '<div id="content"></div><button id="apply-filter" ng-click="applyFilter()">OK</button>'},$scope,$compile);
			}
			return jQuery('#filter-restore-box');
		}
	},

	checkForDataToPass : function($key){
		if (isEmpty($key)) {__cE('Key could not be empty');return;}
		var key_,userID_,$key = $key.toString();
		var dataToPass = {};
		if($key.indexOf('.')== -1){
			if (isNaN($key)) {
				__cE('userID must be a number!');return;
			}
			dataToPass.userID = $key;
		}else{
			var spl = $key.split('.');
			dataToPass.userID = spl[0];
			if (spl.length>1) {
				dataToPass.key = spl[1];				
			}
		}
		return dataToPass;
	},

	/**
	 * @elements : String <css_selector>
	 * @trigger  : input  <checkbox>
	 */
	 toggleElementsIf : function(elements,condition){	 	
	 	if (condition) {
	 		jQuery(elements).hide();
	 	}else{
	 		jQuery(elements).show();
	 	}
	 }
};




/*++++++++++++++++++++++++++++++++++++++AngularJS+++++++++++++++++++++++++++++++++*/
var app = angular.module('asanaFinder',['ui.bootstrap','dndLists',/*'pr.longpress'*/]);
app.controller('mainCtrl', ['$scope', '$http','$location','$filter','$compile','$timeout', function ($scope, $http,$location,$filter,$compile,$timeout ) {
	
	//local variablen in AngularJs Scope
	$scope.paginated = [];	
	$scope.sortBy = 'title'
	$scope.sortReverse = false;
	$scope.searchFilter = '';
	$scope.currentPage = 1;
	$scope.numPerPage = 9;
	$scope.showAll = false;	
	$scope.isGast = !appSettings.currentUser.loggedIn;
	$scope.userID = appSettings.currentUser.ID;

	$scope.request = {};
	$scope.request.headers = {'X-WP-Nonce':appSettings.nonce};
	// $scope.isGast = true;	
	$scope.posts = [];
	$scope.listType = false;
	$scope.XOR = 'und';
	$scope.dragging = false;

	$scope.box = {};
	$scope.box.enlarge = false;
	$scope.box.closeSAB = false;
	$scope.box.notSticky = false;

	$scope.defaultImage = __defaultImg;	

	$scope.saveAfterImport = false;
	$scope.showVor = $scope.showAns = false;
	$scope.isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

	//zu überwachende variablen und die erwartende werte
	$scope.toWatch = {
						levelFilter:'',
						typeFilter:'',
						searchFilter:'',
						XOR:'und',
						showAll:false,
						showVor:false,
						showAns:false,
						sortBy:'title',
						sortReverse:'',
						listType:false,
						box_closeSAB : false,		
						box_notSticky : false,
					 };

	
	//Asana list wird initialisiert
	$scope.loadAsanas = function(){
		var result = false;		
		$scope.posts = appSettings.asanas;
		if (isEmpty(__ls.cached_asanas)){
			__ls.setItem('cached_asanas',angular.toJson(appSettings.asanas));
		}		
		$scope.pagination();
		return result;
	}

	//Asana List wird aktualisiert
	$scope.updateAsanas = function(force){
		var result = false;
		var forceToReload = isEmpty(force)?false:force;
		if (isEmpty(__ls.cached_asanas) || forceToReload) {
			if(__debug){__cL('Asanas werden geladen');}
			$http.get(__url,{
				headers : $scope.request.headers,
			})
			.then(function(res) {
				$scope.posts = res.data;
				__ls.setItem('cached_asanas',angular.toJson(res.data));
				if(__debug){__cL('Asanas werden gecached');  }
				$scope.pagination();
				if (!res.data.length) {
					__mb.warn('Versuche bitte später nochmal. Es ist noch nicht so weit!');
					setTimeout(function() {location.href= location.origin;}, 2000);
				}
				result = true;
			});
		}else{
			$scope.posts = JSON.parse(__ls.getItem('cached_asanas'));			
			if(__debug){__cL('Asanas werden vom cache geladen');}
			result = true;
		}		
		return result;
	}

	$scope.helper = {
		temp : [],
		update : function(dest){
			angular.copy(dest,$scope.helper.temp);
		},
		reset : function(){
			$scope.helper.temp = [];
		},
		isChanged : function(dest){
			return !angular.equals($scope.helper.temp,dest);
		}
	};
	
	//SHARE SEQUENCE
	$scope.shareSequence = function(){
		if (!$scope.isGast) {
			if (__helper.is.visible('#export-box')) {
				return;
			}
			if (__debug) {__cL('Share link is being created');}
			__mb.info('Link zum Teilen wird erstellt..');
			var eb;
			var share = function(link){			
				eb = __helper.open.shareBox($scope,$compile);
				jQuery(eb).find('#wa-share a').attr('href','whatsapp://send?text='+encodeURIComponent(link));
				jQuery(eb).find('#wa-share a').attr('data-action','share/whatsapp/share');			
				jQuery(eb).find('#fb-share a').attr('href',"https://www.facebook.com/sharer/sharer.php?u="+link);
				jQuery(eb).find('#gp-share a').attr('href',"https://plus.google.com/share?url="+link);
				jQuery(eb).find('#al-share a').attr('href',link);			
			}

			if (!$scope.helper.isChanged($scope.selectedAsanas)) {
				if(__debug){__cL('not changed');}
				share($scope.getShareLink());			
				if(__debug){__cL($scope.shareLink);}
				return;
			}
			__cW('changed');
			var key_ = Date.now().toString();
			console.log('Data key:'+key_);

			$http({
				url:__sharedAsanasUrl,
				method:'post',
				headers: $scope.request.headers,	
				data:{
					key: key_,
					value:$scope.selectedAsanas,
					userID:$scope.userID,
					type:'shared-asanas',
					exclude:'label'
				}
			}).then(function successCallback(response) {
				__cL('share link created successfully');
				__mb.success('Link zum Teilen wurde erfolgreich erstellt.');					
				$scope.setShareLink(window.location.href.split('?')[0]+'?getSharedSequence='+btoa(response.data.userID+'.'+response.data.key));
				share($scope.getShareLink());			
				$scope.helper.update($scope.selectedAsanas);

			}, function errorCallback(response) {
				console.log(response);			
				if (response.status == 403) {
					__cE('No access');
					__mb.warn('Um die Sequenz teilen zu können, müssen Sie sich anmelden!');
				}
				__cE("something went wrong");
			});
		}else{
			__mb.warn('Um die Sequenz teilen zu können, müssen Sie sich anmelden!');
		}	
	}

	//GET SAVED SEQUENCE
	$scope.getSavedSequence = function($key){
		$scope.selectedAsanas = $scope.getSavedAsanas($key);
		// $scope.$apply();
	}

	//GET SHARED ASANAS (BY KEY)
	$scope.getSharedSequence = function($key){		
		var helper = __helper.checkForDataToPass($key);
		helper.type = 'shared-asanas';
		helper.exclude = 'label';
		var sharedAsanas;
		try{
			jQuery.ajax(__sharedAsanasUrl,{				
				type:'get',
				data:helper,				
			}).then(function successCallback(response) {				
				if(__debug){console.log(response);}
				if (!isEmpty(response)) {					
					$scope.selectedAsanas = response;
					$scope.$apply();					
					try{
						
					}catch(e){
						__cE(e);
						return false;
					}
					__cL('shared asanas loaded successfully');
					
				}else{
					__cW('Es gibt keine Asanas für diesen User');
				}
				
			}, function errorCallback(response) {
				__cE("something went wrong");				
				console.debug(response);
			});
		}catch(e){
			__cE(e);
		}
	}

	$scope.deleteSequenceFromUser = function(key,type){
		var helper = __helper.checkForDataToPass(key);
		helper.type = type;
		// if (isEmpty(settings.userID)) {__cE('User ID darf nicht fehlen');return;}
		jQuery.ajax({
			url:__sharedAsanasUrl,
			method:'delete',
			headers: $scope.request.headers,
			data:helper,
		}).then(function successCallback(response) {			
			__cL(type+' deleted successfully');
			__mb.info(type+' wurden erfolgreich gelöscht!');
			console.log(response);
		}, function errorCallback(response) {
			__cE("something went wrong");
		});
	}

	$scope.sudo = function(pass){		
		return false;
	}


	

	// experimental
	/*$timeout(function(){
	__ng.fT = jQuery('#config .label-hidden-input').flowingText({type:'fade',waitBeforeInit:5000,scriptType:['angularjs',$compile,$scope]});},1000
	);*/
	// 
	//GLOBAL FUNCTIONS 
	__ng.refresh = function(force){
		$scope.loadAsanas(force);
	}

	$scope.isSudo = false;

	__ng.sudo = function(msg){
		var result = jQuery.ajax({
			url:__sudoUrl,
			type:'post',
			async: false,
			data : {
				password:msg
			}
		})
		.done(function(data){					
			if (parseInt(data) === 1) {
				$scope.isSudo = true;
				__cL('sudo');				
			}else{
				$scope.isSudo = false;
				__cE('not sudo');
			}
		})
		.fail(function(data){
			__cE("something went wrong");			
		}).always(function(){
			return $scope.isSudo?$scope:{};
		}).responseText;

		return parseInt(result) === 1?{scope:$scope,http:$http,location:$location}:{};
		
	}
	

	//GLOBAL FUNCTIONS END

	//PAGINATION
	$scope.pagination = function(){		
		if (isEmpty($scope.searchFilter)) {
			$scope.numPerPage = $scope.showAll?$scope.posts.length:9;
		}else{
			$scope.numPerPage = $scope.posts.length;
		}	
		$scope.$watch('currentPage + numPerPage', function() {
			var begin = (($scope.currentPage - 1) * $scope.numPerPage)
			, end = begin + $scope.numPerPage;

			$scope.paginated = $scope.posts.slice(begin, end);
			$scope.numPages();
		});    
	}


	//GET SEARCH QUERY FROM URL
	$scope.getSearchQueryFromUrl = function(){
		try{
			var query  = $location.search();
		}catch(e){
			console.warn('Error:',e);
			return false;
		}
		if (__debug) {console.debug(query);}
		return query;
	}

	

	//GET FILTER FROM URL
	$scope.getFilterFromUrl = function(){
		var filterQuery;	
		filterQuery = $scope.getSearchQueryFromUrl();
		if (!isEmpty(filterQuery)) {
			for(filter in $scope.toWatch){				
				if(filter == "searchFilter"){
					$scope[filter] = isEmpty(filterQuery[filter])?$scope.toWatch[filter]:__helper.clearFromDuplicates(filterQuery[filter]);
				}else{
					try{
						if (isEmpty(filterQuery[filter])) {
							__helper.assign($scope,filter,$scope.toWatch[filter]);
						}else if(Array.isArray(filterQuery[filter])){
							__helper.assign($scope,filter,filterQuery[filter][0]);
						}else{
							__helper.assign($scope,filter,filterQuery[filter]);
						}
						// $scope[filter] = isEmpty(filterQuery[filter])?$scope.toWatch[filter]:angular.isArray(filterQuery[filter])?filterQuery[filter][0]:filterQuery[filter];
					}
					catch(e){
						console.warn('Error:',e);
					}					
				}			
			}
		}
	}

	//SET FILTER TO URL
	$scope.setFilterToUrl = function(filters){
		 angular.forEach(filters,function(val,key){
		 	if(__debug){console.debug('key:'+key,val);}		 	
		 	// $location.search(key,isEmpty(recompose(val).expected)?(isEmpty(val.value)?null:val.value):(val.expected === val.value.toString())?null:val.value);
		 	$location.search(key,isEmpty(val.expected)?(isEmpty(val.value)?null:val.value):(val.expected === val.value.toString())?null:val.value);
		 	if(__debug){console.debug(key,val.expected,val.value,val.expected == val.value);console.debug(typeof val.expected, typeof val.value);}
		 });
	}

	$scope.saveSequence = function(settings){
		if (settings == 'local') {
			__ls.setItem(__ls.getItem('yogamaker').toLowerCase()+'.asanas',angular.toJson($scope.selectedAsanas));
		}
	}


	//BEFORE START
	$scope.beforeStart = function($filter){
		var yogamaker;		
		/*if(isEmpty(__ls.getItem('yogamaker'))){
			yogamaker = prompt('Würdest du uns deinen Name verraten?');
			if (isEmpty(yogamaker)) {
				__ls.setItem('yogamaker',"gast");
				yogamaker = 'gast';
			}else{
				__ls.setItem('yogamaker',yogamaker);			
			}
		}else{
			yogamaker = __ls.getItem('yogamaker');
		}	
		$scope.yogamaker = yogamaker;*/	
		__ls.setItem('yogamaker',appSettings.currentUser.displayName);
		$scope.yogamaker = appSettings.currentUser.displayName;	
		// $scope.isGast = yogamaker.indexOf('gast')>-1;
		$scope.savedAsanas = $scope.getSavedAsanas();
		angular.copy($scope.savedAsanas,$scope.backUpSavedAsanas);
		$scope.selectedAsanas = $scope.getSavedAsanas();
		$scope.firstTime = $scope.isFirstTime();
		$scope.shareLink = $scope.getShareLink();		
		$scope.saveSequence('local');
		$scope.getFilterFromUrl();		
		__cL("App initialized","showAll: "+$scope.showAll);
		__mb.success("App initialized");
		// $scope.searchAll();		
	}

	//AFTER START
	$scope.afterStart = function(){
		__cL('Started');
	}

	//CREATE IF EMPTY
	$scope.createIfEmpty = function($entry){
		all = isEmpty($entry);
		var elements= ['yogamaker', 'yogamaker.filter', 'yogamaker.filter'];
		if (all) {
			for(element in elements){
				if(isEmpty(__ls.getItem(element))){
					__ls.setItem(element,'');
				}
			}
		}else{
			if(isEmpty(__ls.getItem($entry.key))){
				try{
					__ls.setItem($entry.key,$entry.value);
				}catch(e){
					console.warn('Error:',e);
				}
			}
		}
	}

	//LOG OUT
	$scope.logout = function(){
		__ls.setItem('yogamaker','');
		location.search = '';
		$scope.beforeStart();
	}
	

	//NUM PAGES
	$scope.numPages = function () {
		return Math.ceil($scope.posts.length / $scope.numPerPage);
	};

	//SEARCH ALL
	$scope.searchAll = function(){
		if (!isEmpty($scope.searchFilter)) {			
			$scope.searchFilter = __helper.clearFromDuplicates($scope.searchFilter);
			$scope.paginated = $scope.posts;
		}else{			
			$scope.pagination();
		}
	}	

	//SEARCH THIS
	$scope.searchThis= function(event){
		if ($scope.searchFilter.indexOf(angular.element(event.target).text())== -1) {
			$scope.searchFilter = isEmpty($scope.searchFilter)?angular.element(event.target).text():$scope.searchFilter+__seperator+angular.element(event.target).text();
		}		
		$scope.searchAll();
	}	

	//SAVE ASANAS
	$scope.saveAsanas = function(){				
		var key_ = Date.now().toString();
		if (!$scope.isGast && $scope.selectedAsanas.length) {
			$http({
				url:__sharedAsanasUrl,
				method:'post',
				headers: $scope.request.headers,			
				data:{
					key: key_,
					value:$scope.selectedAsanas,
					userID:$scope.userID,
					type:'saved-asanas'
				}
			}).then(function successCallback(response) {
				console.debug(response);
				if(response.status == 200){
					__cL('asanas werden gespeichert');
					__mb.success("Ihre Sequenz wurde erfolgreich gespeichert");
				}
			}, function errorCallback(response) {
				console.log(response);
				__cE("something went wrong");
				if (response.status == 403) {
					__cE('No access');
					__mb.warn('Sie müssen sich einloggen damit Sie die Sequenz speichern können!');
				}
			});
			__ls.setItem(__ls.getItem('yogamaker').toLowerCase()+'.asanas',angular.toJson($scope.selectedAsanas));					
		}else{
			__mb.warn('Ihre Sequenz wird nur auf dem Browser gespeichert. Loggen Sie sich ein, damit sie in Ihrem Profil gespeichert wird');
			__ls.setItem(__ls.getItem('yogamaker').toLowerCase()+'.asanas',angular.toJson($scope.selectedAsanas));
		}
		if(__debug){console.debug('sequenz gespeichert');}
	}

	//SET SHARE LINK
	$scope.setShareLink = function(link){
		__ls.setItem(__ls.getItem('yogamaker').toLowerCase()+'.shareLink',link);	
	}

	//GET SHARE LINK
	$scope.getShareLink = function(){
		return !isEmpty(__ls.getItem($scope.yogamaker.toLowerCase()+'.shareLink'))?__ls.getItem(__ls.getItem('yogamaker').toLowerCase()+'.shareLink'):'';
	}

	//GET SAVED SEQUENCE
	$scope.getSavedSequences = function($settings){
		var defaults = {
			from : 'database',
			key : '',
			force:false,
		}

		var settings = jQuery.extend({},defaults,$settings,true);
		var key = isEmpty(settings.key)?false:settings.key;

		if (settings.force || key) {
			try{
				var helper = __helper.checkForDataToPass($key);
				helper.type = 'saved-asanas';
				helper.exclude = 'label';
				return jQuery.ajax(__sharedAsanasUrl,{				
					type:'get',
					data:helper,
					async:false,
				}).done(function(data){
					console.debug(data);
				}).fail(function(data){
					console.warn(data);
				}).responseJSON;
			}catch(e){
				__cE(e);
			}
		}

		if (settings.from == 'database') {
			return appSettings.savedSequences;
		}
		if(settings.from == 'localstorage'){
			return !isEmpty(__ls.getItem($scope.yogamaker.toLowerCase()+'.asanas'))?angular.fromJson(__ls.getItem(__ls.getItem('yogamaker').toLowerCase()+'.asanas')):[];
		}
	}

	//GET SAVED ASANAS
	$scope.getSavedAsanas = function($key){
		$key = isEmpty($key)?false:$key;
		if ($key) {
			try{
				var helper = __helper.checkForDataToPass($key);
				helper.type = 'saved-asanas';
				helper.exclude = 'label';
				return jQuery.ajax(__sharedAsanasUrl,{				
					type:'get',
					data:helper,
					async:false,
				}).done(function(data){
					console.debug(data);
				}).fail(function(data){
					console.warn(data);
				}).responseJSON;
			}catch(e){
				__cE(e);
			}
		}else{
			if (isEmpty(appSettings.savedSequences)) {
				if(__debug){console.log('Local Storage');}
				__mb.info('Sequenze wird aus dem Cache geladen');
				return !isEmpty(__ls.getItem($scope.yogamaker.toLowerCase()+'.asanas'))?angular.fromJson(__ls.getItem(__ls.getItem('yogamaker').toLowerCase()+'.asanas')):[];
			}else{
				if(__debug){console.log('Data Base');}
				__mb.info('Sequenze wird aus der Datenbank geladen');
				return appSettings.savedSequences;
			}
		}
	}

	//GET SAVED ASANAS AS STRING
	$scope.getSavedAsanasAsString = function(){
		return !isEmpty(__ls.getItem($scope.yogamaker.toLowerCase()+'.asanas'))?__ls.getItem(__ls.getItem('yogamaker').toLowerCase()+'.asanas'):'';
	}

	//GET SELECTED ASANAS AS STRING
	$scope.getSelectedAsanasAsString = function(){
		return !isEmpty($scope.selectedAsanas)?angular.toJson($scope.selectedAsanas):'';
	}

	//IS FIRST TIME
	$scope.isFirstTime = function(){
		var visit = isEmpty(__ls.getItem($scope.yogamaker+'.firstTime.visit'))?true:false;
		var trash = isEmpty(__ls.getItem($scope.yogamaker+'.firstTime.trash'))?true:false;
		return {visit:visit,trash:trash};
	}
	//TOGGLE XOR 
	$scope.toggleXOR = function(){
		$scope.XOR = $scope.XOR === 'und'?'oder':'und';
	}

	//NOT FIRST TIME ANYMORE
	$scope.notFirstTimeAnymore = function(event){
		event = isEmpty(event)?'visit':event;
		if(__debug){console.log('not first time anymore');}
		if($scope.isFirstTime()[event]){
			__ls.setItem($scope.yogamaker+'.firstTime.'+event,false);
		}
		$scope.firstTime = $scope.isFirstTime();
	}
	// ############################################## app wird gestartet ############################################## \\
	
	$scope.beforeStart($filter);	
	// $scope.afterStart();	
	
	if($scope.loadAsanas()){		
		$scope.getFilterFromUrl();
		$scope.pagination();
	}
	//alles was nach dem Start geaendert werden soll, soll nach dieser Zeile initialisiert/erzeugt werden
	$scope.firstTime = $scope.isFirstTime();	

	if(!isEmpty($location.search()['getSharedSequence'])){
		__cL($location.search()['getSharedSequence']);
		$scope.getSharedSequence(atob(decodeURIComponent($location.search()['getSharedSequence'])));
		$location.search('getSharedSequence',null);
	}

	if(!isEmpty($location.search()['getSavedSequence'])){
		__cL($location.search()['getSavedSequence']);
		$scope.getSavedSequence(atob(decodeURIComponent($location.search()['getSavedSequence'])));
		$location.search('getSavedSequence',null);
	}
	
	// WATCH GROUP
	$scope.$watchGroup(['levelFilter','typeFilter'], function (newVal, oldVal) {
		if(!newVal[0] && !newVal[1]){
			$scope.XOR = 'und';
		}		
	});

	//WATCH TO TOWATCH
	$scope.$watchGroup(Object.keys($scope.toWatch).map(function(obj){return obj.replace('_','.')}),function(newV,oldV){	
		var init = {};
		for(obj in $scope.toWatch){
			init[obj] = {expected:$scope.toWatch[obj],value:__helper.recompose($scope,obj)};
			// init[obj] = {expected:$scope.toWatch[obj],value:$scope[obj]};
		}
		$scope.setFilterToUrl(init);
		// __helper.toggleElementsIf('.td-footer-wrapper,.td-header-wrap',$scope.box.closeSAB);	
	});

	//GO TO
	$scope.goTo = function(url){
		window.location = url;
	}

	//RESTORE ASANAS
	$scope.restoreAsanas = function(){
		$scope.selectedAsanas = $scope.getSavedSequences({from:'localstorage'});
		if(__debug){console.debug('restored');}
		__mb.info('Sequenz wiederhergestellt');
	}

	//DELETE ALL
	$scope.deleteAll = function(){
		$scope.selectedAsanas = [];
		__mb.info('Sequenz gelöscht!');
	}	

	//ADDED
	$scope.added = function(index, item, external, type){		
		// __mb.info('"'+item.title+ '" wurde zur Sequenz hinzugefügt!');
	}

	//INSERTED
	$scope.inserted = function(index){
		if(__debug){console.log('Inserted to:'+index);}
	}

	//IMPORT ASANAS
	$scope.importAsanas = function($asanas){		
		var decoded,input = angular.element('#import-box textarea').val();
		try{
			decoded = angular.fromJson(input);
			if(__debug){console.debug('DECODED OBJECT: '+angular.fromJson(input));		}
		}catch(e){
			console.error('Das ist kein gültiger Format zum Importieren');
			angular.element('#import-box textarea').val('');
			angular.element('#import-box textarea').attr('placeholder','Das ist kein gültiger Format zum Importieren');
			return false;
		}
		if(decoded){
			if(__debug){console.log('DECODED OBJECT: '+decoded);}
			$scope.selectedAsanas = decoded;			
			if (__debug) {console.log($scope.saveAfterImport);}
			if($scope.saveAfterImport){
				$scope.saveAsanas();
			}
			setTimeout(function() {
				angular.element('#import-box').hide();
				angular.element('#import-box textarea').val('');
				$scope.saveAfterImport=false;
			}, 10);
		}
	}

	//PRINT ASANA LIST
	$scope.printSequenz = function(){
		__mb.info('Sequenz wird zum Ausdrucken vorbereitet...');
		var printStyle = '<style type="text/css">'+
							 // '@media print{'+
								'body{position:relative;}'+
								'#asana-container{width:100%;padding:10px;position:absolute;top:5px;}'+
								'h6 {margin: -30px 0 0 0;text-align: center;}#logo {position: absolute;top: 2px;right: 0px;width:40px;display:none;}#logo img {width: 100%;}'+
								'table{width:12cm;}'+
								'.thumb img{width:200px; height:200px;position:absolute;right:0px;top:0px;}'+								
								'.index {font-size: 20px;border: 1px solid;padding: 5px;width: 23px;height: 23px;text-align: center;float: left;margin-right: 5px;}'+								
								'.asana{margin:10px 0;border-bottom:2px solid #bcbcbc;list-style:none;height:9.16cm;width:19cm;position:relative;overflow:hidden;}'+
								'.not-in-drop-area div {font-style: italic;font-weight: bold;margin: 5px;}'+
								'.not-in-drop-area div span:not(:last-child)::after {content: ",";margin-right:2px;}'+
								'td:first-child  {font-size:14px;}td:last-child{font-weight:bold;font-style:italic;}td{border:1px dotted #ececec;}td span:not(:last-child)::after{content:",";margin-right:3px;}'+
							 // '}'+
						 '</style>';		
		var mywindow = window.open('', 'PRINT', 'height=400,width=600');
		mywindow.document.write('<html><head><title>' + document.title  + '</title>');
		mywindow.document.write(printStyle);
		mywindow.document.write('</head><body><div id="logo">'+document.getElementsByClassName('td-main-logo')[0].innerHTML+'</div>');
		mywindow.document.write('<h6>' + document.title  + '</h6>');
		mywindow.document.write('<div id="asana-container">'+document.getElementById('to-print').innerHTML+'</div>');
		mywindow.document.write('</body></html>');

		mywindow.document.close(); 
		mywindow.focus(); 
		
		setTimeout(function() {		
			mywindow.print();
			__mb.warn('Druckfenster geschlossen');	
			if(__debug){console.info('Printed');}			
		}, 1000);
		setTimeout(function() {mywindow.close();}, 3000);
	}	
//++++++++++++++++++++++++++++++ filter functions
	//RESET FILTER
	$scope.resetFilter = function(filter){
		var all = isEmpty(filter);
		if (!all) {
			$scope[filter] = '';
			if(!isEmpty($location.search(filter))){
				$location.search(filter,null);	
			}			
		}
		if (all) {
			for(filter in $scope.toWatch){
				/*if (filter == 'showAll') {
					$scope[filter] = $scope.toWatch[filter] == 'true';
				}*/
				// $scope[filter] = $scope.toWatch[filter];
				__helper.assign($scope,filter,$scope.toWatch[filter]);
			}			
			$location.search('');
			$scope.pagination();	
			if (__debug) {console.debug('All filters are resetted');}		
		}		
	}	
	
	$scope.savedFilters = [];
	//REFRESH FILTER BOX
	$scope.refreshFilterBox = function(){
		$scope.savedFilters = $scope.getSavedFilters();
		var html = '<ul id="restore-filter-options">';
		if (!isEmpty($scope.savedFilters)) {
			angular.forEach($scope.savedFilters,function(value,key){			
				html+= '<li title="'+objectToString(value,'|')
				+'"><span class="index">'+(key+1)+'</span><input class="hidden-inputs" type="radio" id="'+key+'" title="" name="filter"><label class="label-hidden-input" for="'+key+'">'+objectToString(value,'|')
				+'</label><i ng-click="removeFromSavedFilters('+key+')" class="fa fa-times"></i>';
			});
		}else{
			html+= '<li>Kein gespeicherter Filter</li>';
		}
		html+= '</ul>';
		var temp = $compile(html)($scope);
		angular.element('#filter-restore-box #content').html(temp);		
		return $scope.savedFilters;
		if (__debug) {console.debug('refreshed');}
	}

	//SAVE CURRENT FILTER
	$scope.saveCurrentFilter = function(){		
		if(isEmpty($location.search())){console.warn('Es gibt kein Filter argumente');return false;}
		var savedFilters;
		var date = $filter('date')(Date.now(),'dd.MM.yyyy HH:mm');
		$location.search('date',date);	
		var search = $location.search();
		if (isEmpty(savedFilters = $scope.getSavedFilters())) {
			$scope.createIfEmpty({
				key:__ls.getItem($scope.yogamaker+'.filter'),
				value: angular.toJson([])
			});			
		}
		var uniq = savedFilters.every(function(filter){
			return !angular.equals(filter, search);
		});		
		if (!uniq) {console.warn('Der Filter ist schon vorhanden');return false;}
		savedFilters.push(search);			
		__ls.setItem(__ls.getItem('yogamaker').toLowerCase()+'.filter',angular.toJson(savedFilters));
		$location.search('date',null);
		setTimeout(function() {$scope.refreshFilterBox();}, 50);		
		if (__debug) {console.debug(savedFilters);}
	}

	//UPDATE FILTERS
	$scope.updateFilters = function(filter){
		__ls.setItem(__ls.getItem('yogamaker').toLowerCase()+'.filter',angular.toJson(filter));
	}

	//GET SAVED FILTERS
	$scope.getSavedFilters = function(){
		var savedFilters = [];
		var filter;
		if (!isEmpty(__ls.getItem(__ls.getItem('yogamaker').toLowerCase()+'.filter'))) {
			filter = angular.fromJson(__ls.getItem(__ls.getItem('yogamaker').toLowerCase()+'.filter'));
			for(let i=0;i<filter.length;i++){
				savedFilters.push(angular.fromJson(filter[i]));
			}
			if (__debug) {console.debug(savedFilters);}
			return savedFilters;
		}else{
			return [];
		}	
	}
	
	//RESTORE FILTER
	$scope.restoreFilter = function(){			
		var rf = __helper.open.restoreFilterBox($scope,$compile);
		var html = $scope.refreshFilterBox();		
	}
	
	//APPLY FILTER
	$scope.applyFilter = function(){		
		var index = angular.element('#restore-filter-options input:checked').attr('id');		
		angular.element('#filter-restore-box').toggle();
		angular.element('#filter-restore-box #content').html();
		$scope.resetFilter();
		angular.forEach($scope.savedFilters[index],function(value,key){
			if(__debug){console.log('scope.'+key+':'+value);}
			__helper.assign($scope,key,value);			
			// $scope[key] = value;			
		});
		$scope.pagination();
	}

	//REMOVE FROM SAVED FILTERS
	$scope.removeFromSavedFilters = function(index){
		$scope.savedFilters.splice(index,1);		
		$scope.updateFilters($scope.savedFilters);
		$scope.refreshFilterBox();
	}
	//++++++++++++++++++ end of filter functions
	
	//REMOVE FROM SEARCH
	$scope.removeFromSearch = function(event){
		var tag = angular.element(event.target).parent().parent(),
		text = tag.text().replace(new RegExp(/\s*$/,'g'),'');				
		$scope.searchFilter = $scope.searchFilter.split(__seperator).filter(function(val,key){
			return val !== text;
		}).join(__seperator);
		var newSearch,search = $location.search().searchFilter;
		if (angular.isArray(search)) {
			newSearch = search.filter(function(val,key){
				return val !== text;
			});
		}else{
			if(!isEmpty(search)){
				newSearch = search.split(__seperator).filter(function(val,key){
					return val !== text;
				});
			}
		}
		if (__debug) { 
			console.log(newSearch);
			console.log(search);
		}
		try{
			$location.search('searchFilter',newSearch);
		}catch(e){
			console.warn('Error:',e);
		}
		$scope.searchFilter = deleteSeperator($scope.searchFilter);
		$scope.pagination();
	}

	//CANCELED
	$scope.canceled = function(index){
		if(__debug){console.error('Canceled:'+index);}
	}
	//DRAG STARTED
	$scope.dragStarted = function(event){
		if (event.dataTransfer.setDragImage) {		
         var img = new Image();
         img.src = addTextToImg(__imgSrc,event.target.dataset.title);
         event.dataTransfer.setDragImage(img, 50,50);
         $scope.dragging = true;
       }
		if(__debug){console.info('DragStarted:'+event);}
	}

	$scope.dragEnded = function(event){
		$scope.dragging = false;
	}

	//ADD TEXT TO IMG
	function addTextToImg(src,text){
		var canvas = document.getElementById('matText');
		var ctx = canvas.getContext('2d');
		var imageObj = new Image();
		imageObj.onload = function() {
			var marginImg = (canvas.width- imageObj.width)/2
			ctx.drawImage(imageObj, marginImg,0);
			ctx.strokeStyle = 'rgb(188,188,188)';
			ctx.rect(0, 0, canvas.width, canvas.height);
			ctx.stroke();		
		};
		imageObj.src = src;	     	
		ctx.font = '12px "PT Serif"';		
		var marginText = (ctx.measureText(text).width<canvas.width)?(canvas.width-ctx.measureText(text).width)/2:0;
		ctx.fillText(text,marginText, 100,canvas.width);		
		var url = ctx.canvas.toDataURL();
		ctx.clearRect(0,0,canvas.width,canvas.height);
		return url;
	}

	//DELETE SEPERATOR
	function deleteSeperator(input){
		if(isEmpty(input) || input.indexOf(__seperator) == -1) return input;
		var trimmer = __seperator,
			result = '';
		
		var splitted = input.split(trimmer);
		for(var i=0;i<splitted.length;i++){
			if (splitted[i] != '') {
				result+=splitted[i];
				if (i!= splitted.length-1) result+=trimmer;
			}
		}
		return result;		
	}
}]);

//config
app.config(function($locationProvider) {
    $locationProvider.html5Mode({
    	enabled: true,
    	requireBase: false,
    	rewriteLinks: true
    });
});

//services


//filters
app.filter('camelCase', function() {
	return function(input) {
		input = input || '';
		return input.replace(new RegExp(/_/,'g'),' ').replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
	};
});

app.filter('searchedWords', function() {
	return function(input) {
		try{
			input = input.split(__seperator)||'';
		}catch(e){
			console.warn('Error:',e);
		}
		return input;
	};
});

app.filter('allFilter', function($filter) {
	// orderBy:sortBy:sortReverse | filter:levelFilter |filter:typeFilter | multiSearchFilter:search
	return function(input,sortby,direction,levFil,typFil,search,xor) {
		var xor = isEmpty(xor)?true:xor==='und';		
		var filtered = $filter('orderBy')(input,sortby,direction);
			if (xor) {
				filtered = $filter('filter')(filtered,levFil);
				filtered = $filter('filter')(filtered,typFil);
				filtered = $filter('multiSearchFilter')(filtered,search);
			}else{
				search = customSplit(search);
				if (!isEmpty(search)) {
					filtered = $filter('concatFilter')(filtered,[levFil,typFil].concat(search));	
				}else{
					filtered = $filter('concatFilter')(filtered,[levFil,typFil]);	
				}
							
			}			
		return filtered;
	};
});

function getFiltered(filter,asanas,tag){
	var angFilter = filter('filter'),
	angLimit = filter('limitTo');
	var result = [];
	var nameFilter = angFilter(asanas, {title:tag}),
	sanskritNameFilter = angFilter(asanas, {sanskrit_name:tag}),
	vorbereitendeFilter = angFilter(asanas, {vorbereitende:tag}),
	anschliessendeFilter = angFilter(asanas, {anschliessende:tag});
	typeFilter = angFilter(asanas, tag);
	levelFilter = angFilter(asanas, tag);

	sanskritNameFilter.map(function(obj){obj['data_filter'] = 'sanskritNameFilter';});
	vorbereitendeFilter.map(function(obj){obj['data_filter'] = 'vorbereitendeFilter';});
	anschliessendeFilter.map(function(obj){obj['data_filter'] = 'anschliessendeFilter';});
	nameFilter.map(function(obj){obj['data_filter'] = 'nameFilter';});
	

	result = result.concat(nameFilter).concat(sanskritNameFilter).concat(vorbereitendeFilter).concat(anschliessendeFilter).concat(typeFilter).concat(levelFilter);
	return filter('unique')(result,'title');
}

app.filter('concatFilter', function($filter) {
   return function(asanas, tags,filterType,uniq) {
      var result = [];
      var uniq = isEmpty(uniq)?true:uniq; 
      var angFilter = isEmpty(filterType)?$filter('filter'):$filter(filterType);
			tags.some(function(tag){
				if(!isEmpty(tag)){
					result = result.concat(getFiltered($filter,asanas,tag));
				}
			});
			return uniq?$filter('unique')(result,'title'):result;
   };
});

app.filter('multiSearchFilter', function($filter) {
	var angFilter = $filter('filter');
	return function(asanas,tags){
		tags = customSplit(tags);
		if (!angular.isArray(tags)){			
			return getFiltered($filter,asanas,tags);
		} 
		else{			
			return $filter('concatFilter')(asanas,tags,'filter');
		}
	}	
});

app.filter('unique', function() {
   return function(collection, keyname) {
      var output = [], 
          keys = [];

      angular.forEach(collection, function(item) {
          var key = item[keyname];
          if(keys.indexOf(key) === -1) {
              keys.push(key);
              output.push(item);
          }
      });

      return output;
   };
});

// directives
app.directive('asana',function(){
	return{
		templateUrl : appSettings.pluginDirUrl+'angularjs/templates/asanas.html'
	}
});

app.directive('sab',function(){
	return{
		templateUrl : appSettings.pluginDirUrl+'angularjs/templates/sab.html'
	}
});

app.directive('selectedAsanas',function(){
	return{
		templateUrl : appSettings.pluginDirUrl+'angularjs/templates/selected-asanas.html'
	}
});

app.directive('toPrint',function(){
	return{
		templateUrl : appSettings.pluginDirUrl+'angularjs/templates/to-print.html'
	}
});

app.directive('ngEnter', function () {
    return function (scope, element, attrs) {
        element.bind("keydown keypress", function (event) {
            if (event.which === 13) {
                scope.$apply(function () {
                    scope.$eval(attrs.ngEnter);
                });
                event.preventDefault();
            }
        });
    };
});

app.directive('ngRightClick', function($parse) {
    return function(scope, element, attrs) {
        var fn = $parse(attrs.ngRightClick);
        element.bind('contextmenu', function(event) {
            scope.$apply(function() {
                event.preventDefault();
                fn(scope, {$event:event});
            });
        });
    };
});

var iosDragDropShim = { enableEnterLeave: true }

/*experimental*/
jQuery(document).ready(function($) {
	function stickyConfigMenu(){
		var offsetTop = jQuery('#filter-sort-search').offset().top;
		var tp;
		if (jQuery(window).width() > 768) {
			jQuery(window).scroll(function(){
				if(jQuery(this).scrollTop() > offsetTop){		
					tp = (jQuery('.td-header-menu-wrap.td-affix').length)?jQuery('.td-header-menu-wrap.td-affix').position().top+jQuery('.td-header-menu-wrap').height():0;
					jQuery('#filter-sort-search').addClass('sticky-config');
					jQuery('#filter-sort-search').css('top',tp)

				}else{
					jQuery('#filter-sort-search').removeClass('sticky-config');
				}
			});	
		}else{
			jQuery('#filter-sort-search').removeClass('sticky-config');
		}
	}
	stickyConfigMenu();
	$(window).resize(function(){
		stickyConfigMenu();
	});
});