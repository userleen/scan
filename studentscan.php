<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>QR Code Scanner</h1>
    <div id="qrScanner">
        <div id="qrResult"></div><center>
        <video id="qrVideo" width="500px" playsinline autoplay></video></center>
        <center>
        <table>
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Room</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Year Level/Section</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody id="tableBody"></tbody>
        </table></center>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script>
        let timeIn = null;

        // Request camera access
        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function (stream) {
                document.querySelector("#qrVideo").srcObject = stream;
                startScanner();
            })
            .catch(function (err) {
                console.log("Error accessing the camera: " + err);
                alert("Error accessing the camera: " + err);
            });

        function startScanner() {
            const video = document.getElementById('qrVideo');
            const canvasElement = document.createElement('canvas');
            const canvas = canvasElement.getContext('2d');
            const qrResult = document.getElementById('qrResult');
            const tableBody = document.getElementById('tableBody');

            // Fix video size
            video.addEventListener('loadedmetadata', () => {
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
            });

            // Scan the QR code
            const scanQrCode = () => {
                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (code) {
                    const data = parseQRCodeData(code.data);
                    if (!timeIn) {
                        timeIn = new Date().toLocaleString();
                        data.timeIn = timeIn;
                    } else {
                        data.timeIn = timeIn;
                        data.timeOut = new Date().toLocaleString();
                        timeIn = null;
                    }
                    displayDataInTable(data);
                    sendDataToServer(data);
                } else {
                    qrResult.textContent = 'No QR Code detected';
                }

                requestAnimationFrame(scanQrCode);
            };

            // Start scanning
            scanQrCode();

            function parseQRCodeData(qrCodeMessage) {
                const parts = qrCodeMessage.split(',');
                const data = {};
                parts.forEach(part => {
                    const [key, value] = part.split(':');
                    if (key.trim() !== "") {
                        let parsedKey = key.trim().replace(/"/g, ""); // Remove quotes if present
                        const keyLowerCase = parsedKey.toLowerCase();
                        if (keyLowerCase === 'name' || keyLowerCase === 'subject' || keyLowerCase === 'room' || keyLowerCase === 'student id' || keyLowerCase === 'year level/section' || keyLowerCase === 'remarks') {
                            data[keyLowerCase.replace(/\s+/g, '_')] = value.trim(); // Replace spaces with underscores
                        }
                    }
                });
                return data;
            }

            function displayDataInTable(data) {
                qrResult.textContent = 'QR Code detected';
                const newRow = document.createElement('tr');
                newRow.innerHTML = `<td>${data.student_id || ''}</td><td>${data.name || ''}</td><td>${data.subject || ''}</td><td>${data.room || ''}</td><td>${data.timeIn || ''}</td><td>${data.timeOut || ''}</td><td>${data.year_level_section || ''}</td><td>${data.remarks || ''}</td>`;
                tableBody.appendChild(newRow);
            }

            function sendDataToServer(data) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'dc.php', true);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        console.log(xhr.responseText);
                    }
                };
                const params = `student_id=${data.student_id || ''}&name=${data.name || ''}&subject=${data.subject || ''}&room=${data.room || ''}&timein=${data.timeIn || ''}&timeout=${data.timeOut || ''}&year_level_section=${data.year_level_section || ''}&remarks=${data.remarks || ''}`;
                xhr.send(params);
            }
        }
    </script>
</body>
</html>
