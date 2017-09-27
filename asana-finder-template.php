<?php
/*
Template Name: Asana-Finder-App
*/
 require_once('angularjs/head.php');
  ?> 
  <div class="td-main-content-wrap td-main-page-wrap" ng-app="asanaFinder" ng-controller="mainCtrl"  dnd-list="[]">
    <div class="td-container tdc-content-wrap">
      <div class="vc_row wpb_row td-pb-row"> 
        <div class="app-container">          
          <?php require_once('angularjs/templates/top.php'); ?>
          <div id="progress" ng-show="!posts.length">
            <img  src="<?php echo $local; ?>/wp-admin/images/spinner-2x.gif">
          </div>    
          <div ng-show="posts.length && !(posts|allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length" class="no-result-text">Keine Ergebnisse für die Suche</div>
          <div ng-show="posts.length && (typeFilter.length || levelFilter.length || searchFilter.length ) && ((posts|allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length != (paginated |allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length)" class="result-paginated">Um den Filter auf alle Asanas anzuwenden, klicke auf "Alle Anzeigen"</div>
          <asana class="asana" id="asana-container" ng-show="posts.length && (posts|allFilter:sortBy:sortReverse:levelFilter:typeFilter:searchFilter:XOR).length"></asana>      
          <div ng-show="!searchFilter.length" data-pagination="" data-num-pages="numPages();" 
          data-current-page="currentPage" data-max-size="maxSize"  
          data-boundary-links="true">                         
          </div>      
          <sab></sab>
          <to-print></to-print>     
          <canvas id="matText" style="display:none;" width="110" height="150"></canvas>
        </div>   
      </div>
    </div>
</div>
<?php
get_footer();
?>
