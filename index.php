<?php
    $servername = "localhost";
    $username = "msi";
    $password = "123456";
    $dbname = "sensor";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    //sql cua temp va humi
    $sql = "SELECT temperature, humidity FROM dht22_readings ORDER BY timestamp DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $temperature = $row["temperature"];
        $humidity = $row["humidity"];
    } else {
        $temperature = "N/A";
        $humidity = "N/A";
    }

    $conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Lamp - Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background-image: url("https://i.pinimg.com/564x/23/20/b3/2320b3e6fb349c0a85acfbbe2efd159b.jpg");
            font: 14px sans-serif;
            margin-top: 40px;
            margin-left: 10px;
            margin-right: 10px;
        }

        .wrapper {
            float: left;
            width: 30%;
            padding: 20px;
            margin: 20px;
            height: 420px;
            background-color: white;
        }

        .box {
            margin: auto;
            width: 60%;
            height: 50%;
        }

        .table {
            width: 70%;
            margin: auto;
        }
    
        .header {
            text-align: center;
            color: #6a1d1d;
            margin-bottom: 15px;
            font-size:45px;
        }

        .control input[type="text"] {
            margin-left: 20px;
            background-color: transparent;
            border: none;
            color: #6a1d1d;
            font-size: 30px;
            width: 50px;
            text-align: center;
        }

        .control {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        .bt {
            width: 100px;
            height: 50px;
        }

        .mb {
            text-align: center;
        }

        .auto {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>
    <h1 class="header" id="runlcd">SMART LAMP</h1>
    <!-- <div class = "mb-3 auto">
        <button type="button" class="bt btn btn-primary" id="auto">Auto</button>
    </div> -->
    <div class="control">
        <label for="form-label" style="font-size: 25px;">
            Temperature: <span id="tempValue"><?php echo $temperature; ?></span> &deg;C&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Humidity: <span id="humiValue"><?php echo $humidity; ?></span> %
        </label>
    </div>
    
    <script>
        function updateValues() {
            fetch('index.php') 
                .then(response => response.json())
                .then(data => {
                    document.getElementById('tempValue').textContent = data.temperature;
                    document.getElementById('humiValue').textContent = data.humidity;
                });
        }
        updateValues();
        setInterval(updateValues, 5000); 
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
    
    <div class="control">
        <div class="wrapper border border-2 rounded border-primary">
            <h2 style="margin-top: 20px;">Temp - Humi Control</h2>
            <br>
            <div class="mb">
                <div class="mb-3">
                    <button type="button" style="margin-top: 40px;" class="bt btn btn-primary" id="ledOn">DHT22</button>
                </div>
                <div class="mb-3">
                    <button type="button" style="margin-top: 35px;" class="bt btn btn-primary" id="ledOff">LCD</button>
                </div>
            </div>
        </div>
    
        <div class="wrapper border border-2 rounded border-primary">
            <h2 style="margin-top: 20px;">RGB LED CONTROL</h2>
            <br>
            <div class="mb-3" action="addcolor.php" method="POST">
                <label class="form-label" style ="font-size:20px;">Color Adjustment</label>
                <br>
                R: <input type="range" class="form-range" name="r" min="0" max="255" id="red" value="0">
                <br>
                G: <input type="range" class="form-range" name="g" min="0" max="255" id="green" value="0">
                <br>
                B: <input type="range" class="form-range" name="b" min="0" max="255" id="blue" value="0">
            </div>
            <div class="mb-3">
                <button type="button" class="bt btn btn-primary" id="apply">Apply</button>
            </div>
        </div>
    
        <div class="wrapper border border-2 rounded border-primary">
            <h3>CURRENT LED COLOR</h3>
            <br>
            <div class="box border border-info" id="colorBox" style="background-color: rgb(0, 0, 0);"></div>
            <br>
            <h5>Current LED values:</h5>
            <table class="table table_size" id="colorValuesTable">
                <thead>
                    <tr>
                        <th scope="col">Red</th>
                        <th scope="col">Green</th>
                        <th scope="col">Blue</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td id="redValue">0</td>
                        <td id="greenValue">0</td>
                        <td id="blueValue">0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script type="text/javascript">
        const redSlider = document.getElementById('red');
        const greenSlider = document.getElementById('green'); 
        const blueSlider = document.getElementById('blue');
        const colorBox = document.getElementById('colorBox');
        const redValue = document.getElementById('redValue');
        const greenValue = document.getElementById('greenValue');
        const blueValue = document.getElementById('blueValue');

        function updateColor() {
            const r = redSlider.value;
            const g = greenSlider.value;
            const b = blueSlider.value;

            colorBox.style.backgroundColor = `rgb(${r}, ${g}, ${b})`;

            redValue.textContent = r;
            greenValue.textContent = g;
            blueValue.textContent = b;
        }

        redSlider.addEventListener('input', updateColor);
        greenSlider.addEventListener('input', updateColor);
        blueSlider.addEventListener('input', updateColor);

        const ledOnButton = document.getElementById('ledOn');
        const ledOffButton = document.getElementById('ledOff');
        const autoButton = document.getElementById('auto');
        const applyBtn = document.getElementById('apply');
        applyBtn.addEventListener('click', () => {
            const r = redSlider.value;
            const g = greenSlider.value;
            const b = blueSlider.value;

            fetch('addcolor.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `r=${r}&g=${g}&b=${b}`
            })
            .then(response => response.text()) 
            .then(data => {
                console.log(data); 
            })
            .catch(error => {
                console.errsor('Error:', error);
            });
            fetch('led_control.php?action=apply'); 
        });

        ledOffButton.addEventListener('click', () => {
            fetch('led_control.php?action=auto'); 
        });0

        ledOnButton.addEventListener('click', () => {
            fetch('led_control.php?action=on'); 
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>