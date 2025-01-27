<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
ini_set('display_errors', 1);
error_reporting(E_ALL);

$serverName = "localhost";
$userName = "root"; 
$password = ""; 
$conn = mysqli_connect($serverName, $userName, $password);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create Database if it doesn't exist
$createDatabase = "CREATE DATABASE IF NOT EXISTS prototype2";
if (!mysqli_query($conn, $createDatabase)) {
    die("Failed to create database: " . mysqli_connect_error());
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

// Execute the query and check for errors
if (!mysqli_query($conn, $createTable)) {
    die("Failed to create table: " . mysqli_error($conn));
}

$cityName = isset($_GET['q']) ? $_GET['q'] : 'Prichard'; // Default city is Prichard

// Check if the data exists for the specified city
$selectAllData = "SELECT * FROM weatherr WHERE city = '$cityName' AND timestamp > NOW() - INTERVAL 2 HOUR";
$result = mysqli_query($conn, $selectAllData);

// If no data, fetch from OpenWeatherMap API
if (mysqli_num_rows($result) == 0) {
    $apiKey = "730e06276ae1ea2fc77f8dd5a853494d"; 
    $url = "https://api.openweathermap.org/data/2.5/weather?q=$cityName&units=metric&appid=$apiKey";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data && $data['cod'] == 200) {
        // Extract weather data
        $temperature = $data['main']['temp'];
        $condition = $data['weather'][0]['description'];
        $precipitation = $data['clouds']['all']; 
        $humidity = $data['main']['humidity'];
        $wind = $data['wind']['speed'];
        $airQuality = "moderate"; 
        $icon = $data['weather'][0]['icon']; 

        // Insert data into the database
        $insertData = "INSERT INTO weatherr (city, temperature, `condition`, precipitation, humidity, wind, air_quality, icon)
                       VALUES ('$cityName', '$temperature', '$condition', '$precipitation', '$humidity', '$wind', '$airQuality', '$icon')";
        if (!mysqli_query($conn, $insertData)) {
            die("Failed to insert data: " . mysqli_error($conn));
        }
    } else {
        die("Error fetching data from OpenWeatherMap API.");
    }
}

// Fetch the latest data for the city
$selectAllData = "SELECT * FROM weatherr WHERE city = '$cityName' ORDER BY timestamp DESC LIMIT 1";
$result = mysqli_query($conn, $selectAllData);
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

// Encode the data as JSON and return it
echo json_encode($responseData);

mysqli_close($conn);
?>
