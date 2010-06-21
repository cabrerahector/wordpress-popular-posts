<?php
	if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__)) exit('Please do not load this page directly');
?>

<style>
	#wpp-wrapper {width:100%}
	
		#wpp-wrapper h2 {margin:0 0 15px 0; color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:24px; font-style:italic}
		
		#wpp-wrapper h3 {color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:16px}
		
		#wpp-wrapper h4 {margin:0 0 4px 0; color:#666; font-weight:100; font-family:Georgia, "Times New Roman", Times, serif; font-size:13px}
		#wpp-wrapper h4 a {text-decoration:none}
		#wpp-wrapper h4 a:hover {text-decoration:underline}
		
		#wpp-stats-tabs {
			padding:2px 0;
		}
			
		#wpp-stats-canvas {
			overflow:hidden;
			padding:2px 0;
			width:570px;
		}
		
			.wpp-stats {
				display:none;
				width:544px;
				padding:10px;
				font-size:8px;
				background:#fff;
				border:#999 3px solid;
			}
			
			.wpp-stats-active {
				display:block;
			}
			
				.wpp-stats ol li {
					margin:0 0 10px 0;
					padding:0 0 2px 0;
					font-size:12px;
					line-height:12px;
					color:#999;
					border-bottom:#eee 1px solid;
				}
				
					.wpp-post-title {
						display:block;
						font-weight:bold;
					}
					
					.post-stats {
						display:block;
						font-size:9px!important;
						text-align:right;
						color:#999;
					}
					
				.wpp-stats-unique-item, .wpp-stats-last-item {
					margin:0!important;
					padding:0!important;
					border:none!important;
				}
</style>
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery("#wpp-stats-tabs a").click(function(){
			var activeTab = jQuery(this).attr("rel");
			jQuery(this).removeClass("button-secondary").addClass("button-primary").siblings().removeClass("button-primary").addClass("button-secondary");
			jQuery(".wpp-stats:visible").fadeOut("fast", function(){
				jQuery("#"+activeTab).slideDown("fast");
			});
			
			return false;
		});
			
		jQuery(".wpp-stats").each(function(){
			if (jQuery("li", this).length == 1) {
				jQuery("li", this).addClass("wpp-stats-last-item");
			} else {
				jQuery("li:last", this).addClass("wpp-stats-last-item");
			}
		});
	});
</script>
<div class="wrap">
	<div id="icon-index" class="icon32"><br /></div>
    <h2 id="wmpp-title">Wordpress Popular Posts Stats</h2>
    <p><?php _e("Click on each tab to see what are the most popular entries on your blog today, this week, last 30 days or all time since Wordpress Popular Posts was installed.", "wordpress-popular-posts"); ?></p>    
    <div id="wpp-stats-tabs">
    	<!--<a href="#" class="button-secondary" rel="wpp-yesterday"><?php _e("Yesterday", "wordpress-popular-posts"); ?></a>-->
        <a href="#" class="button-primary" rel="wpp-daily"><?php _e("Today", "wordpress-popular-posts"); ?></a>
        <a href="#" class="button-secondary" rel="wpp-weekly"><?php _e("Weekly", "wordpress-popular-posts"); ?></a>
        <a href="#" class="button-secondary" rel="wpp-monthly"><?php _e("Monthly", "wordpress-popular-posts"); ?></a>
        <a href="#" class="button-secondary" rel="wpp-all"><?php _e("All-time", "wordpress-popular-posts"); ?></a>
    </div>
    <div id="wpp-stats-canvas">
        <!--
        <div class="wpp-stats" id="wpp-yesterday">
            <?php //echo do_shortcode('[wpp range=yesterday stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]'); ?>
        </div>
        -->
        <div class="wpp-stats wpp-stats-active" id="wpp-daily">
            <?php echo do_shortcode('[wpp range=today stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]'); ?>
        </div>
        <div class="wpp-stats" id="wpp-weekly">
            <?php echo do_shortcode('[wpp range=weekly stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]'); ?>
        </div>
        <div class="wpp-stats" id="wpp-monthly">
            <?php echo do_shortcode('[wpp range=monthly stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]'); ?>
        </div>
        <div class="wpp-stats" id="wpp-all">
            <?php echo do_shortcode('[wpp range=all stats_views=1 order_by=views wpp_start=<ol> wpp_end=</ol>]'); ?>
        </div>
    </div>
    <?php    
	?>
</div>