<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance QR Code Generator</title>
    <link rel="stylesheet" href="qrc.css">
</head>
<body>
    <div class="container">
        <h1>QR Code Generator</h1>
        <form id="attendanceForm">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" placeholder="Enter the subject">
            <label for="timein">Time In:</label>
            <input type="text" id="timein" name="timein" placeholder="Enter time in (HH:MM AM/PM)">
            <label for="timeout">Time Out:</label>
            <input type="text" id="timeout" name="timeout" placeholder="Enter time out (HH:MM AM/PM)">
            <label for="room">Room:</label>
            <input type="text" id="room" name="room" placeholder="Enter your room">
            <br><br>
            <button type="button" onclick="generateQR()">Generate QR Code</button>
            <br><br>
            <div id="qrCode"></div>
            <br><br>
        </form>
    </div>
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script>
        function generateQR() {
            var role = "teacher"; // Teacher role
            var name = document.getElementById('name').value;
            var subject = document.getElementById('subject').value;
            var timein = document.getElementById('timein').value;
            var timeout = document.getElementById('timeout').value;
            var room = document.getElementById('room').value;

            // Check if all required fields are filled
            if (name && subject && timein && timeout && room) {

                // Generate the QR code
                var qrCodeData = `Role: ${role}, Name: ${name}, Subject: ${subject}, Time In: ${timein}, Time Out: ${timeout}, Room: ${room}`;
                
                var qrCodeElement = document.getElementById('qrCode');
                qrCodeElement.innerHTML = '';
                new QRCode(qrCodeElement, {
                    text: qrCodeData,
                    width: 128,
                    height: 128,
                    colorDark: '#000000',
                    colorLight: '#fff',
                    correctLevel: QRCode.CorrectLevel.H // High correction level
                });

                // Send data to scan.php
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "scan.php", true);
                xhr.setRequestHeader("Content-Type", "application/json");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log(xhr.responseText);
                    }
                };
                xhr.send(JSON.stringify({
                    role: role,
                    name: name,
                    subject: subject,
                    timein: timein,
                    timeout: timeout,
                    room: room
                }));

                // Hide the form fields after generating QR code
                document.getElementById('attendanceForm').reset();
                document.getElementById('qrCode').style.display = 'block';
            } else {
                alert('Please fill all the required fields.');
            }
        }
    </script>
</body>
</html>
