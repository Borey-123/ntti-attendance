<?php
$ch = curl_init('http://localhost/sana_project/Final_Project/NTTI_Teacher_Attendent/ntti-attendance/public/api/attendance/scan');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['uid' => 'Manual']);
$response = curl_exec($ch);
echo "CURL 1: " . $response . "\n";
curl_close($ch);
