<?php
// Simple signup test page
echo "<!DOCTYPE html>";
echo "<html><head><title>Simple Signup Test</title></head><body>";
echo "<h1>🧪 Simple Signup Test</h1>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "<h2>✅ Form Submitted Successfully!</h2>";
    echo "<p><strong>Data received:</strong></p>";
    echo "<ul>";
    foreach ($_POST as $key => $value) {
        echo "<li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($value) . "</li>";
    }
    echo "</ul>";
    echo "<p><a href='?'>Try Again</a></p>";
} else {
    echo "<h2>Simple Registration Form</h2>";
    echo "<form method='POST'>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><td>First Name:</td><td><input type='text' name='fname' required></td></tr>";
    echo "<tr><td>Last Name:</td><td><input type='text' name='lname' required></td></tr>";
    echo "<tr><td>Username:</td><td><input type='text' name='username' required></td></tr>";
    echo "<tr><td>Email:</td><td><input type='email' name='email' required></td></tr>";
    echo "<tr><td>Phone:</td><td><input type='tel' name='cellno' required></td></tr>";
    echo "<tr><td>Password:</td><td><input type='password' name='password' required></td></tr>";
    echo "<tr><td>Confirm Password:</td><td><input type='password' name='cnf_password' required></td></tr>";
    echo "<tr><td>Account Type:</td><td>";
    echo "<select name='level' required>";
    echo "<option value=''>Select Type</option>";
    echo "<option value='1'>House Seeker</option>";
    echo "<option value='2'>Property Owner</option>";
    echo "<option value='3'>Real Estate Agent</option>";
    echo "</select>";
    echo "</td></tr>";
    echo "<tr><td colspan='2'><button type='submit' name='register'>Register</button></td></tr>";
    echo "</table>";
    echo "</form>";
}

echo "</body></html>";
?>
