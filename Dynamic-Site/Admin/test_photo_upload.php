<?php
include '../lib/Session.php';
Session::init();
include '../lib/Database.php';
include '../helpers/Format.php';

spl_autoload_register(function($class){
    include_once '../classes/'.$class.'.php';
});

$pro = new Property();

// Simulate user login
Session::set("userlogin", true);
Session::set("userId", 1);

echo "<h2>Photo Upload Debug Test</h2>";

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_upload'])){
    echo "<h3>Form Submitted!</h3>";
    
    // Debug $_FILES array
    echo "<h4>$_FILES Array:</h4>";
    echo "<pre>" . print_r($_FILES, true) . "</pre>";
    
    // Debug $_POST array
    echo "<h4>$_POST Array:</h4>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Check if file was uploaded
    if(isset($_FILES['adimg']) && !empty($_FILES['adimg']['name'])) {
        echo "<h4>File Upload Details:</h4>";
        echo "<ul>";
        echo "<li><strong>File Name:</strong> " . $_FILES['adimg']['name'] . "</li>";
        echo "<li><strong>File Size:</strong> " . $_FILES['adimg']['size'] . " bytes</li>";
        echo "<li><strong>File Type:</strong> " . $_FILES['adimg']['type'] . "</li>";
        echo "<li><strong>Temp Name:</strong> " . $_FILES['adimg']['tmp_name'] . "</li>";
        echo "<li><strong>Error Code:</strong> " . $_FILES['adimg']['error'] . "</li>";
        echo "</ul>";
        
        // Check for upload errors
        switch($_FILES['adimg']['error']) {
            case UPLOAD_ERR_OK:
                echo "<p style='color:green;'>✓ No upload errors</p>";
                break;
            case UPLOAD_ERR_INI_SIZE:
                echo "<p style='color:red;'>✗ File exceeds upload_max_filesize</p>";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "<p style='color:red;'>✗ File exceeds MAX_FILE_SIZE</p>";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "<p style='color:red;'>✗ File partially uploaded</p>";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "<p style='color:red;'>✗ No file uploaded</p>";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "<p style='color:red;'>✗ Missing temporary folder</p>";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "<p style='color:red;'>✗ Failed to write file to disk</p>";
                break;
            default:
                echo "<p style='color:red;'>✗ Unknown upload error</p>";
                break;
        }
        
        // Test file validation
        $file_name = $_FILES['adimg']['name'];
        $permited = array('jpg', 'jpeg', 'png');
        $div = explode('.', $file_name);
        $file_ext = strtolower(end($div));
        
        echo "<h4>File Validation:</h4>";
        echo "<ul>";
        echo "<li><strong>File Extension:</strong> " . $file_ext . "</li>";
        echo "<li><strong>Allowed Extensions:</strong> " . implode(', ', $permited) . "</li>";
        echo "<li><strong>Valid:</strong> " . (in_array($file_ext, $permited) ? "Yes" : "No") . "</li>";
        echo "</ul>";
        
        if(in_array($file_ext, $permited) && $_FILES['adimg']['error'] == 0) {
            // Generate unique filename
            $unique_image = substr(md5(time()), 0, 10).'.'.$file_ext;
            $upload_path = "../uploads/ad_image/".$unique_image;
            $relative_path = "uploads/ad_image/".$unique_image;
            
            echo "<h4>Upload Attempt:</h4>";
            echo "<ul>";
            echo "<li><strong>Unique Filename:</strong> " . $unique_image . "</li>";
            echo "<li><strong>Upload Path:</strong> " . $upload_path . "</li>";
            echo "<li><strong>Relative Path:</strong> " . $relative_path . "</li>";
            echo "</ul>";
            
            // Check if upload directory is writable
            if(is_writable("../uploads/ad_image/")) {
                echo "<p style='color:green;'>✓ Upload directory is writable</p>";
            } else {
                echo "<p style='color:red;'>✗ Upload directory is not writable</p>";
            }
            
            // Attempt upload
            if(move_uploaded_file($_FILES['adimg']['tmp_name'], $upload_path)) {
                echo "<p style='color:green;'>✓ File uploaded successfully!</p>";
                echo "<p><strong>Uploaded to:</strong> " . $upload_path . "</p>";
                
                // Check if file exists
                if(file_exists($upload_path)) {
                    echo "<p style='color:green;'>✓ File exists on server</p>";
                    echo "<p><strong>File size on server:</strong> " . filesize($upload_path) . " bytes</p>";
                } else {
                    echo "<p style='color:red;'>✗ File does not exist on server</p>";
                }
            } else {
                echo "<p style='color:red;'>✗ Failed to move uploaded file</p>";
                echo "<p>Source: " . $_FILES['adimg']['tmp_name'] . "</p>";
                echo "<p>Destination: " . $upload_path . "</p>";
                
                // Check source file exists
                if(file_exists($_FILES['adimg']['tmp_name'])) {
                    echo "<p style='color:green;'>✓ Temp file exists</p>";
                } else {
                    echo "<p style='color:red;'>✗ Temp file does not exist</p>";
                }
            }
        }
        
    } else {
        echo "<p style='color:red;'>No file uploaded</p>";
    }
}

// Show PHP upload settings
echo "<h3>PHP Upload Settings:</h3>";
echo "<ul>";
echo "<li><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</li>";
echo "<li><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</li>";
echo "<li><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</li>";
echo "<li><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? 'On' : 'Off') . "</li>";
echo "<li><strong>upload_tmp_dir:</strong> " . (ini_get('upload_tmp_dir') ?: 'Default') . "</li>";
echo "</ul>";

// Check directory permissions
echo "<h3>Directory Permissions:</h3>";
$upload_dir = "../uploads/ad_image/";
echo "<ul>";
echo "<li><strong>Upload directory exists:</strong> " . (is_dir($upload_dir) ? "Yes" : "No") . "</li>";
echo "<li><strong>Upload directory writable:</strong> " . (is_writable($upload_dir) ? "Yes" : "No") . "</li>";
echo "<li><strong>Upload directory readable:</strong> " . (is_readable($upload_dir) ? "Yes" : "No") . "</li>";
echo "</ul>";
?>

<h3>Test Photo Upload:</h3>
<form method="POST" action="" enctype="multipart/form-data">
    <p>
        <label><b>Select Photo:</b></label><br>
        <input type="file" name="adimg" accept="image/*" style="padding:5px;">
        <small style="color:#666;">Supported formats: JPG, JPEG, PNG</small>
    </p>
    
    <p>
        <button type="submit" name="test_upload" style="padding:10px 20px; background:#007cba; color:white; border:none; cursor:pointer;">Test Upload</button>
    </p>
</form>

<h3>Recent Uploaded Images:</h3>
<?php
$image_dir = "../uploads/ad_image/";
$images = array_diff(scandir($image_dir), array('.', '..'));
$recent_images = array_slice(array_reverse($images), 0, 5);

if(!empty($recent_images)) {
    echo "<div style='display:flex; gap:10px; flex-wrap:wrap;'>";
    foreach($recent_images as $image) {
        if(in_array(strtolower(pathinfo($image, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png'])) {
            echo "<div style='text-align:center;'>";
            echo "<img src='../uploads/ad_image/$image' style='width:100px; height:100px; object-fit:cover; border:1px solid #ccc;' alt='$image'>";
            echo "<br><small>$image</small>";
            echo "</div>";
        }
    }
    echo "</div>";
} else {
    echo "<p>No images found</p>";
}
?>
