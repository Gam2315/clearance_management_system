<!DOCTYPE html>
<html>
<head>
    <title>Simple API Test</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        button { padding: 10px 20px; margin: 5px; }
        pre { background: #f8f9fa; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🔍 Simple API Test Page</h1>
    
    <div class="test-section">
        <h3>System Info</h3>
        <p><strong>User:</strong> {{ auth()->user()->name ?? 'Not logged in' }} ({{ auth()->user()->role ?? 'No role' }})</p>
        <p><strong>CSRF Token:</strong> {{ csrf_token() }}</p>
        <p><strong>Current URL:</strong> {{ url()->current() }}</p>
    </div>

    <div class="test-section">
        <h3>Quick Tests</h3>
        <button onclick="testSimple()">Test Simple Route</button>
        <button onclick="testReports()">Test Reports API</button>
        <button onclick="clearResults()">Clear Results</button>
    </div>

    <div id="results"></div>

    <script>
        function log(message, type = 'info') {
            const results = document.getElementById('results');
            const div = document.createElement('div');
            div.className = 'test-section ' + (type === 'error' ? 'error' : 'success');
            div.innerHTML = '<pre>' + JSON.stringify(message, null, 2) + '</pre>';
            results.appendChild(div);
            console.log(message);
        }

        function clearResults() {
            document.getElementById('results').innerHTML = '';
        }

        async function testSimple() {
            log('=== Testing Simple Route ===');
            try {
                const response = await fetch('/admin/debug/test-simple', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({test: 'simple'})
                });

                log('Simple Route Status: ' + response.status);
                const text = await response.text();
                log('Simple Route Response: ' + text);
                
                if (response.ok) {
                    const data = JSON.parse(text);
                    log('✅ Simple route works!', 'success');
                } else {
                    log('❌ Simple route failed', 'error');
                }
            } catch (error) {
                log('❌ Simple route error: ' + error.message, 'error');
            }
        }

        async function testReports() {
            log('=== Testing Reports API ===');
            try {
                const response = await fetch('{{ route("admin.reports.students-not-cleared") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        academic_id: 1,
                        department_id: null,
                        semester: null
                    })
                });

                log('Reports API Status: ' + response.status);
                const text = await response.text();
                log('Reports API Response: ' + text);
                
                if (response.ok) {
                    const data = JSON.parse(text);
                    log('✅ Reports API works! Found ' + (data.students ? data.students.length : 0) + ' students', 'success');
                } else {
                    log('❌ Reports API failed', 'error');
                }
            } catch (error) {
                log('❌ Reports API error: ' + error.message, 'error');
            }
        }

        // Auto-run tests on page load
        window.onload = function() {
            log('Page loaded. Ready for testing.');
        };
    </script>
</body>
</html>
