<?php
session_start();

// Only allow admin users to view OTPs
if(!isset($_SESSION["userlogin"]) || !isset($_SESSION["userLevel"]) || $_SESSION["userLevel"] != 3) {
    die("Access denied. Admin access required.");
}

include "lib/Database.php";
$db = new Database();

echo "<h1>🔐 Admin OTP Manager</h1>";
echo "<p><strong>Admin:</strong> " . $_SESSION["userFName"] . " " . $_SESSION["userLName"] . "</p>";

if(isset($_POST["show_otp"])) {
    $email = mysqli_real_escape_string($db->link, $_POST["email"]);
    
    echo "<h2>OTP Details for: $email</h2>";
    
    $otpQuery = "SELECT * FROM tbl_otp WHERE email = '$email' ORDER BY created_at DESC LIMIT 5";
    $result = $db->select($otpQuery);
    
    if($result && $result->num_rows > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>OTP Code</th><th>Purpose</th><th>Used</th><th>Expires</th><th>Created</th></tr>";
        while($row = $result->fetch_assoc()) {
            $isExpired = (strtotime($row["expires_at"]) < time()) ? "🔴 Expired" : "🟢 Valid";
            $isUsed = $row["is_used"] ? "✅ Used" : "⏳ Unused";
            
            echo "<tr>";
            echo "<td><strong>" . $row["otp"] . "</strong></td>";
            echo "<td>" . $row["purpose"] . "</td>";
            echo "<td>$isUsed</td>";
            echo "<td>$isExpired</td>";
            echo "<td>" . $row["created_at"] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No OTP records found for this email.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Admin OTP Manager</title></head>
<body>
    <form method="post">
        <label>User Email:</label>
        <input type="email" name="email" required>
        <button type="submit" name="show_otp">Show OTP Details</button>
    </form>
    <p><a href="dashboard_agent.php">← Back to Admin Dashboard</a></p>
</body>
</html>