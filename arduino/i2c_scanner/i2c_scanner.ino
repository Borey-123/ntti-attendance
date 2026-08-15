// ============================================================
//  I2C Address Scanner
//  Upload this to your ESP32, then open Serial Monitor at 115200
//  It will tell you the exact I2C address of your LCD adapter
// ============================================================
#include <Wire.h>

#define SDA_PIN 26
#define SCL_PIN 27

void setup() {
  Serial.begin(115200);
  delay(1000);
  Wire.begin(SDA_PIN, SCL_PIN);

  Serial.println("==============================");
  Serial.println("  ESP32 I2C Address Scanner");
  Serial.println("  SDA=GPIO26  SCL=GPIO27");
  Serial.println("==============================");
  Serial.println("Scanning...");

  int found = 0;
  for (byte addr = 1; addr < 127; addr++) {
    Wire.beginTransmission(addr);
    byte error = Wire.endTransmission();

    if (error == 0) {
      Serial.print("  Found device at address: 0x");
      if (addr < 16) Serial.print("0");
      Serial.print(addr, HEX);

      // Identify common chips
      if (addr == 0x27) Serial.print("  <-- PCF8574  (most common LCD adapter)");
      if (addr == 0x3F) Serial.print("  <-- PCF8574A (alternate LCD adapter)");
      Serial.println();
      found++;
    }
  }

  if (found == 0) {
    Serial.println();
    Serial.println("  *** No I2C devices found! ***");
    Serial.println("  Check SDA/SCL wiring on GPIO 26/27.");
  } else {
    Serial.print("\n  Total devices found: ");
    Serial.println(found);
  }

  Serial.println("==============================");
  Serial.println("Use the address above in");
  Serial.println("#define LCD_I2C_ADDR  0x??");
  Serial.println("==============================");
}

void loop() {
  // Nothing — check Serial Monitor for results
}
