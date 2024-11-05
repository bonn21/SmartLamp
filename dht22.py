import time
import adafruit_dht
import board
import pymysql.cursors

dht_device = adafruit_dht.DHT22(board.D4)

while True:
    try:
        temperature_c = dht_device.temperature
        temperature_f = temperature_c * (9 / 5) + 32
        humidity = dht_device.humidity

        print(
            "Temp:{:.1f} C / {:.1f} F  Humidity: {}%".format(
                temperature_c, temperature_f, humidity
            )
        )
        
        try:
            connection = pymysql.connect(
                host="localhost",
                user="msi",
                password="123456",
                db="sensor",
                charset="utf8mb4",
                cursorclass=pymysql.cursors.DictCursor,
            )

            with connection.cursor() as cursor:
                sql = "INSERT INTO `dht22_readings` (`temperature`, `humidity`) VALUES (%s, %s)"
                cursor.execute(sql, (temperature_c, humidity))  

            connection.commit()
        except pymysql.MySQLError as e:
            print(f"MySQL Error: {e}")
        finally:
            if connection:
                connection.close()

    except RuntimeError as err:
        print(err.args[0])

    time.sleep(10.0)
