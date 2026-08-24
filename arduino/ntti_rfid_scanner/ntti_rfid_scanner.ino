

#include <ArduinoJson.h>
#include <HTTPClient.h>
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <Preferences.h>  // Added to save IP permanently
#include <SPI.h>
#include <WiFi.h>
#include <WiFiManager.h>  // Added for WiFi Manager
#include <Wire.h>

// ---------------------------
// Network & API Configuration
// ---------------------------
String serverIP = "66.42.61.106";  // Vultr Live Server IP
String serverUrl = "";
String heartbeatUrl = "";
Preferences preferences;

const char *deviceId = "SCANNER_02";

unsigned long lastHeartbeat = 0;
const unsigned long heartbeatInterval =
  60000;  // Send heartbeat every 60 seconds

// ---------------------------
// Hardware Pins (ESP32)
// ---------------------------
#define RST_PIN 4
#define SS_PIN 5
#define LED_GREEN_PIN 32
#define LED_RED_PIN 33
#define BUZZER_PIN 25

// LCD Setup - We will initialize TWO objects to cover both possible addresses
LiquidCrystal_I2C lcd1(0x27, 16, 2);
LiquidCrystal_I2C lcd2(0x3F, 16, 2);

MFRC522 mfrc522(SS_PIN, RST_PIN);

// ---------------------------
// Helpers
// ---------------------------

String lcdPad(String s, int width) {
  s.trim();
  if ((int)s.length() > width)
    s = s.substring(0, width);
  while ((int)s.length() < width)
    s += " ";
  return s;
}

// This function prints to BOTH potential LCD addresses
void lcdPrint(String line1, String line2 = "") {
  String p1 = lcdPad(line1, 16);
  String p2 = lcdPad(line2, 16);

  Serial.println("LCD: " + line1 + " | " + line2);

  lcd1.setCursor(0, 0);
  lcd1.print(p1);
  lcd1.setCursor(0, 1);
  lcd1.print(p2);

  lcd2.setCursor(0, 0);
  lcd2.print(p1);
  lcd2.setCursor(0, 1);
  lcd2.print(p2);
}

void lcdIdle() {
  lcdPrint("NTTI Attendance", "Scan your card..");
}

void sendHeartbeat() {
  if (WiFi.status() != WL_CONNECTED)
    return;

  HTTPClient http;
  http.begin(heartbeatUrl);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(5000);

  StaticJsonDocument<128> doc;
  doc["device_id"] = deviceId;
  doc["ip"] = WiFi.localIP().toString();
  doc["rssi"] = WiFi.RSSI();

  String requestBody;
  serializeJson(doc, requestBody);

  int httpCode = http.POST(requestBody);
  if (httpCode > 0) {
    Serial.print("Heartbeat sent. IP: ");
    Serial.print(WiFi.localIP());
    Serial.print(" | RSSI: ");
    Serial.println(WiFi.RSSI());
  } else {
    Serial.print("Heartbeat failed: ");
    Serial.println(http.errorToString(httpCode).c_str());
  }
  http.end();
}

// ---------------------------
// setup()
// ---------------------------
void setup() {
  Serial.begin(115200);

  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(LED_GREEN_PIN, LOW);
  digitalWrite(LED_RED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  // Initialize both possible LCDs
  Wire.begin(21, 22);

  lcd1.init();
  lcd1.backlight();

  lcd2.init();
  lcd2.backlight();

  delay(500);
  lcdPrint("NTTI Attendance", "  Starting...   ");
  delay(1000);

  SPI.begin();
  mfrc522.PCD_Init();

  Serial.println(F("Ready to scan cards."));
  connectWiFi();

  // Send first heartbeat immediately
  sendHeartbeat();
  lastHeartbeat = millis();

  lcdIdle();
}

void loop() {
  // WiFi Maintenance
  if (WiFi.status() != WL_CONNECTED) {
    lcdPrint("WiFi Lost!", "Reconnecting...");
    connectWiFi();
    lcdIdle();
  }

  // Heartbeat Logic (Every 60 seconds)
  if (millis() - lastHeartbeat >= heartbeatInterval) {
    sendHeartbeat();
    lastHeartbeat = millis();
  }

  // RFID Scan Logic
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    uid += String(mfrc522.uid.uidByte[i] < 0x10 ? "0" : "");
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.println("Card Scanned! UID: " + uid);
  lcdPrint("Card Detected", uid);
  delay(1000);

  sendScanData(uid);

  // Update heartbeat timer after a scan to avoid double-sending
  lastHeartbeat = millis();

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  delay(4000);
  lcdIdle();
}

void connectWiFi() {
  Serial.println("Starting WiFiManager...");
  lcdPrint("Setup WiFi AP:", "Scanner_Setup");

  // Load saved Server IP from memory
  preferences.begin("scanner", false);
  serverIP = preferences.getString("serverIP", "66.42.61.106");
  preferences.end();

  WiFiManager wifiManager;

  // Add Custom Parameter for Server IP
  char ipBuffer[40];
  serverIP.toCharArray(ipBuffer, 40);
  WiFiManagerParameter custom_server_ip("server_ip", "Server Host/URL (e.g. ntti-attendance.onrender.com or 192.168.1.19)",
                                        ipBuffer, 60);
  wifiManager.addParameter(&custom_server_ip);

  // Try to connect to last saved WiFi. If it fails, create an Access Point
  // named "Scanner_Setup"
  bool connected = wifiManager.autoConnect("Scanner_Setup");

  if (connected) {
    // Save new Host/IP if user changed it in the Captive Portal
    String newIP = custom_server_ip.getValue();
    newIP.trim();
    if (newIP != "" && newIP != serverIP) {
      serverIP = newIP;
      preferences.begin("scanner", false);
      preferences.putString("serverIP", serverIP);
      preferences.end();
    }

    // Intelligently build the base URL for local IP or cloud domain
    String baseHost = serverIP;
    baseHost.trim();
    if (!baseHost.startsWith("http://") && !baseHost.startsWith("https://")) {
      if (baseHost.indexOf(":") == -1 && (baseHost.startsWith("192.") || baseHost.startsWith("10.") || baseHost.startsWith("172."))) {
        baseHost = "http://" + baseHost + ":8001";
      } else {
        baseHost = "http://" + baseHost;
      }
    }
    if (baseHost.endsWith("/")) {
      baseHost = baseHost.substring(0, baseHost.length() - 1);
    }

    serverUrl = baseHost + "/api/attendance/scan";
    heartbeatUrl = baseHost + "/api/hardware/heartbeat";

    Serial.println("Server URL: " + serverUrl);

    lcdPrint("WiFi Connected!", WiFi.localIP().toString());
    digitalWrite(LED_GREEN_PIN, HIGH);
    delay(1500);
    digitalWrite(LED_GREEN_PIN, LOW);
  } else {
    lcdPrint("WiFi FAILED!", "Restarting...");
    errorSignal();
    delay(3000);
    ESP.restart();  // Reboot and try again
  }
}

void sendScanData(String uid) {
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(8000);

  StaticJsonDocument<512> doc;
  doc["device_id"] = deviceId;
  doc["uid"] = uid;
  doc["ip"] = WiFi.localIP().toString();
  doc["rssi"] = WiFi.RSSI();

  String requestBody;
  serializeJson(doc, requestBody);

  int httpResponseCode = http.POST(requestBody);

  if (httpResponseCode > 0) {
    String response = http.getString();
    DynamicJsonDocument res(1024);
    DeserializationError jsonErr = deserializeJson(res, response);

    if (!jsonErr) {
      String status = res["status"].as<String>();
      String action = res["action"].as<String>();
      String teacherName = res["teacher_name"].as<String>();

      if (status == "success") {
        String attStatus = res["attendance_status"].as<String>();

        if (action == "check-in") {
          if (attStatus == "late") {
            lcdPrint(teacherName, "LATE CHECK-IN");
            lateSignal();
          } else {
            lcdPrint(teacherName, "CHECK-IN OK");
            successSignal();
          }
        } else if (action == "check-out") {
          lcdPrint(teacherName, "CHECK-OUT OK");
          checkoutSignal();
        } else {
          lcdPrint(teacherName, "Scan Success");
          successSignal();
        }
      } else if (status == "info") {
        lcdPrint(teacherName, "ALREADY SCANNED");
        infoSignal();
      } else {
        lcdPrint("ERROR", res["message"].as<String>());
        errorSignal();
      }
    } else {
      lcdPrint("JSON Error", "Bad Response");
      errorSignal();
    }
  } else {
    lcdPrint("Server Error", "Code: " + String(httpResponseCode));
    errorSignal();
  }
  http.end();
}

void successSignal() {
  digitalWrite(LED_GREEN_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(150);
  digitalWrite(BUZZER_PIN, LOW);
  delay(100);
  digitalWrite(LED_GREEN_PIN, LOW);
}

void checkoutSignal() {
  digitalWrite(LED_GREEN_PIN, HIGH);
  // Two rising beeps
  digitalWrite(BUZZER_PIN, HIGH);
  delay(80);
  digitalWrite(BUZZER_PIN, LOW);
  delay(50);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(200);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_GREEN_PIN, LOW);
}

void lateSignal() {
  digitalWrite(LED_RED_PIN, HIGH);
  // Three short warning beeps
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(100);
    digitalWrite(BUZZER_PIN, LOW);
    delay(100);
  }
  digitalWrite(LED_RED_PIN, LOW);
}

void infoSignal() {
  // Two very short beeps for "Already Scanned"
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(50);
    digitalWrite(BUZZER_PIN, LOW);
    delay(50);
  }
}

void errorSignal() {
  digitalWrite(LED_RED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(800);
  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_RED_PIN, LOW);
}
