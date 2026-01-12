<!DOCTYPE html>
<html>
<head>
    <title>Test RFID Detection</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .warning { background-color: #fff3cd; border-color: #ffeaa7; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>RFID Detection Test</h1>
    
    <button onclick="testDetection()">Test Detection Endpoint</button>
    <button onclick="updateTimestamp()">Update Timestamp</button>
    <button onclick="clearResults()">Clear Results</button>
    
    <div id="results"></div>

    <script>
        function testDetection() {
            fetch('/debug-detection', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                displayResult('Detection Result', data, 'success');
            })
            .catch(error => {
                displayResult('Detection Error', {error: error.message}, 'error');
            });
        }

        function updateTimestamp() {
            fetch('/nfc/store-uid', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({uid: '1B8CE14E'})
            })
            .then(response => response.json())
            .then(data => {
                displayResult('Timestamp Update', data, 'success');
                // After updating timestamp, test detection again
                setTimeout(testDetection, 1000);
            })
            .catch(error => {
                displayResult('Update Error', {error: error.message}, 'error');
            });
        }

        function displayResult(title, data, type) {
            const resultsDiv = document.getElementById('results');
            const resultDiv = document.createElement('div');
            resultDiv.className = `result ${type}`;
            resultDiv.innerHTML = `
                <h3>${title}</h3>
                <pre>${JSON.stringify(data, null, 2)}</pre>
                <small>Time: ${new Date().toLocaleString()}</small>
            `;
            resultsDiv.appendChild(resultDiv);
        }

        function clearResults() {
            document.getElementById('results').innerHTML = '';
        }

        // Auto-test on page load
        window.onload = function() {
            updateTimestamp();
        };
    </script>
</body>
</html>
