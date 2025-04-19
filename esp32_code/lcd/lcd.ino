#include <TinyGPS++.h>
#include <HardwareSerial.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522v2.h>
#include <MFRC522DriverSPI.h>
#include <MFRC522DriverPinSimple.h>
#include <LiquidCrystal_I2C.h>

const char* ssid = "ARV";
const char* password = "11111111";

const char* serverURL = "http://34.204.229.70/easygo/api/rfid-auth.php";


HardwareSerial GPS(2);
TinyGPSPlus gps;

MFRC522DriverPinSimple ss_pin(5);
MFRC522DriverSPI driver{ss_pin};
MFRC522 mfrc522{driver};

#define LED_PIN 12


LiquidCrystal_I2C lcd(0x27, 16, 2);

bool lastWasTapIn = false;

void setup() {
  Serial.begin(115200);

 
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  lcd.init();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println(" Connected!");
  lcd.clear();
  lcd.print("WiFi Connected!");

  
  GPS.begin(9600, SERIAL_8N1, 16, 17);
  Serial.println("Waiting for GPS fix...");
  lcd.setCursor(0, 1);
  lcd.print("GPS Fix...");

  SPI.begin();
  mfrc522.PCD_Init();
  Serial.println("RFID Initialized");

  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, HIGH);
}

void loop() {

  while (GPS.available()) {
    gps.encode(GPS.read());
  }

  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) return;

   digitalWrite(LED_PIN, LOW);
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uid += (mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  
  String action = lastWasTapIn ? "tap_out" : "tap_in";
  lastWasTapIn = !lastWasTapIn;

  float lat = gps.location.isValid() ? gps.location.lat() : 0.0;
  float lng = gps.location.isValid() ? gps.location.lng() : 0.0;

  Serial.println(action == "tap_in" ? "TAP IN..." : "TAP OUT...");
  Serial.println("UID: " + uid);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print(action == "tap_in" ? "TAP IN..." : "TAP OUT...");
  lcd.setCursor(0, 1);
  lcd.print("UID: ");
  lcd.print(uid.substring(0, 10));
  delay(1000);
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverURL);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "rfid_uid=" + uid + "&action=" + action + "&lat=" + String(lat, 6) + "&lng=" + String(lng, 6);
    int httpCode = http.POST(postData);
    String response = http.getString();

    Serial.println("Server response: " + response);

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("Response:");
    lcd.setCursor(0, 1);
    lcd.print(response.substring(0, 16));
    delay(1500);
    lcd.clear();
  } else {
    Serial.println("WiFi not connected");

    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi not connected");
  }

  

  digitalWrite(LED_PIN, HIGH);
  mfrc522.PICC_HaltA();
  delay(1000);
}
