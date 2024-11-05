#include <mariadb/mysql.h>
#include <stdio.h>
#include <stdlib.h>
#include <wiringPi.h>
#include <wiringPiI2C.h>

// Define some device parameters
#define I2C_ADDR 0x27  // I2C device address

// Define some device constants
#define LCD_CHR 9  // Mode - Sending data
#define LCD_CMD 8  // Mode - Sending command

#define LINE1 0x80  // 1st line
#define LINE2 0xC0  // 2nd line

#define LCD_BACKLIGHT 0x08  // On
// LCD_BACKLIGHT_OFF 0x00  // Off
#define BACKLIGHT_PIN 2

#define ENABLE 0b00000100  // Enable bit

void lcd_init(void);
void lcd_byte(int bits, int mode);
void lcd_toggle_enable(int bits);

void typeFloat(float myFloat);
void lcdLoc(int line);  // move cursor
void ClrLcd(void);      // clr LCD return home
void typeln(const char *s);
int fd;  // seen by all subroutines

int main() {
    MYSQL *conn;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char *server = "localhost";
    char *user = "msi";
    char *password = "123456";
    char *database = "sensor";

    //khoi tao ket noi den sql
    conn = mysql_init(NULL);
    if (!mysql_real_connect(conn, server, user, password, database, 0, NULL, 0)) {
        fprintf(stderr, "%s\n", mysql_error(conn));
        exit(1);
    }

    //khoi tao wiringpi va lcd
    if (wiringPiSetup() == -1) exit(1);
    fd = wiringPiI2CSetup(I2C_ADDR);
    lcd_init();

    
    while (1) {
        // truy van sql de lay du lieu moi nhat
        if (mysql_query(conn, "SELECT temperature, humidity FROM dht22_readings ORDER BY timestamp DESC LIMIT 1")) {
            fprintf(stderr, "%s\n", mysql_error(conn));
            continue;
        }

        res = mysql_store_result(conn);
        if (res == NULL) {
            fprintf(stderr, "%s\n", mysql_error(conn));
            continue;
        }

        row = mysql_fetch_row(res);
        if (row == NULL) {
            fprintf(stderr, "No data found\n");
            continue;
        }

        //lay gia tri temp va humi tu ket qua truy van
        float temp = atof(row[0]);
        float humi = atof(row[1]);

        //hien thi lcd
        lcdLoc(LINE1);
        typeln("Temp: ");
        typeFloat(temp);
        typeln("*C");

        lcdLoc(LINE2);
        typeln("Humi: ");
        typeFloat(humi);
        typeln("%");
        delay(2000);

        ClrLcd();

        mysql_free_result(res); // giai phong bo nho

        //digitalWrite(BACKLIGHT_PIN, LOW);  // turn off backlight
    } 
    mysql_close(conn); // dong ket noi

    
    return 0;
}

// float to string
void typeFloat(float myFloat) {
    char buffer[20];
    sprintf(buffer, "%.1f", myFloat);
    typeln(buffer);
}

// clr lcd go home loc 0x80
void ClrLcd(void) {
    lcd_byte(0x01, LCD_CMD);
    lcd_byte(0x02, LCD_CMD);
}

// go to location on LCD
void lcdLoc(int line) {
    lcd_byte(line, LCD_CMD);
}

// this allows use of any size string
void typeln(const char *s) {
    while (*s) lcd_byte(*(s++), LCD_CHR);
}

void lcd_byte(int bits, int mode) {
    // Send byte to data pins
    //  bits = the data
    //  mode = 1 for data, 0 for command
    int bits_high;
    int bits_low;
    // uses the two half byte writes to LCD
    bits_high = mode | (bits & 0xF0) | LCD_BACKLIGHT;
    bits_low = mode | ((bits << 4) & 0xF0) | LCD_BACKLIGHT;

    // High bits
    wiringPiI2CReadReg8(fd, bits_high);
    lcd_toggle_enable(bits_high);

    // Low bits
    wiringPiI2CReadReg8(fd, bits_low);
    lcd_toggle_enable(bits_low);
}

void lcd_toggle_enable(int bits) {
    // Toggle enable pin on LCD display
    delayMicroseconds(500);
    wiringPiI2CReadReg8(fd, (bits | ENABLE));
    delayMicroseconds(500);
    wiringPiI2CReadReg8(fd, (bits & ~ENABLE));
    delayMicroseconds(500);
}

void lcd_init() {
    // Initialise display
    lcd_byte(0x33, LCD_CMD);  // Initialise
    lcd_byte(0x32, LCD_CMD);  // Initialise
    lcd_byte(0x06, LCD_CMD);  // Cursor move direction
    lcd_byte(0x0C, LCD_CMD);  // 0x0F On, Blink Off
    lcd_byte(0x28, LCD_CMD);  // Data length, number of lines, font size
    lcd_byte(0x01, LCD_CMD);  // Clear display
    delayMicroseconds(500);
}