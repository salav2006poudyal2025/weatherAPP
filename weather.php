<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$serverName = "localhost";
$userName = "root"; 
$password = ""; 
$conn = mysqli_connect($serverName, $userName, $password);

if (!$conn) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed."]));
}

// Create Database if it doesn't exist
$createDatabase = "CREATE DATABASE IF NOT EXISTS prototype2";
if (!mysqli_query($conn, $createDatabase)) {
    http_response_code(500);
    die(json_encode(["error" => "Failed to create database."]));
}

// Select the database
mysqli_select_db($conn, 'prototype2');

// Create table if it doesn't exist
$createTable = "CREATE TABLE IF NOT EXISTS weatherr (
    city VARCHAR(255) NOT NULL,
    temperature FLOAT NOT NULL,
    `condition` VARCHAR(255) NOT NULL, 
    precipitation FLOAT NOT NULL,
    humidity FLOAT NOT NULL,
    wind FLOAT NOT NULL,
    air_quality VARCHAR(50) NOT NULL,
    icon VARCHAR(255) NOT NULL, 
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if (!mysqli_query($conn, $createTable)) {
    http_response_code(500);
    die(json_encode(["error" => "Failed to create table."]));
}

// Get city from query parameter
$cityName = isset($_GET['q']) ? $_GET['q'] : 'Prichard';
$cityName = mysqli_real_escape_string($conn, $cityName);

// Check for cached data in the database
$selectData = "SELECT * FROM weatherr WHERE city = '$cityName' AND timestamp > NOW() - INTERVAL 2 HOUR";
$result = mysqli_query($conn, $selectData);

// If no cached data, fetch from OpenWeatherMap API
if (mysqli_num_rows($result) == 0) {
    $apiKey = "730e06276ae1ea2fc77f8dd5a853494d";
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$cityName&units=metric&appid=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data && $data['cod'] == 200) {
        $temperature = round($data['main']['temp']);
        $condition = $data['weather'][0]['description'];
        $precipitation = $data['clouds']['all'];
        $humidity = $data['main']['humidity'];
        $wind = $data['wind']['speed'];
        $airQuality = "Good"; // Static value; replace with real API data if available
        $icon = $data['weather'][0]['icon'];

        $insertData = "INSERT INTO weatherr (city, temperature, `condition`, precipitation, humidity, wind, air_quality, icon)
                       VALUES ('$cityName', '$temperature', '$condition', '$precipitation', '$humidity', '$wind', '$airQuality', '$icon')";
        if (!mysqli_query($conn, $insertData)) {
            http_response_code(500);
            die(json_encode(["error" => "Failed to insert data."]));
        }
    } else {
        http_response_code(404);
        die(json_encode(["error" => "City not found."]));
    }
}

// Fetch the latest data for the city
$selectData = "SELECT * FROM weatherr WHERE city = '$cityName' ORDER BY timestamp DESC LIMIT 1";
$result = mysqli_query($conn, $selectData);
$weatherData = mysqli_fetch_assoc($result);

$responseData = [
    "city" => $weatherData['city'],
    "temperature" => $weatherData['temperature'],
    "condition" => $weatherData['condition'],
    "precipitation" => $weatherData['precipitation'],
    "humidity" => $weatherData['humidity'],
    "wind" => $weatherData['wind'],
    "airQuality" => $weatherData['air_quality'],
    "icon" => $weatherData['icon'],
    "date" => $weatherData['timestamp']
];

echo json_encode($responseData);
mysqli_close($conn);
?>
