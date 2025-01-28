const searchInput = document.getElementById("search");
const cityName = document.getElementById("name");
const temperature = document.getElementById("temperature");
const weatherCondition = document.getElementById("condition");
const dateElement = document.getElementById("date");
const precipitation = document.getElementById("precipitation");
const humidity = document.getElementById("humidity");
const wind = document.getElementById("wind");
const airQuality = document.getElementById("airQuality");

async function fetchWeatherData(city) {
  try {
    if (!navigator.onLine) {
      alert("You are offline. Please check your internet connection.");
      const cachedData = localStorage.getItem(city);
      if (cachedData) {
        // Parse and update with cached data
        updateWeatherData(JSON.parse(cachedData));
      } else {
        alert("No cached data available. Please try again later.");
      }
      return;
    }

    const response = await fetch(
      `http://localhost/Prototype2/connection.php?q=${city}`
    );

    // Check if response is successful
    if (!response.ok) {
      throw new Error("City not found or server error.");
    }

    const data = await response.json();
    console.log("Weather Data:", data);

    // Cache the data in localStorage for offline use
    localStorage.setItem(city, JSON.stringify(data));

    updateWeatherData(data);
  } catch (error) {
    console.error("Error fetching weather data:", error);
    alert("Error fetching weather data. Please check the console for details.");
  }
}

function updateWeatherData(data) {
  cityName.textContent = `${data.city}`;
  temperature.innerHTML = `${Math.round(data.temperature)}°C`;
  weatherCondition.textContent = data.condition;

  const iconContainer = document.getElementById("icon-container");
  iconContainer.innerHTML = `<img src="https://openweathermap.org/img/wn/${data.icon}@2x.png" alt="Weather Icon">`;

  dateElement.textContent = `Date: ${
    new Date(data.date).toISOString().split("T")[0]
  }`;
  precipitation.textContent = `${data.precipitation}%`;
  humidity.textContent = `${data.humidity}%`;
  wind.textContent = `${data.wind} km/h`;
  airQuality.textContent = data.airQuality;
}

// Set current date when the page loads
document.getElementById("current-date").textContent = new Date()
  .toISOString()
  .split("T")[0];

// Event listener for the search button
let searchButton = document.getElementById("city-search-weather");
searchButton.addEventListener("click", async () => {
  const city = searchInput.value.trim();
  if (city) {
    await fetchWeatherData(city);
  } else {
    alert("Please enter a city name.");
  }
});

// Event listener for pressing Enter key in the search input field
searchInput.addEventListener("keypress", async (event) => {
  if (event.key === "Enter") {
    const city = searchInput.value.trim();
    if (city) {
      await fetchWeatherData(city);
    } else {
      alert("Please enter a city name.");
    }
  }
});
