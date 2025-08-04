<!--Footer Section Start------------->
<!--Javascript Files Start------------->
	<script type="text/javascript" src="../js/jquery.min.js"></script>
	<script type="text/javascript" src="../js/bootstrap.min.js"></script>
	<script type="text/javascript" src="../css/datatable/jquery.datatable.min.js"></script>
	<script type="text/javascript" src="../css/slickslider/slick.min.js"></script>

<!--Javascript Files End------------->

<!--Script Start------------->
<script>

// NO TINYMCE - Simple text editor only
// Convert all tinymce textareas to simple textareas with nice styling
document.addEventListener('DOMContentLoaded', function() {
	const textareas = document.querySelectorAll('textarea.tinymce');
	textareas.forEach(function(textarea) {
		// Remove tinymce class
		textarea.classList.remove('tinymce');
		
		// Add nice styling for simple textarea
		textarea.style.cssText = `
			width: 100%;
			min-height: 200px;
			padding: 15px;
			border: 2px solid #ddd;
			border-radius: 5px;
			font-family: Arial, sans-serif;
			font-size: 14px;
			line-height: 1.5;
			resize: vertical;
			background-color: #fff;
			transition: border-color 0.3s ease;
		`;
		
		// Add focus effect
		textarea.addEventListener('focus', function() {
			this.style.borderColor = '#007cba';
			this.style.outline = 'none';
		});
		
		textarea.addEventListener('blur', function() {
			this.style.borderColor = '#ddd';
		});
		
		// Add placeholder if not exists
		if (!textarea.placeholder) {
			textarea.placeholder = 'Enter description here...';
		}
	});
	
	console.log('Simple text editors initialized - No TinyMCE required!');
});

function togglebtn(){
	var x = document.getElementById("navList");
	if (x.style.display === "block"){
		x.style.display = "none";
	} else{
		x.style.display = "block";
	}
}

function togglebtn_sitedetails(){
	var x = document.getElementById("sitedetailsNav");
	if (x.style.display === "block"){
		x.style.display = "none";
	} else{
		x.style.display = "block";
	}
}

function togglebtn_category(){
	var x = document.getElementById("categoryNav");
	if (x.style.display === "block"){
		x.style.display = "none";
	} else{
		x.style.display = "block";
	}
}

function togglebtn_personalize(){
	var x = document.getElementById("personalizeNav");
	if (x.style.display === "block"){
		x.style.display = "none";
	} else{
		x.style.display = "block";
	}
}

$(document).ready(function() {
    $('#example').DataTable();
});

function userApprove(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "owner_list.php?action=" + status + "&usrid=" + id;
    }
}

function userAction(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "owner_list.php?action=" + status + "&usrid=" + id;
    }
}

function propertyAction(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "property_list_admin.php?action=" + status + "&pid=" + id;
    }
}

function bookingAction(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "booking_list.php?action=" + status + "&bid=" + id;
    }
}

function bookingActionOwner(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "booking_list_owner.php?action=" + status + "&bid=" + id;
    }
}

function categoryAction(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "category_list.php?action=" + status + "&catid=" + id;
    }
}

function adAction(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "property_by_owner.php?action=" + status + "&adid=" + id;
    }
}

function imgAdAction(id, status){
    var dlt = confirm("Are you sure?");
    if(dlt == true){
        window.location = "property_by_owner.php?action=" + status + "&imgid=" + id;
    }
}

</script>
<!--Script End------------->
<!--Footer Section End------------->
</body>
</html>
