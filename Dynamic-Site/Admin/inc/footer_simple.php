<!--Footer Section Start------------->		
	<footer class="footersection overflow">
		<div class="footer-bottom">
		</div>
	</footer>
<!--Footer Section End------------->

<!--Javascript Files for TinyMce Editor Start------------->	
	<!-- TinyMce Javascript local file ---->
	<script type="text/javascript" src="../css/tinymce/js/tinymce/tinymce.min.js?v=<?php echo time(); ?>"></script>
<!--Javascript Files for TinyMce Editor End------------->

<!--Script for TinyMce Editor Start------------->
<script>
// Simple TinyMCE initialization with complete fallback
try {
	tinymce.init({
		selector: 'textarea.tinymce',
		plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste wordcount',
		toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
		height: 400,
		branding: false,
		promotion: false,
		// Disable all cloud services and premium features
		cloud_server: false,
		forced_root_block: 'p',
		// Prevent any API calls
		referrer_policy: 'no-referrer',
		// Complete offline mode
		skin: false,
		content_css: false,
		setup: function (editor) {
			editor.on('init', function () {
				console.log('TinyMCE initialized successfully without cloud services');
			});
		},
		init_instance_callback: function (editor) {
			console.log('TinyMCE instance initialized: ' + editor.id);
		}
	});
} catch (error) {
	console.error('TinyMCE initialization failed:', error);
	// Fallback: Remove tinymce class from all textareas
	document.addEventListener('DOMContentLoaded', function() {
		const textareas = document.querySelectorAll('textarea.tinymce');
		textareas.forEach(function(textarea) {
			textarea.classList.remove('tinymce');
			textarea.style.width = '100%';
			textarea.style.minHeight = '200px';
			textarea.style.padding = '10px';
			textarea.style.border = '1px solid #ddd';
			textarea.style.fontFamily = 'Arial, sans-serif';
		});
		
		// Show notification
		const notification = document.createElement('div');
		notification.style.cssText = 'background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; margin: 10px 0; border-radius: 4px;';
		notification.innerHTML = '⚠ Rich text editor unavailable. Using simple text editor.';
		
		const firstTextarea = document.querySelector('textarea');
		if (firstTextarea && firstTextarea.parentNode) {
			firstTextarea.parentNode.insertBefore(notification, firstTextarea);
		}
	});
}
</script>
<!--Script for TinyMce Editor End------------->

<!--Javascript Fiels for Data Table Start------------->
	<script type="text/javascript" src="../js/jquery-3.5.1.js"></script>
	<script type="text/javascript" src="../js/jquery.dataTables.min.js"></script>
	
	<!-- Data Table Javascript cdn ---->
	<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
	<script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<!--Javascript Fiels for Data Table End------------->

<!-- Initializing Data Table Start---->
	<script>
		$(document).ready(function() {
			$('#example').DataTable();
		} );
	</script>
<!-- Initializing Data Table End---->

<!--Script for responsive navbar Start------------>
<script>
	var navList = document.getElementById("navList");
	function togglebtn(){
		navList.classList.toggle("hidemenu");
	}
	
	var sitedetailsNav = document.getElementById("sitedetailsNav");
	function togglebtn_sitedetails(){
		sitedetailsNav.classList.toggle("hide_dashboard_menu");
	}
	
	var categoryNav = document.getElementById("categoryNav");
	function togglebtn_category(){
		categoryNav.classList.toggle("hide_dashboard_menu");
	}
	
	var personalizeNav = document.getElementById("personalizeNav");
	function togglebtn_personalize(){
		personalizeNav.classList.toggle("hide_dashboard_menu");
	}
	
	var propertyNav = document.getElementById("propertyNav");
	function togglebtn_property(){
		propertyNav.classList.toggle("hide_dashboard_menu");
	}
	
	var dashboardNav = document.getElementById("dashboardNav");
	function togglebtn_dashboard(){
		dashboardNav.classList.toggle("hide_dashboard_menu");
	}
</script>
<!--Script for responsive navbar End------------>
</html>
