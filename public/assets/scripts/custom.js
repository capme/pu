/**
 * 
 */

jQuery(document).ready(function() {
	$(".page-sidebar-menu .arrow").each(function(){
		if($(this).parents("a").next().html() == ""){
			$(this).hide();
			$(this).parents("a").next().remove();
		}
	})
})