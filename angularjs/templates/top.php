<?php 
$level_filter = new WP_Query(array(
	'post_type' => 'asana_level'
	));
$type_filter = new WP_Query(array(
	'post_type' => 'asana_type',
	'posts_per_page'=>30,
	));

function create_input($settings = array('label'=>'','id'=>'','ngIf'=>'','type'=>'','label_ngClass'=>'','ngClick'=>'','ngChecked'=>'','ngModel'=>'')){
	$id = $settings["id"] != ''?'id="'.$settings["id"].'"':'';
	$ngIf = $settings["ngIf"] != '' ? 'ng-if="'.$settings["ngIf"].'"':'';
	$ngClass = $settings["ngClass"] != '' ? 'ng-class="'.$settings["ngClass"].'"':'';
	$ngDisabled = $settings["ngDisabled"] != '' ? 'ng-disabled="'.$settings["ngDisabled"].'"':'';
	$ngTitle = $settings["ngTitle"] != '' ? 'ng-attr-title="'.$settings["ngTitle"].'"':'';
	$ngClick = $settings["ngClick"] != '' ? 'ng-click="'.$settings["ngClick"].'"':'';
	$ngModel = $settings["ngModel"] != '' ? 'ng-model="'.$settings["ngModel"].'"':'';
	$ngChecked = $settings["ngChecked"] != '' && $settings["type"] == 'checkbox'? 'ng-checked="'.$settings["ngChecked"].'"':'';

	echo '<input type="'.$settings["type"].'" class="hidden-inputs" '.$ngModel.' '.$ngDisabled.' '.$ngClick.' '.$ngChecked.' '.$id.'><label '.$ngIf.' class="label-hidden-input label-inline" title="'.strip_tags($settings["label"]).'" '.$ngTitle.' for="'.$settings["id"].'" '.$ngClass.'><span class="label-wrap"><span>'.$settings["label"].'</span></span></label>';
}
?>
<div id="user" ng-if="yogamaker">Willkommen <span id="user-name">{{yogamaker}}</span></div>
<div id="fss-wrapper">
	<div ng-show="posts.length" id="filter-sort-search" ng-class="{'force-no-sticky':box.notSticky}">		
		<div id="search-input">
			<input ng-model="searchFilter" ng-enter="searchAll()" type="text" name="search" value="" placeholder="Asana Title eingeben und enter drücken" ng-model-options="{updateOn:'blur'}" data-search-now autofocus>
			<div ng-class="{'no-result': (paginated |allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length == 0 }" class="result-asana-number general">
			<span id="pr">{{(paginated |allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length}}</span>
				/ 
				<span id="ar">{{(posts |allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length + (SFA?followingAsanas.length:0) + (SPA?preparingAsanas.length:0) + (SPFA?mutualAsanas.length:0)}}</span>
			</div>               
		</div>    

		<div id="config">          
			<select ng-model="levelFilter" title="Filtern nach Level" class="label-hidden-input">
				<option value="">Filtern nach Level</option>
				<?php while($level_filter->have_posts()):$level_filter->the_post(); ?>
					<option value="<?php the_title(); ?>"><?php the_title(); ?></option>
				<?php endwhile;?>                              
			</select>
			<select ng-model="XOR" title="UND / ODER" class="label-hidden-input" ng-show="levelFilter&&typeFilter || levelFilter&&searchFilter || typeFilter&&searchFilter" ng-options="opt for opt in ['und','oder']">             
			</select>    
			<select ng-model="typeFilter" title="Filtern nach Typen" class="label-hidden-input">
				<option value="">Filtern nach Typen</option>
				<?php while($type_filter->have_posts()):$type_filter->the_post(); ?>
					<option value="<?php the_title(); ?>"><?php the_title(); ?></option>
				<?php endwhile;?>                              
			</select>
			<?php create_input(array('type'=>'checkbox','id'=>'show-all','ngChecked'=>'showAll','ngModel'=>'showAll','ngClick'=>'pagination();','label'=>'Alle anzeigen','ngTitle'=>'{{showAll?\'9 anzeigen\':\'Alle anzeigen\'}}','ngClass'=>'{\'warning-blink \':(typeFilter.length || levelFilter.length || searchFilter.length ) && ((posts|allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length != (paginated |allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length)}'));?>               
			<?php create_input(array('type'=>'checkbox','id'=>'sbTitle','ngChecked'=>'sortReverse','ngClick'=>'sortReverse = !sortReverse;','label'=>'Asana','ngClass' =>'{reversed : sortReverse }','ngTitle'=>'sortieren nach Name ´{{sortReverse? \'absteigend\':\'aufsteigend\'}}´'));?>                  
			<?php create_input(array('type'=>'button','id'=>'filter-speichern','ngIf'=>'!isGast','ngClick'=>'saveCurrentFilter();','label'=>'Filter speichern'));?>          
			<?php create_input(array('type'=>'button','id'=>'filter-abrufen','ngIf'=>'!isGast','ngClick'=>'restoreFilter();','label'=>'Filter abrufen'));?>          
			<?php create_input(array('type'=>'button','id'=>'zurueksetzen','ngClick'=>'resetFilter();','label'=>'Zurücksetzen'));?>          
			<?php create_input(array('type'=>'checkbox','id'=>'list-type','ngModel'=>'listType','label'=>'<i ng-class="{\'fa fa-bars\':listType,\'fa fa-th\':!listType}" aria-hidden="true"></i>','ngTitle'=>'Als {{listType?\'Boxen\':\'Liste\'}} anzeigen'));?>          
			<?php create_input(array('type'=>'checkbox','id'=>'toggle-sab',/*'ngIf'=>'!isGast',*/'ngModel'=>'box.closeSAB','label'=>'<i ng-class="{\'fa fa-eye-slash\':box.closeSAB,\'fa fa-eye\':!box.closeSAB}"></i>','ngTitle'=>'Sequenzbox {{box.closeSAB? \'öffnen\':\'schliessen\'}}'));?>          
		</div>
		<div id="top-search-filter-config" ng-class="{'opened':!box.enlarge && searchFilter.length && (followingAsanas.length || preparingAsanas.length || mutualAsanas.length),'warning-blink': !(SPA || SFA || SPFA) && (followingAsanas.length || preparingAsanas.length || mutualAsanas.length) }">
			<div id="inner">
				<?php create_input(array('type'=>'checkbox','id'=>'SPA','ngChecked'=>'SPA','ngModel'=>'SPA','label'=>'in vorbereitenden Asanas suchen {{preparingAsanas.length}}','ngDisabled'=>'!preparingAsanas.length','ngClass'=>'{\'not-empty warning-box\':preparingAsanas.length}'));?> 
				<?php create_input(array('type'=>'checkbox','id'=>'SPFA','ngChecked'=>'SPFA','ngModel'=>'SPFA','label'=>'in beiden suchen {{mutualAsanas.length}}','ngDisabled'=>'!mutualAsanas.length','ngClass'=>'{\'not-empty warning-box\':mutualAsanas.length}'));?> 
				<?php create_input(array('type'=>'checkbox','id'=>'SFA','ngChecked'=>'SFA','ngModel'=>'SFA','label'=>'in anschliessenden Asanas suchen {{followingAsanas.length}}','ngDisabled'=>'!followingAsanas.length','ngClass'=>'{\'not-empty warning-box\':followingAsanas.length}'));?> 
			</div>
		</div>         
		<div id="searched-words" dnd-list="(searchFilter| searchedWords)">
			<div class="words" ng-class="{'XOR': XOR === 'und'}" id="lF" ng-if="levelFilter">{{levelFilter}}
				<span class="removeTag" ng-click="resetFilter('levelFilter')"><i class="fa fa-times" aria-hidden="true"></i></span>
			</div>
			<div class="words XOR" ng-if="levelFilter&&typeFilter" ng-click="toggleXOR();">{{XOR}}          
			</div>
			<div class="words" ng-class="{'XOR': XOR === 'und'}" id="tF" ng-if="typeFilter">{{typeFilter}}
				<span class="removeTag" ng-click="resetFilter('typeFilter')"><i class="fa fa-times" aria-hidden="true"></i></span>
			</div>
			<div class="words XOR" ng-if="levelFilter&&searchFilter || typeFilter&&searchFilter" ng-click="toggleXOR();">{{XOR}}          
			</div>
			<div class="words orFilter" id="sw{{$index}}" ng-if="word.length" ng-repeat="word in (searchFilter| searchedWords) track by $index">{{word}}
				<span class="removeTag" ng-click="removeFromSearch($event);"><i class="fa fa-times" aria-hidden="true"></i></span>
			</div>        
		</div>
	</div>
</div>