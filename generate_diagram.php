<?php

$mermaid = <<<EOT
erDiagram
    TEACHER ||--o{ ATTENDANCE : "has many"
    TEACHER ||--o| RFID_CARD : "has one"
    DEPARTMENT ||--o{ TEACHER : "has many"
    TEACHER ||--o| DEPARTMENT : "is head of"

    TEACHER {
        int id PK
        string employee_id
        string name
        string name_kh
        string department FK
        string email
        string phone
        string photo
        string position
        string status
    }

    ATTENDANCE {
        int id PK
        int teacher_id FK
        date date
        string rfid_uid
        time morning_in
        time morning_out
        time afternoon_in
        time afternoon_out
        time evening_in
        time evening_out
        string manual_note
    }

    RFID_CARD {
        int id PK
        string uid
        int teacher_id FK
        string status
        datetime assigned_at
    }

    DEPARTMENT {
        int id PK
        string name
        string name_kh
        string description
        int head_id FK
    }
    
    USER {
        int id PK
        string name
        string email
        string password
    }
    
    SECURITY_LOG {
        int id PK
        string event
        string ip_address
        text details
        datetime created_at
    }
    
    SETTING {
        int id PK
        string key
        text value
    }
EOT;

$encoded = base64_encode(gzcompress($mermaid));
$encoded = str_replace(['+', '/'], ['-', '_'], $encoded);
$url = 'https://kroki.io/mermaid/png/' . $encoded;

echo "Downloading from: $url\n";

$image = file_get_contents($url);
if ($image) {
    file_put_contents('database_diagram.png', $image);
    echo "Success! Diagram saved to: " . getcwd() . DIRECTORY_PATH_SEPARATOR . "database_diagram.png\n";
} else {
    echo "Failed to generate diagram via Kroki API.\n";
}
