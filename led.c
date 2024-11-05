#include <mariadb/mysql.h>
#include <stdio.h>
#include <stdlib.h>
#include <wiringPi.h>
#include <softPwm.h>

float data;
int main(void) {
    MYSQL *conn;
    MYSQL_RES *res;
    MYSQL_ROW row;
    char *server = "localhost";
    char *user = "msi";
    char *password = "123456";
    char *database = "sensor";

    // khoi tao ket noi den sql
    conn = mysql_init(NULL);
    if (!mysql_real_connect(conn, server, user, password, database, 0, NULL, 0)) {
        fprintf(stderr, "%s\n", mysql_error(conn));
        exit(1);
    }

    // khoi tao wiringpi va lcd
    if (wiringPiSetup() == -1) exit(1);

    wiringPiSetup();

    pinMode(0, OUTPUT); //blue
    pinMode(2, OUTPUT); //red
    pinMode(3, OUTPUT); //green

    softPwmCreate(0, 0, 100);
    softPwmCreate(2, 0, 100);
    softPwmCreate(3, 0, 100);

    while(1){
        if (mysql_query(conn, "SELECT red, green, blue from led_rgb ORDER BY timestamp DESC LIMIT 1")) {
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

        // lay gia tri temp va humi tu ket qua truy van
        int red = atof(row[0]);
        int green = atof(row[1]);
        int blue = atof(row[2]);

        red = red * 100 / 255;
        green = green * 100 / 255;
        blue = blue * 100 / 255;

        softPwmWrite(0, blue);
        softPwmWrite(2, green);
        softPwmWrite(3, red);

        mysql_free_result(res);
    }
    mysql_close(conn);
    return 0;
}