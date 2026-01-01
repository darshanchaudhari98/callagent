<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trade Diary - Calling Agent</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; }
        h1 { color: #333; margin-bottom: 20px; text-align: center; }
        .card { background: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px; font-size: 16px; }
        button { width: 100%; padding: 14px; background: #4CAF50; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; }
        button:hover { background: #45a049; }
        button:disabled { background: #ccc; cursor: not-allowed; }
        .status { margin-top: 20px; padding: 15px; border-radius: 4px; display: none; }
        .status.success { display: block; background: #d4edda; color: #155724; }
        .status.error { display: block; background: #f8d7da; color: #721c24; }
        .status.loading { display: block; background: #fff3cd; color: #856404; }
        .call-log { margin-top: 20px; }
        .call-log h3 { margin-bottom: 10px; }
        .log-item { padding: 10px; background: #f9f9f9; margin-bottom: 8px; border-radius: 4px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📞 Trade Diary Calling Agent</h1>
        
        <div class="card">
            <form id="callForm">
                <label>Phone Number</label>
                <input type="tel" id="phone" placeholder="+919876543210" required>
                
                <label>Lead Name (optional)</label>
                <input type="text" id="name" placeholder="John Doe">
                
                <button type="submit" id="callBtn">Make Call</button>
            </form>
            
            <div id="status" class="status"></div>
        </div>
        
        <div class="call-log card" style="margin-top: 20px;">
            <h3>Recent Calls</h3>
            <div id="callLogs">No calls yet</div>
        </div>
    </div>

    <script>
        const form = document.getElementById('callForm');
        const status = document.getElementById('status');
        const callBtn = document.getElementById('callBtn');
        const callLogs = document.getElementById('callLogs');
        let logs = [];

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const phone = document.getElementById('phone').value;
            const name = document.getElementById('name').value;
            
            callBtn.disabled = true;
            callBtn.textContent = 'Calling...';
            status.className = 'status loading';
            status.textContent = 'Initiating call...';
            
            try {
                const res = await fetch('/calling-agent/initiate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ phone_number: phone, lead_name: name })
                });
                
                const data = await res.json();
                
                if (data.success) {
                    status.className = 'status success';
                    status.textContent = '✓ Call initiated! SID: ' + (data.call_sid || 'N/A');
                    addLog(phone, name, 'initiated');
                } else {
                    status.className = 'status error';
                    status.textContent = '✗ ' + data.message;
                }
            } catch (err) {
                status.className = 'status error';
                status.textContent = '✗ Error: ' + err.message;
            }
            
            callBtn.disabled = false;
            callBtn.textContent = 'Make Call';
        });

        function addLog(phone, name, stat) {
            logs.unshift({ phone, name: name || 'Unknown', status: stat, time: new Date().toLocaleTimeString() });
            if (logs.length > 5) logs.pop();
            renderLogs();
        }

        function renderLogs() {
            callLogs.innerHTML = logs.map(l => 
                `<div class="log-item"><strong>${l.name}</strong> (${l.phone}) - ${l.status} at ${l.time}</div>`
            ).join('');
        }
    </script>
</body>
</html>
