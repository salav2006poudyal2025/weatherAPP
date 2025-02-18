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
        updateWeatherData(JSON.parse(cachedData));
      } else {
        alert("No cached data available. Please try again later.");
      }
      return;
    }

    const apiUrl = `http://sulavprototype3.infy.uk/prototype3/connection.php?q=${city}`;
    const response = await fetch(apiUrl);
    if (!response.ok) {
      throw new Error("Error fetching weather data.");
    }

    const data = await response.json();
    if (data.error) {
      throw new Error(data.error);
    }

    localStorage.setItem(city, JSON.stringify(data));
    updateWeatherData(data);
  } catch (error) {
    console.error("Error fetching weather data:", error);
    alert("Error fetching weather data. Please check the console for details.");
  }
}

function updateWeatherData(data) {
  cityName.textContent = data.city || "Unknown City";
  temperature.innerHTML = `${Math.round(data.temperature)}°C` || "N/A";
  weatherCondition.textContent = data.condition || "N/A";
  document.getElementById(
    "icon-container"
  ).innerHTML = `<img src="https://openweathermap.org/img/wn/${data.icon}@2x.png" alt="Weather Icon">`;
  dateElement.textContent = `Date: ${new Date(data.date).toLocaleDateString()}`;
  precipitation.textContent = `${data.precipitation}%` || "N/A";
  humidity.textContent = `${data.humidity}%` || "N/A";
  wind.textContent = `${data.wind} km/h` || "N/A";
  airQuality.textContent = data.airQuality || "N/A";
}

document.getElementById("current-date").textContent = new Date()
  .toISOString()
  .split("T")[0];

const searchButton = document.getElementById("city-search-weather");
searchButton.addEventListener("click", async () => {
  const city = searchInput.value.trim();
  if (city) {
    await fetchWeatherData(city);
  } else {
    alert("Please enter a city name.");
  }
});

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
