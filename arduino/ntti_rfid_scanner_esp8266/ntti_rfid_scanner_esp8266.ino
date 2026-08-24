
// ============================================================
//  NTTI Attendance RFID Scanner - ESP8266MOD Version
//  Board: ESP8266 (NodeMCU / Wemos D1 Mini / ESP8266MOD)
//
//  Differences from ESP32 version:
//    - Uses ESP8266WiFi.h instead of WiFi.h
//    - Uses ESP8266HTTPClient.h instead of HTTPClient.h
//    - Uses EEPROM instead of Preferences (NVS)
//    - I2C pins: SDA=D2(GPIO4), SCL=D1(GPIO5)
//    - SPI pins: MOSI=D7, MISO=D6, SCK=D5, SS=D8(GPIO15)
//    - RST: D3 (GPIO0)
//    - LED Green: D4 (GPIO2) — active LOW on ESP8266
//    - LED Red:   D0 (GPIO16)
//    - Buzzer:    D8 is used for SS, so Buzzer on TX(GPIO1)
//                 OR use a spare pin — see PIN MAP below
// ============================================================
//
//  WIRING PIN MAP (NodeMCU/ESP8266MOD):
//  ┌─────────────────────────────────────────────┐
//  │  RFID RC522                                  │
//  │    SDA  →  D8 (GPIO15)                       │
//  │    SCK  →  D5 (GPIO14)                       │
//  │    MOSI →  D7 (GPIO13)                       │
//  │    MISO →  D6 (GPIO12)                       │
//  │    IRQ  →  not connected                     │
//  │    GND  →  GND                               │
//  │    RST  →  D3 (GPIO0)                        │
//  │    3.3V →  3.3V                              │
//  │                                              │
//  │  LCD I2C (16x2)                              │
//  │    SDA  →  D2 (GPIO4)                        │
//  │    SCL  →  D1 (GPIO5)                        │
//  │    VCC  →  5V or 3.3V                        │
//  │    GND  →  GND                               │
//  │                                              │
//  │  LEDs & Buzzer                               │
//  │    Green LED (+) → D4 (GPIO2) [active LOW!]  │
//  │    Red   LED (+) → D0 (GPIO16)               │
//  │    Buzzer (+)    → RX (GPIO3)                │
//  └─────────────────────────────────────────────┘
//
// ============================================================

#include <ArduinoJson.h>
#include <EEPROM.h>            // Replaces Preferences (ESP32 NVS)
#include <ESP8266HTTPClient.h> // Replaces HTTPClient
#include <ESP8266WiFi.h>       // Replaces WiFi.h
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <SPI.h>
#include <WiFiManager.h>
#include <Wire.h>

// ---------------------------
// EEPROM Config
// ---------------------------
#define EEPROM_SIZE   64          // Bytes reserved
#define EEPROM_ADDR   0           // Start address for saved serverIP

// ---------------------------
// Network & API Configuration
// ---------------------------
String serverIP  = "66.42.61.106";  // Default: Vultr Live Server
String serverUrl = "";
String heartbeatUrl = "";

const char *deviceId = "SCANNER_02";  // Change to unique ID for each scanner

unsigned long lastHeartbeat = 0;
const unsigned long heartbeatInterval = 60000; // 60 seconds

// ---------------------------
// Hardware Pins (ESP8266MOD)
// ---------------------------
#define RST_PIN        0   // D3
#define SS_PIN         15  // D8
#define LED_GREEN_PIN  2   // D4 — NOTE: Active LOW on ESP8266 (inverted)
#define LED_RED_PIN    16  // D0
#define BUZZER_PIN     3   // RX pin (GPIO3) — disconnect USB serial when using

// ---------------------------
// LCD Setup (I2C)
// SDA = D2 (GPIO4), SCL = D1 (GPIO5)
// ---------------------------
LiquidCrystal_I2C lcd1(0x27, 16, 2);
LiquidCrystal_I2C lcd2(0x3F, 16, 2);

MFRC522 mfrc522(SS_PIN, RST_PIN);

WiFiClient wifiClient; // Required for ESP8266HTTPClient

// ---------------------------
// EEPROM Helpers (replaces Preferences)
// ---------------------------
void saveServerIP(String ip) {
  ip.trim();
  // Clear the area first
  for (int i = 0; i < EEPROM_SIZE; i++) {
    EEPROM.write(EEPROM_ADDR + i, 0);
  }
  // Write new IP
  for (int i = 0; i < (int)ip.length() && i < EEPROM_SIZE - 1; i++) {
    EEPROM.write(EEPROM_ADDR + i, ip[i]);
  }
  EEPROM.commit();
  Serial.println("Server IP saved to EEPROM: " + ip);
}

String loadServerIP() {
  String ip = "";
  for (int i = 0; i < EEPROM_SIZE; i++) {
    char c = (char)EEPROM.read(EEPROM_ADDR + i);
    if (c == 0 || c == 255) break;
    ip += c;
  }
  ip.trim();
  if (ip.length() == 0) ip = "66.42.61.106"; // fallback default
  Serial.println("Loaded Server IP from EEPROM: " + ip);
  return ip;
}

// ---------------------------
// LCD Helpers
// ---------------------------
String lcdPad(String s, int width) {
  s.trim();
  if ((int)s.length() > width) s = s.substring(0, width);
  while ((int)s.length() < width) s += " ";
  return s;
}

void lcdPrint(String line1, String line2 = "") {
  String p1 = lcdPad(line1, 16);
  String p2 = lcdPad(line2, 16);

  Serial.println("LCD: " + line1 + " | " + line2);

  lcd1.setCursor(0, 0); lcd1.print(p1);
  lcd1.setCursor(0, 1); lcd1.print(p2);
  lcd2.setCursor(0, 0); lcd2.print(p1);
  lcd2.setCursor(0, 1); lcd2.print(p2);
}

void lcdIdle() {
  lcdPrint("NTTI Attendance", "Scan your card..");
}

// ---------------------------
// LED Helpers
// NOTE: LED_GREEN_PIN (D4/GPIO2) is ACTIVE LOW on ESP8266
//       So HIGH = OFF, LOW = ON — we invert logic below
// ---------------------------
void greenOn()  { digitalWrite(LED_GREEN_PIN, LOW);  }   // Active LOW
void greenOff() { digitalWrite(LED_GREEN_PIN, HIGH); }   // Active LOW
void redOn()    { digitalWrite(LED_RED_PIN,   HIGH); }
void redOff()   { digitalWrite(LED_RED_PIN,   LOW);  }

// ---------------------------
// Signal Patterns
// ---------------------------
void successSignal() {
  greenOn();
  digitalWrite(BUZZER_PIN, HIGH); delay(150);
  digitalWrite(BUZZER_PIN, LOW);  delay(100);
  greenOff();
}

void checkoutSignal() {
  greenOn();
  digitalWrite(BUZZER_PIN, HIGH); delay(80);
  digitalWrite(BUZZER_PIN, LOW);  delay(50);
  digitalWrite(BUZZER_PIN, HIGH); delay(200);
  digitalWrite(BUZZER_PIN, LOW);
  greenOff();
}

void lateSignal() {
  redOn();
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH); delay(100);
    digitalWrite(BUZZER_PIN, LOW);  delay(100);
  }
  redOff();
}

void infoSignal() {
  for (int i = 0; i < 2; i++) {
    digitalWrite(BUZZER_PIN, HIGH); delay(50);
    digitalWrite(BUZZER_PIN, LOW);  delay(50);
  }
}

void errorSignal() {
  redOn();
  digitalWrite(BUZZER_PIN, HIGH); delay(800);
  digitalWrite(BUZZER_PIN, LOW);
  redOff();
}

// ---------------------------
// Heartbeat
// ---------------------------
void sendHeartbeat() {
  if (WiFi.status() != WL_CONNECTED) return;

  HTTPClient http;
  http.begin(wifiClient, heartbeatUrl); // ESP8266 requires WiFiClient
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(5000);

  StaticJsonDocument<128> doc;
  doc["device_id"] = deviceId;
  doc["ip"]        = WiFi.localIP().toString();
  doc["rssi"]      = WiFi.RSSI();

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
// WiFi Connect (via WiFiManager)
// ---------------------------
void connectWiFi() {
  Serial.println("Starting WiFiManager...");
  lcdPrint("Setup WiFi AP:", "Scanner_Setup");

  // Load saved Server IP from EEPROM
  serverIP = loadServerIP();

  WiFiManager wifiManager;

  // Custom parameter for Server IP entry in captive portal
  char ipBuffer[64];
  serverIP.toCharArray(ipBuffer, 64);
  WiFiManagerParameter custom_server_ip(
    "server_ip",
    "Server Host/IP (e.g. 66.42.61.106 or ntti.example.com)",
    ipBuffer,
    60
  );
  wifiManager.addParameter(&custom_server_ip);

  bool connected = wifiManager.autoConnect("Scanner_Setup");

  if (connected) {
    // Save new IP if user changed it in captive portal
    String newIP = custom_server_ip.getValue();
    newIP.trim();
    if (newIP != "" && newIP != serverIP) {
      serverIP = newIP;
      saveServerIP(serverIP);
    }

    // Build URL intelligently (local IP vs cloud domain)
    String baseHost = serverIP;
    baseHost.trim();
    if (!baseHost.startsWith("http://") && !baseHost.startsWith("https://")) {
      if (baseHost.indexOf(":") == -1 &&
          (baseHost.startsWith("192.") || baseHost.startsWith("10.") || baseHost.startsWith("172."))) {
        baseHost = "http://" + baseHost + ":8001";
      } else {
        baseHost = "http://" + baseHost;
      }
    }
    if (baseHost.endsWith("/")) {
      baseHost = baseHost.substring(0, baseHost.length() - 1);
    }

    serverUrl    = baseHost + "/api/attendance/scan";
    heartbeatUrl = baseHost + "/api/hardware/heartbeat";

    Serial.println("Server URL: " + serverUrl);

    lcdPrint("WiFi Connected!", WiFi.localIP().toString());
    greenOn(); delay(1500); greenOff();
  } else {
    lcdPrint("WiFi FAILED!", "Restarting...");
    errorSignal();
    delay(3000);
    ESP.restart();
  }
}

// ---------------------------
// Send RFID Scan to Server
// ---------------------------
void sendScanData(String uid) {
  HTTPClient http;
  http.begin(wifiClient, serverUrl); // ESP8266 requires WiFiClient
  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.setTimeout(8000);

  StaticJsonDocument<512> doc;
  doc["device_id"] = deviceId;
  doc["uid"]       = uid;
  doc["ip"]        = WiFi.localIP().toString();
  doc["rssi"]      = WiFi.RSSI();

  String requestBody;
  serializeJson(doc, requestBody);

  int httpResponseCode = http.POST(requestBody);

  if (httpResponseCode > 0) {
    String response = http.getString();
    DynamicJsonDocument res(1024);
    DeserializationError jsonErr = deserializeJson(res, response);

    if (!jsonErr) {
      String status      = res["status"].as<String>();
      String action      = res["action"].as<String>();
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

// ---------------------------
// setup()
// ---------------------------
void setup() {
  Serial.begin(115200);

  // Init EEPROM
  EEPROM.begin(EEPROM_SIZE);

  // Pin Modes
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_RED_PIN,   OUTPUT);
  pinMode(BUZZER_PIN,    OUTPUT);
  greenOff();             // Active LOW — start with LED OFF
  redOff();
  digitalWrite(BUZZER_PIN, LOW);

  // I2C for LCD — ESP8266: SDA=GPIO4(D2), SCL=GPIO5(D1)
  Wire.begin(4, 5);

  lcd1.init(); lcd1.backlight();
  lcd2.init(); lcd2.backlight();

  delay(500);
  lcdPrint("NTTI Attendance", "  Starting...   ");
  delay(1000);

  // SPI for RFID
  SPI.begin();
  mfrc522.PCD_Init();
  Serial.println(F("RFID Ready."));

  connectWiFi();

  sendHeartbeat();
  lastHeartbeat = millis();

  lcdIdle();
}

// ---------------------------
// loop()
// ---------------------------
void loop() {
  // WiFi reconnect if lost
  if (WiFi.status() != WL_CONNECTED) {
    lcdPrint("WiFi Lost!", "Reconnecting...");
    connectWiFi();
    lcdIdle();
  }

  // Heartbeat every 60 seconds
  if (millis() - lastHeartbeat >= heartbeatInterval) {
    sendHeartbeat();
    lastHeartbeat = millis();
  }

  // RFID Scan
  if (!mfrc522.PICC_IsNewCardPresent() || !mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  // Build UID string
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

  lastHeartbeat = millis(); // Reset heartbeat timer after scan

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  delay(4000);
  lcdIdle();
}
