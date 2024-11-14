<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request and Exchange Page</title>
    <style>
        /* CSS styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #007bff;
            font-weight: bold;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #007bff;
        }

        form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #555;
        }

        select, input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s;
        }

        select:focus, input:focus {
            border-color: #007bff;
            outline: none;
        }

        .btn,.dash_button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 15px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            padding: 5px 15px;
            color: white;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 5px 15px;
            color: white;
        }

        /* Additional styles for action buttons in the table */
        .action-btns {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Request Car/Driver Exchange **THIS PAGE IS A WORK IN PROGRESS**</h2>

    <!-- Request Form -->
    <form id="request-form">
        <div class="form-group">
            <label for="request-type">Request Type</label>
            <select id="request-type" name="request_type">
                <option value="car">Car</option>
                <option value="driver">Driver</option>
            </select>
        </div>

        <div class="form-group">
            <label for="company-id">Requested Company ID</label>
            <input type="number" id="company-id" name="requested_company_id" required>
        </div>

        <div class="form-group" id="car-input">
            <label for="car-id">Car ID</label>
            <input type="number" id="car-id" name="car_id">
        </div>

        <div class="form-group" id="driver-input" style="display:none;">
            <label for="driver-username">Driver Username</label>
            <input type="text" id="driver-username" name="driver_username">
        </div>

        <button type="submit" class="btn">Submit Request</button>
    </form>

    <!-- Incoming Requests Table -->
    <h2>Incoming Requests</h2>
    <table>
        <thead>
        <tr>
            <th>Request ID</th>
            <th>Type</th>
            <th>Requesting Company ID</th>
            <th>Requested Item</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody id="incoming-requests">
        <!-- Incoming requests will be populated here -->
        </tbody>
    </table>

    <!-- Outgoing Requests Table -->
    <h2>Outgoing Requests</h2>
    <table>
        <thead>
        <tr>
            <th>Request ID</th>
            <th>Type</th>
            <th>Requested Company ID</th>
            <th>Requested Item</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody id="outgoing-requests">
        <!-- Outgoing requests will be populated here -->
        </tbody>
    </table>
    <button onclick="window.location.href='c_dash.php'" class="dash_button">back to dash</button>
</div>

<script>
    // Toggle between car/driver input based on request type selection
    document.getElementById('request-type').addEventListener('change', function () {
        const requestType = this.value;
        document.getElementById('car-input').style.display = requestType === 'car' ? 'block' : 'none';
        document.getElementById('driver-input').style.display = requestType === 'driver' ? 'block' : 'none';
    });

    // Submit a request
    document.getElementById('request-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const requestType = document.getElementById('request-type').value;
        const companyID = 1;  // Replace with your current company ID
        const requestedCompanyID = document.getElementById('company-id').value;
        const requestItem = requestType === 'car' ? document.getElementById('car-id').value : document.getElementById('driver-username').value;

        const data = {
            request_type: requestType,
            company_id: companyID,
            requested_company_id: requestedCompanyID
        };
        if (requestType === 'car') {
            data.car_id = requestItem;
        } else {
            data.driver_username = requestItem;
        }

        fetch('submit_request.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams(data)
        }).then(response => response.text())
            .then(data => alert(data));
    });

    // Fetch requests and display them
    const companyID = 1;  // Replace with your current company ID
    fetch(`fetch_requests.php?company_id=${companyID}`)
        .then(response => response.json())
        .then(data => {
            const incomingRequestsTable = document.getElementById('incoming-requests');
            const outgoingRequestsTable = document.getElementById('outgoing-requests');

            // Populate incoming requests (car and driver)
            data.incomingCarRequests.forEach(request => {
                const row = `<tr>
                                <td>${request.requestID}</td>
                                <td>Car</td>
                                <td>${request.requestingCompanyID}</td>
                                <td>${request.carID}</td>
                                <td>${request.status}</td>
                                <td class="action-btns">
                                    <button class="btn btn-success" onclick="processRequest(${request.requestID}, 'car', 'approve')">Approve</button>
                                    <button class="btn btn-danger" onclick="processRequest(${request.requestID}, 'car', 'reject')">Reject</button>
                                </td>
                             </tr>`;
                incomingRequestsTable.innerHTML += row;
            });

            data.incomingDriverRequests.forEach(request => {
                const row = `<tr>
                                <td>${request.requestID}</td>
                                <td>Driver</td>
                                <td>${request.requestingCompanyID}</td>
                                <td>${request.driverUsername}</td>
                                <td>${request.status}</td>
                                <td class="action-btns">
                                    <button class="btn btn-success" onclick="processRequest(${request.requestID}, 'driver', 'approve')">Approve</button>
                                    <button class="btn btn-danger" onclick="processRequest(${request.requestID}, 'driver', 'reject')">Reject</button>
                                </td>
                             </tr>`;
                incomingRequestsTable.innerHTML += row;
            });

            // Populate outgoing requests (car and driver)
            data.outgoingCarRequests.forEach(request => {
                const row = `<tr>
                                <td>${request.requestID}</td>
                                <td>Car</td>
                                <td>${request.requestedCompanyID}</td>
                                <td>${request.carID}</td>
                                <td>${request.status}</td>
                             </tr>`;
                outgoingRequestsTable.innerHTML += row;
            });

            data.outgoingDriverRequests.forEach(request => {
                const row = `<tr>
                                <td>${request.requestID}</td>
                                <td>Driver</td>
                                <td>${request.requestedCompanyID}</td>
                                <td>${request.driverUsername}</td>
                                <td>${request.status}</td>
                             </tr>`;
                outgoingRequestsTable.innerHTML += row;
            });
        });

    // Function to process a request (approve/reject)
    function processRequest(requestID, type, action) {
        const url = action === 'approve' ? 'approve_request.php' : 'reject_request.php';
        fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: new URLSearchParams({request_id: requestID, request_type: type})
        }).then(response => response.text())
          .then(data => {
              alert(data);
              // Reload the requests to reflect changes
              location.reload();
          });
    }
</script>
</body>
</html>
