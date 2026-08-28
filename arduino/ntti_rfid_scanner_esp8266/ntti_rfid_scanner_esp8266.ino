#include <ArduinoJson.h>
#include <HTTPClient.h>
#include <LiquidCrystal_I2C.h>
#include <MFRC522.h>
#include <Preferences.h>
#include <SPI.h>
#include <WiFi.h>
#include <WiFiManager.h>
#include <Wire.h>

// =====================================================
// HARDWARE PINS
// =====================================================

#define RST_PIN 4
#define SS_PIN 5

#define LED_GREEN_PIN 32
#define LED_RED_PIN 33
#define BUZZER_PIN 25

// =====================================================
// LCD
// =====================================================

LiquidCrystal_I2C lcd1(0x27, 16, 2);
LiquidCrystal_I2C lcd2(0x3F, 16, 2);

// =====================================================
// RFID
// =====================================================

MFRC522 mfrc522(SS_PIN, RST_PIN);

// =====================================================
// PREFERENCES
// =====================================================

Preferences preferences;

// =====================================================
// SERVER CONFIGURATION
// =====================================================

String serverIP = "66.42.61.106";
String serverUrl = "";
String heartbeatUrl = "";

const char *deviceId = "SCANNER_01";

// =====================================================
// HEARTBEAT
// =====================================================

unsigned long lastHeartbeat = 0;

const unsigned long heartbeatInterval = 5000;

// =====================================================
// LCD HELPERS
// =====================================================

String lcdPad(String s, int width) {

  s.trim();

  if ((int)s.length() > width) {
    s = s.substring(0, width);
  }

  while ((int)s.length() < width) {
    s += " ";
  }

  return s;
}


// =====================================================
// PRINT TO BOTH LCDs
// =====================================================

void lcdPrint(String line1, String line2 = "") {

  String p1 = lcdPad(line1, 16);
  String p2 = lcdPad(line2, 16);

  Serial.print("LCD: ");
  Serial.print(line1);
  Serial.print(" | ");
  Serial.println(line2);

  // LCD 0x27
  lcd1.setCursor(0, 0);
  lcd1.print(p1);

  lcd1.setCursor(0, 1);
  lcd1.print(p2);

  // LCD 0x3F
  lcd2.setCursor(0, 0);
  lcd2.print(p1);

  lcd2.setCursor(0, 1);
  lcd2.print(p2);
}


// =====================================================
// LCD IDLE SCREEN
// =====================================================

void lcdIdle() {

  lcdPrint(
    "NTTI Attendance",
    "Scan your card.."
  );
}


// =====================================================
// HEARTBEAT
// =====================================================

void sendHeartbeat() {

  if (WiFi.status() != WL_CONNECTED) {

    Serial.println("Heartbeat skipped - WiFi not connected");

    return;
  }

  if (heartbeatUrl == "") {

    Serial.println("Heartbeat skipped - URL empty");

    return;
  }

  Serial.println("Sending heartbeat...");

  HTTPClient http;

  http.begin(heartbeatUrl);

  http.addHeader(
    "Content-Type",
    "application/json"
  );

  http.setTimeout(5000);

  StaticJsonDocument<128> doc;

  doc["device_id"] = deviceId;
  doc["ip"] = WiFi.localIP().toString();
  doc["rssi"] = WiFi.RSSI();

  String requestBody;

  serializeJson(
    doc,
    requestBody
  );

  Serial.print("Heartbeat URL: ");
  Serial.println(heartbeatUrl);

  Serial.print("Heartbeat Body: ");
  Serial.println(requestBody);

  int httpCode = http.POST(requestBody);

  if (httpCode > 0) {

    Serial.print("Heartbeat sent. HTTP Code: ");
    Serial.println(httpCode);

    Serial.print("IP: ");
    Serial.println(WiFi.localIP());

    Serial.print("RSSI: ");
    Serial.println(WiFi.RSSI());

  } else {

    Serial.print("Heartbeat failed: ");

    Serial.println(
      http.errorToString(httpCode).c_str()
    );
  }

  http.end();
}


// =====================================================
// WIFI CONNECTION
// =====================================================

void connectWiFi() {

  Serial.println();
  Serial.println("================================");
  Serial.println("Starting WiFiManager...");
  Serial.println("================================");

  lcdPrint(
    "Setup WiFi AP:",
    "Scanner_Setup"
  );


  // ---------------------------------------------------
  // Load saved server IP
  // ---------------------------------------------------

  preferences.begin(
    "scanner",
    false
  );

  serverIP = preferences.getString(
    "serverIP",
    "66.42.61.106"
  );

  preferences.end();


  Serial.print("Saved Server: ");
  Serial.println(serverIP);


  // ---------------------------------------------------
  // WiFiManager
  // ---------------------------------------------------

  WiFiManager wifiManager;


  // ---------------------------------------------------
  // Server IP input
  // ---------------------------------------------------

  char ipBuffer[64];

  serverIP.toCharArray(
    ipBuffer,
    sizeof(ipBuffer)
  );


  WiFiManagerParameter custom_server_ip(
    "server_ip",
    "Server Host/URL",
    ipBuffer,
    63
  );


  wifiManager.addParameter(
    &custom_server_ip
  );


  // ---------------------------------------------------
  // Auto Connect
  // ---------------------------------------------------

  Serial.println(
    "Trying saved WiFi..."
  );

  bool connected =
    wifiManager.autoConnect(
      "Scanner_Setup"
    );


  // ---------------------------------------------------
  // Connection successful
  // ---------------------------------------------------

  if (connected) {

    Serial.println();
    Serial.println("WiFi Connected!");

    Serial.print("SSID: ");
    Serial.println(WiFi.SSID());

    Serial.print("IP: ");
    Serial.println(WiFi.localIP());

    Serial.print("RSSI: ");
    Serial.println(WiFi.RSSI());


    // -------------------------------------------------
    // Save new server IP
    // -------------------------------------------------

    String newIP =
      custom_server_ip.getValue();

    newIP.trim();


    if (
      newIP != "" &&
      newIP != serverIP
    ) {

      serverIP = newIP;

      preferences.begin(
        "scanner",
        false
      );

      preferences.putString(
        "serverIP",
        serverIP
      );

      preferences.end();

      Serial.print(
        "New Server Saved: "
      );

      Serial.println(serverIP);
    }


    // -------------------------------------------------
    // Build Base URL
    // -------------------------------------------------

    String baseHost = serverIP;

    baseHost.trim();


    // -------------------------------------------------
    // Add HTTP if missing
    // -------------------------------------------------

    if (
      !baseHost.startsWith("http://") &&
      !baseHost.startsWith("https://")
    ) {

      // Local network
      if (
        baseHost.indexOf(":") == -1 &&
        (
          baseHost.startsWith("192.") ||
          baseHost.startsWith("10.") ||
          baseHost.startsWith("172.")
        )
      ) {

        baseHost =
          "http://" +
          baseHost +
          ":8001";

      } else {

        baseHost =
          "http://" +
          baseHost;
      }
    }


    // -------------------------------------------------
    // Remove trailing /
    // -------------------------------------------------

    if (
      baseHost.endsWith("/")
    ) {

      baseHost =
        baseHost.substring(
          0,
          baseHost.length() - 1
        );
    }


    // -------------------------------------------------
    // API URLs
    // -------------------------------------------------

    serverUrl =
      baseHost +
      "/api/attendance/scan";

    heartbeatUrl =
      baseHost +
      "/api/hardware/heartbeat";


    Serial.println();
    Serial.println("========== SERVER CONFIG ==========");

    Serial.print("Base URL: ");
    Serial.println(baseHost);

    Serial.print("Attendance URL: ");
    Serial.println(serverUrl);

    Serial.print("Heartbeat URL: ");
    Serial.println(heartbeatUrl);

    Serial.println(
      "==================================="
    );


    // -------------------------------------------------
    // LCD
    // -------------------------------------------------

    lcdPrint(
      "WiFi Connected!",
      WiFi.localIP().toString()
    );


    // Green LED
    digitalWrite(
      LED_GREEN_PIN,
      HIGH
    );

    delay(1500);

    digitalWrite(
      LED_GREEN_PIN,
      LOW
    );

  }

  // ---------------------------------------------------
  // WiFi failed
  // ---------------------------------------------------

  else {

    Serial.println(
      "WiFi connection FAILED!"
    );

    lcdPrint(
      "WiFi FAILED!",
      "Restarting..."
    );

    errorSignal();

    delay(3000);

    ESP.restart();
  }
}


// =====================================================
// SEND RFID SCAN DATA
// =====================================================

void sendScanData(String uid) {

  if (WiFi.status() != WL_CONNECTED) {

    lcdPrint(
      "WiFi Error",
      "Not Connected"
    );

    errorSignal();

    return;
  }


  if (serverUrl == "") {

    lcdPrint(
      "Server Error",
      "URL Empty"
    );

    errorSignal();

    return;
  }


  Serial.println();
  Serial.println("================================");
  Serial.println("Sending RFID Scan");
  Serial.println("================================");


  HTTPClient http;

  http.begin(serverUrl);

  http.addHeader(
    "Content-Type",
    "application/json"
  );

  http.setTimeout(8000);


  // ---------------------------------------------------
  // JSON Request
  // ---------------------------------------------------

  StaticJsonDocument<512> doc;

  doc["device_id"] = deviceId;
  doc["uid"] = uid;
  doc["ip"] = WiFi.localIP().toString();
  doc["rssi"] = WiFi.RSSI();


  String requestBody;

  serializeJson(
    doc,
    requestBody
  );


  Serial.print("URL: ");
  Serial.println(serverUrl);

  Serial.print("Request: ");
  Serial.println(requestBody);


  // ---------------------------------------------------
  // POST
  // ---------------------------------------------------

  int httpResponseCode =
    http.POST(requestBody);


  Serial.print(
    "HTTP Response Code: "
  );

  Serial.println(
    httpResponseCode
  );


  // ---------------------------------------------------
  // Server Response
  // ---------------------------------------------------

  if (httpResponseCode > 0) {

    String response =
      http.getString();


    Serial.println(
      "Server Response:"
    );

    Serial.println(
      response
    );


    // -------------------------------------------------
    // Parse JSON
    // -------------------------------------------------

    StaticJsonDocument<768> res;

    DeserializationError jsonErr =
      deserializeJson(
        res,
        response
      );


    if (!jsonErr) {

      String status =
        res["status"] |
        "";

      String action =
        res["action"] |
        "";

      String teacherName =
        res["teacher_name"] |
        "";


      Serial.print("Status: ");
      Serial.println(status);

      Serial.print("Action: ");
      Serial.println(action);

      Serial.print("Teacher: ");
      Serial.println(teacherName);


      // ------------------------------------------------
      // SUCCESS
      // ------------------------------------------------

      if (status == "success") {

        String attStatus =
          res["attendance_status"] |
          "";


        Serial.print(
          "Attendance Status: "
        );

        Serial.println(
          attStatus
        );


        // ----------------------------------------------
        // CHECK-IN
        // ----------------------------------------------

        if (
          action == "check-in"
        ) {

          if (
            attStatus == "late"
          ) {

            lcdPrint(
              teacherName,
              "LATE CHECK-IN"
            );

            lateSignal();

          } else {

            lcdPrint(
              teacherName,
              "CHECK-IN OK"
            );

            successSignal();
          }
        }


        // ----------------------------------------------
        // CHECK-OUT
        // ----------------------------------------------

        else if (
          action == "check-out"
        ) {

          lcdPrint(
            teacherName,
            "CHECK-OUT OK"
          );

          checkoutSignal();
        }


        // ----------------------------------------------
        // OTHER SUCCESS
        // ----------------------------------------------

        else {

          lcdPrint(
            teacherName,
            "Scan Success"
          );

          successSignal();
        }
      }


      // ------------------------------------------------
      // ALREADY SCANNED
      // ------------------------------------------------

      else if (
        status == "info"
      ) {

        lcdPrint(
          teacherName,
          "ALREADY SCANNED"
        );

        infoSignal();
      }


      // ------------------------------------------------
      // SERVER ERROR
      // ------------------------------------------------

      else {

        String message =
          res["message"] |
          "Unknown Error";


        lcdPrint(
          "ERROR",
          message
        );

        errorSignal();
      }
    }


    // --------------------------------------------------
    // JSON ERROR
    // --------------------------------------------------

    else {

      Serial.print(
        "JSON Parse Error: "
      );

      Serial.println(
        jsonErr.c_str()
      );


      lcdPrint(
        "JSON Error",
        "Bad Response"
      );

      errorSignal();
    }
  }


  // ---------------------------------------------------
  // HTTP ERROR
  // ---------------------------------------------------

  else {

    Serial.print(
      "HTTP Error: "
    );

    Serial.println(
      http.errorToString(
        httpResponseCode
      ).c_str()
    );


    lcdPrint(
      "Server Error",
      "Code: " +
      String(httpResponseCode)
    );

    errorSignal();
  }


  http.end();

  Serial.println(
    "RFID request finished."
  );
}


// =====================================================
// SUCCESS SIGNAL
// =====================================================

void successSignal() {

  digitalWrite(
    LED_GREEN_PIN,
    HIGH
  );

  digitalWrite(
    BUZZER_PIN,
    HIGH
  );

  delay(150);

  digitalWrite(
    BUZZER_PIN,
    LOW
  );

  delay(100);

  digitalWrite(
    LED_GREEN_PIN,
    LOW
  );
}


// =====================================================
// CHECK-OUT SIGNAL
// =====================================================

void checkoutSignal() {

  digitalWrite(
    LED_GREEN_PIN,
    HIGH
  );


  // First beep

  digitalWrite(
    BUZZER_PIN,
    HIGH
  );

  delay(80);

  digitalWrite(
    BUZZER_PIN,
    LOW
  );

  delay(50);


  // Second beep

  digitalWrite(
    BUZZER_PIN,
    HIGH
  );

  delay(200);

  digitalWrite(
    BUZZER_PIN,
    LOW
  );


  digitalWrite(
    LED_GREEN_PIN,
    LOW
  );
}


// =====================================================
// LATE SIGNAL
// =====================================================

void lateSignal() {

  digitalWrite(
    LED_RED_PIN,
    HIGH
  );


  // Three short beeps

  for (
    int i = 0;
    i < 3;
    i++
  ) {

    digitalWrite(
      BUZZER_PIN,
      HIGH
    );

    delay(100);

    digitalWrite(
      BUZZER_PIN,
      LOW
    );

    delay(100);
  }


  digitalWrite(
    LED_RED_PIN,
    LOW
  );
}


// =====================================================
// INFO SIGNAL
// =====================================================

void infoSignal() {

  // Two short beeps

  for (
    int i = 0;
    i < 2;
    i++
  ) {

    digitalWrite(
      BUZZER_PIN,
      HIGH
    );

    delay(50);

    digitalWrite(
      BUZZER_PIN,
      LOW
    );

    delay(50);
  }
}


// =====================================================
// ERROR SIGNAL
// =====================================================

void errorSignal() {

  digitalWrite(
    LED_RED_PIN,
    HIGH
  );

  digitalWrite(
    BUZZER_PIN,
    HIGH
  );

  delay(800);

  digitalWrite(
    BUZZER_PIN,
    LOW
  );

  digitalWrite(
    LED_RED_PIN,
    LOW
  );
}


// =====================================================
// SETUP
// =====================================================

void setup() {

  Serial.begin(115200);

  delay(500);


  Serial.println();
  Serial.println();
  Serial.println(
    "================================"
  );
  Serial.println(
    "NTTI RFID ATTENDANCE"
  );
  Serial.println(
    "ESP32 SCANNER"
  );
  Serial.println(
    "================================"
  );


  // ---------------------------------------------------
  // STEP 1
  // ---------------------------------------------------

  Serial.println(
    "STEP 1: Setup started"
  );


  // ---------------------------------------------------
  // GPIO
  // ---------------------------------------------------

  pinMode(
    LED_GREEN_PIN,
    OUTPUT
  );

  pinMode(
    LED_RED_PIN,
    OUTPUT
  );

  pinMode(
    BUZZER_PIN,
    OUTPUT
  );


  digitalWrite(
    LED_GREEN_PIN,
    LOW
  );

  digitalWrite(
    LED_RED_PIN,
    LOW
  );

  digitalWrite(
    BUZZER_PIN,
    LOW
  );


  Serial.println(
    "STEP 2: GPIO OK"
  );


  // ---------------------------------------------------
  // I2C
  // ---------------------------------------------------

  Wire.begin(
    21,
    22
  );


  Serial.println(
    "STEP 3: I2C started"
  );


  // ---------------------------------------------------
  // LCD 0x27
  // ---------------------------------------------------

  Serial.println(
    "STEP 4: Initializing LCD 0x27..."
  );

  lcd1.init();

  lcd1.backlight();


  Serial.println(
    "STEP 5: LCD 0x27 OK"
  );


  // ---------------------------------------------------
  // LCD 0x3F
  // ---------------------------------------------------

  Serial.println(
    "STEP 6: Initializing LCD 0x3F..."
  );

  lcd2.init();

  lcd2.backlight();


  Serial.println(
    "STEP 7: LCD 0x3F OK"
  );


  // ---------------------------------------------------
  // Startup LCD
  // ---------------------------------------------------

  delay(500);

  lcdPrint(
    "NTTI Attendance",
    "Starting..."
  );


  delay(1000);


  // ---------------------------------------------------
  // SPI
  // ---------------------------------------------------

  Serial.println(
    "STEP 8: Starting SPI..."
  );

  SPI.begin();


  Serial.println(
    "STEP 9: SPI OK"
  );


  // ---------------------------------------------------
  // RC522
  // ---------------------------------------------------

  Serial.println(
    "STEP 10: Initializing RC522..."
  );

  mfrc522.PCD_Init();


  Serial.println(
    "STEP 11: RC522 OK"
  );


  Serial.println(
    "Ready to scan cards."
  );


  // ---------------------------------------------------
  // WiFi
  // ---------------------------------------------------

  Serial.println(
    "STEP 12: Starting WiFiManager..."
  );

  connectWiFi();


  Serial.println(
    "STEP 13: WiFi connected"
  );


  // ---------------------------------------------------
  // Heartbeat
  // ---------------------------------------------------

  Serial.println(
    "STEP 14: Sending heartbeat..."
  );

  sendHeartbeat();


  Serial.println(
    "STEP 15: Heartbeat done"
  );


  lastHeartbeat =
    millis();


  // ---------------------------------------------------
  // Idle
  // ---------------------------------------------------

  lcdIdle();


  Serial.println();
  Serial.println(
    "================================"
  );
  Serial.println(
    "SYSTEM READY"
  );
  Serial.println(
    "SCAN RFID CARD"
  );
  Serial.println(
    "================================"
  );
}


// =====================================================
// LOOP
// =====================================================

void loop() {

  // ---------------------------------------------------
  // WiFi Maintenance
  // ---------------------------------------------------

  if (
    WiFi.status() != WL_CONNECTED
  ) {

    Serial.println(
      "WiFi Lost! Reconnecting..."
    );

    lcdPrint(
      "WiFi Lost!",
      "Reconnecting..."
    );


    connectWiFi();


    lcdIdle();
  }


  // ---------------------------------------------------
  // Heartbeat
  // ---------------------------------------------------

  if (
    millis() -
    lastHeartbeat >=
    heartbeatInterval
  ) {

    sendHeartbeat();

    lastHeartbeat =
      millis();
  }


  // ---------------------------------------------------
  // RFID
  // ---------------------------------------------------

  if (
    !mfrc522.PICC_IsNewCardPresent()
  ) {

    return;
  }


  if (
    !mfrc522.PICC_ReadCardSerial()
  ) {

    return;
  }


  // ---------------------------------------------------
  // Build UID
  // ---------------------------------------------------

  String uid = "";


  for (
    byte i = 0;
    i < mfrc522.uid.size;
    i++
  ) {

    if (
      mfrc522.uid.uidByte[i] < 0x10
    ) {

      uid += "0";
    }


    uid += String(
      mfrc522.uid.uidByte[i],
      HEX
    );
  }


  uid.toUpperCase();


  // ---------------------------------------------------
  // RFID detected
  // ---------------------------------------------------

  Serial.println();
  Serial.println(
    "================================"
  );

  Serial.print(
    "Card Scanned! UID: "
  );

  Serial.println(uid);

  Serial.println(
    "================================"
  );


  lcdPrint(
    "Card Detected",
    uid
  );


  delay(1000);


  // ---------------------------------------------------
  // Send to Laravel
  // ---------------------------------------------------

  sendScanData(uid);


  // ---------------------------------------------------
  // Update heartbeat timer
  // ---------------------------------------------------

  lastHeartbeat =
    millis();


  // ---------------------------------------------------
  // Stop RFID
  // ---------------------------------------------------

  mfrc522.PICC_HaltA();

  mfrc522.PCD_StopCrypto1();


  // ---------------------------------------------------
  // Return to idle
  // ---------------------------------------------------

  delay(1500);

  lcdIdle();
}