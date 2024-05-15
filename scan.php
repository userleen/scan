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
                    <th>Name</th>
                    <th>Subject</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Room</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table></center>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jsqr/dist/jsQR.min.js"></script>
    <script>
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
            const tableBody = document.querySelector("table tbody");

            // Fix video size
            video.addEventListener('loadedmetadata', () => {
                canvasElement.width = video.videoWidth;
                canvasElement.height = video.videoHeight;
            });

            let firstScanData = null;

            // Scan the QR code
            const scanQrCode = () => {
                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                const imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: 'dontInvert',
                });

                if (code) {
                    const data = parseQRCodeData(code.data);
                    if (!firstScanData) {
                        firstScanData = data;
                        firstScanData.timein = new Date().toLocaleString();
                    } else {
                        firstScanData.timeout = new Date().toLocaleString();
                        displayDataInTable(firstScanData);
                        sendDataToServer(firstScanData); // Send data to server
                        firstScanData = null;
                    }
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
                        data[keyLowerCase] = keyLowerCase.includes('time') ? new Date(value.trim()).toLocaleString() : value.trim();
                    }
                });
                return data;
            }

            function displayDataInTable(data) {
                qrResult.textContent = 'QR Code detected';
                const newRow = document.createElement('tr');
                newRow.innerHTML = `<td>${data.name || ''}</td><td>${data.subject || ''}</td><td>${data.timein || ''}</td><td>${data.timeout || ''}</td><td>${data.room || ''}</td>`;
                tableBody.appendChild(newRow);
            }

            function sendDataToServer(data) {
                const xhr = new XMLHttpRequest();
                const url = "dc.php"; // Change this to your PHP script URL
                const params = `name=${data.name}&subject=${data.subject}&timein=${data.timein}&timeout=${data.timeout}&room=${data.room}`;
                xhr.open("POST", url, true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.send(params);
            }
        }
    </script>
</body>
</html>