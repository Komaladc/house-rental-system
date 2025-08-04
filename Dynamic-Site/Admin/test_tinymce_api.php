<?php include"inc/header.php"; ?>

<div style="padding: 20px; max-width: 800px; margin: 50px auto;">
    <h1>TinyMCE API Key Test - Add Property Form</h1>
    
    <div style="background: #f0f0f0; padding: 15px; margin: 20px 0; border-radius: 5px;">
        <h3>Instructions:</h3>
        <ol>
            <li>Open browser developer tools (F12)</li>
            <li>Go to the Network tab</li>
            <li>Look for any requests to "tiny.cloud" or API key errors</li>
            <li>The editor should load without any cloud service calls</li>
        </ol>
    </div>

    <form>
        <div style="margin: 20px 0;">
            <label for="property-description" style="display: block; font-weight: bold; margin-bottom: 10px;">
                Property Description (This should work without API key):
            </label>
            <textarea class="tinymce" name="addetails" id="property-description">
                <p><strong>Sample property description</strong></p>
                <p>This editor should work completely offline without any API key requirements.</p>
                <ul>
                    <li>Test formatting</li>
                    <li>Test lists</li>
                    <li>Test other features</li>
                </ul>
            </textarea>
        </div>
        
        <div style="margin: 20px 0;">
            <button type="button" onclick="testTinyMCE()" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;">
                Test TinyMCE Content
            </button>
            <div id="test-result" style="margin-top: 10px; padding: 10px; background: #e7f3ff; border-radius: 3px; display: none;"></div>
        </div>
    </form>
</div>

<script>
function testTinyMCE() {
    try {
        var content = tinymce.get('property-description').getContent();
        document.getElementById('test-result').innerHTML = '<strong>Success!</strong> TinyMCE is working properly. Content: ' + content.substring(0, 100) + '...';
        document.getElementById('test-result').style.display = 'block';
        document.getElementById('test-result').style.background = '#d4edda';
        console.log('TinyMCE test successful - no API key required!');
    } catch (error) {
        document.getElementById('test-result').innerHTML = '<strong>Error:</strong> ' + error.message;
        document.getElementById('test-result').style.display = 'block';
        document.getElementById('test-result').style.background = '#f8d7da';
        console.error('TinyMCE test failed:', error);
    }
}

// Monitor for any API key related errors
window.addEventListener('error', function(e) {
    if (e.message && e.message.toLowerCase().includes('api')) {
        console.error('Potential API related error detected:', e.message);
        alert('API Error detected: ' + e.message);
    }
});

// Monitor network requests (if possible)
if (window.fetch) {
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        if (args[0] && args[0].includes && args[0].includes('tiny.cloud')) {
            console.error('Blocked cloud request:', args[0]);
            alert('Blocked cloud request to: ' + args[0]);
            return Promise.reject(new Error('Cloud requests are disabled'));
        }
        return originalFetch.apply(this, args);
    };
}
</script>

<?php include"inc/footer.php"; ?>
