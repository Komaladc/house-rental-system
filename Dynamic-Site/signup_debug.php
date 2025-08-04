<?php include"inc/header.php"; ?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .form-container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
    .form-group { margin: 15px 0; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    select, input { padding: 8px; width: 100%; font-size: 16px; border: 1px solid #ccc; border-radius: 4px; }
    #document-section { 
        background: #fffacd; 
        border: 3px solid #ff0000; 
        padding: 20px; 
        margin: 20px 0; 
        display: none; 
        border-radius: 8px;
    }
    .debug { background: #e7f3ff; padding: 15px; margin: 10px 0; border-radius: 5px; border-left: 4px solid #007bff; }
</style>

<div class="form-container">
    <h1>🏠 Registration Debug Page</h1>
    
    <div class="debug" id="debug">
        <strong>Debug Log:</strong>
        <div id="debug-log">Page loading...</div>
    </div>
    
    <form>
        <div class="form-group">
            <label for="level">Account Type *</label>
            <select name="level" id="level" required>
                <option value="">Select Account Type</option>
                <option value="1">🏠 House Seeker (Browse & Book Properties)</option>
                <option value="2">🏘️ Property Owner (List Your Properties)</option>
                <option value="3">🏢 Real Estate Agent (Manage Multiple Properties)</option>
            </select>
        </div>
        
        <div id="document-section">
            <h3 style="color: #dc3545; margin-bottom: 15px;">📋 SUCCESS! Document Section is Visible!</h3>
            <p><strong>This section appeared when you selected Property Owner or Agent!</strong></p>
            <input type="text" placeholder="Citizenship ID" style="margin: 10px 0;">
            <input type="file" accept="image/*,.pdf" style="margin: 10px 0;">
        </div>
    </form>
</div>

<script>
function debug(message) {
    const debugLog = document.getElementById('debug-log');
    debugLog.innerHTML += '<br>' + new Date().toLocaleTimeString() + ': ' + message;
    console.log('[DEBUG] ' + message);
}

document.addEventListener('DOMContentLoaded', function() {
    debug('DOM Content Loaded');
    
    const levelSelect = document.getElementById('level');
    const documentSection = document.getElementById('document-section');
    
    debug('Level select found: ' + (levelSelect ? 'YES' : 'NO'));
    debug('Document section found: ' + (documentSection ? 'YES' : 'NO'));
    
    function updateDocumentSection() {
        const selectedValue = levelSelect ? levelSelect.value : '';
        debug('Selected: "' + selectedValue + '"');
        
        if (selectedValue === '2' || selectedValue === '3') {
            debug('*** SHOWING DOCUMENT SECTION ***');
            if (documentSection) {
                documentSection.style.display = 'block';
                documentSection.style.visibility = 'visible';
            }
        } else {
            debug('*** HIDING DOCUMENT SECTION ***');
            if (documentSection) {
                documentSection.style.display = 'none';
            }
        }
    }
    
    if (levelSelect) {
        levelSelect.addEventListener('change', function() {
            debug('*** DROPDOWN CHANGED ***');
            updateDocumentSection();
        });
    }
    
    updateDocumentSection();
    debug('Setup complete');
});
</script>

<?php include"inc/footer.php"; ?>
