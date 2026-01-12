<!DOCTYPE html>
<html>
<head>
    <title>Debug Clearance Detection</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; }
        .warning { background-color: #fff3cd; }
        .error { background-color: #f8d7da; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Debug Clearance Detection</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="debug-section">
                    <h3>Test Controls</h3>
                    <button class="btn btn-primary" onclick="updateTimestamp()">1. Update Timestamp</button>
                    <button class="btn btn-success" onclick="testDetection()">2. Test Detection</button>
                    <button class="btn btn-warning" onclick="testStudentCard()">3. Test Student Card</button>
                    <button class="btn btn-danger" onclick="clearResults()">Clear Results</button>
                </div>
                
                <div class="debug-section">
                    <h3>Expected vs Actual</h3>
                    <div id="comparison"></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="debug-section">
                    <h3>Detection Results</h3>
                    <div id="detection-results"></div>
                </div>
                
                <div class="debug-section">
                    <h3>Student Card Preview</h3>
                    <div id="student-card-preview"></div>
                </div>
            </div>
        </div>
        
        <div class="debug-section">
            <h3>Console Logs</h3>
            <div id="console-logs"></div>
        </div>
    </div>

    <script>
        let detectionData = null;
        
        function log(message, type = 'info') {
            const logsDiv = document.getElementById('console-logs');
            const logEntry = document.createElement('div');
            logEntry.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} py-1 mb-1`;
            logEntry.innerHTML = `<small>${new Date().toLocaleTimeString()}: ${message}</small>`;
            logsDiv.appendChild(logEntry);
            logsDiv.scrollTop = logsDiv.scrollHeight;
        }

        function updateTimestamp() {
            log('Updating timestamp...');
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
                log('Timestamp updated successfully', 'success');
                document.getElementById('detection-results').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
            })
            .catch(error => {
                log('Error updating timestamp: ' + error.message, 'error');
            });
        }

        function testDetection() {
            log('Testing detection endpoint...');
            fetch('/debug-detection', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                detectionData = data;
                log(`Detection result: ${data.status}`, data.found ? 'success' : 'warning');
                document.getElementById('detection-results').innerHTML = `<pre>${JSON.stringify(data, null, 2)}</pre>`;
                
                // Show comparison
                showComparison(data);
            })
            .catch(error => {
                log('Error in detection: ' + error.message, 'error');
            });
        }

        function testStudentCard() {
            if (!detectionData) {
                log('No detection data available. Run detection test first.', 'error');
                return;
            }
            
            if (detectionData.status !== 'found') {
                log('Student not found. Cannot generate card.', 'error');
                return;
            }
            
            log('Generating student card...');
            try {
                const cardHtml = generateStudentCard(detectionData);
                document.getElementById('student-card-preview').innerHTML = cardHtml;
                log('Student card generated successfully', 'success');
            } catch (error) {
                log('Error generating student card: ' + error.message, 'error');
            }
        }

        function showComparison(data) {
            const expected = {
                found: true,
                status: 'detected',
                student: {
                    name: 'Expected format',
                    student_number: '2024-01-0001',
                    department: 'Department name',
                    program: 'Program name',
                    year: '3rd'
                }
            };
            
            const comparisonHtml = `
                <h5>Expected Structure:</h5>
                <pre>${JSON.stringify(expected, null, 2)}</pre>
                <h5>Actual Structure:</h5>
                <pre>${JSON.stringify({
                    found: data.found,
                    status: data.status,
                    student: data.student || 'Not found'
                }, null, 2)}</pre>
                <h5>Status Check:</h5>
                <ul>
                    <li>Found: ${data.found ? '✅' : '❌'}</li>
                    <li>Status is 'detected': ${data.status === 'detected' ? '✅' : '❌ (' + data.status + ')'}</li>
                    <li>Has student data: ${data.student ? '✅' : '❌'}</li>
                </ul>
            `;
            
            document.getElementById('comparison').innerHTML = comparisonHtml;
        }

        function generateStudentCard(data) {
            const student = data.student;
            const clearance = data.clearance;
            const existingStatus = data.existing_status;
            const canClear = data.can_clear;

            let statusBadge = '';
            if (existingStatus) {
                const badgeClass = existingStatus.status === 'cleared' ? 'badge-success' : 'badge-warning';
                statusBadge = \`<span class="badge \${badgeClass}">\${existingStatus.status.toUpperCase()}</span>\`;
            } else {
                statusBadge = '<span class="badge badge-secondary">NO STATUS</span>';
            }

            let clearanceInfo = '';
            if (clearance && clearance.is_locked) {
                clearanceInfo = \`
                    <div class="alert alert-danger">
                        <i class="anticon anticon-lock"></i> <strong>Clearance Locked</strong>
                        <br>Reason: \${clearance.lock_reason}
                    </div>
                \`;
            }

            let accessInfo = '';
            if (!canClear) {
                accessInfo = \`
                    <div class="alert alert-warning">
                        <i class="anticon anticon-warning"></i> You cannot clear this student (different department)
                    </div>
                \`;
            }

            return \`
                <div class="card border-success">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h4 class="mb-2">👤 \${student.name}</h4>
                            \${statusBadge}
                        </div>

                        <div class="student-details">
                            <div class="row mb-2">
                                <div class="col-12">
                                    <strong>Student ID:</strong> \${student.student_number}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <strong>Department:</strong> \${student.department}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <strong>Program:</strong> \${student.program} | \${student.year} Year
                                </div>
                            </div>

                            \${student.has_violations ? '<div class="row mb-2"><div class="col-12"><span class="text-danger">⚠️ Has Violations</span></div></div>' : ''}
                            \${student.is_graduated ? '<div class="row mb-2"><div class="col-12"><span class="text-info">🏆 Graduated</span></div></div>' : ''}

                            \${existingStatus ? \`<div class="row mb-2"><div class="col-12"><small class="text-muted">Approved by: \${existingStatus.approved_by}<br>\${existingStatus.created_at}</small></div></div>\` : ''}
                            \${existingStatus && existingStatus.or_number ? \`<div class="row mb-2"><div class="col-12"><strong>OR Number:</strong> \${existingStatus.or_number}</div></div>\` : ''}
                        </div>

                        \${clearanceInfo}
                        \${accessInfo}
                    </div>
                </div>
            \`;
        }

        function clearResults() {
            document.getElementById('detection-results').innerHTML = '';
            document.getElementById('student-card-preview').innerHTML = '';
            document.getElementById('comparison').innerHTML = '';
            document.getElementById('console-logs').innerHTML = '';
            detectionData = null;
        }

        // Auto-run on page load
        window.onload = function() {
            log('Debug page loaded. Click "1. Update Timestamp" then "2. Test Detection"');
        };
    </script>
</body>
</html>
