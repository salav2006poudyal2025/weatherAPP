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
  const apiKey = "730e06276ae1ea2fc77f8dd5a853494d";
  const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&appid=${apiKey}`;

  try {
    const response = await fetch(apiUrl);
    if (!response.ok) {
      throw new Error("City not found.................!!!");
    }
    const data = await response.json();
    updateWeatherData(data);
  } catch (error) {
    alert(error.message);
  }
}

function updateWeatherData(data) {
  cityName.textContent = `${data.name} ${
    data.sys.country ? `, ${data.sys.country}` : ""
  }`;
  temperature.innerHTML = `${Math.round(data.main.temp)}°C`;

  const iconContainer = document.getElementById("icon-container");
  iconContainer.innerHTML = `<img src="https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png" alt="Weather Icon">`;

  dateElement.textContent = `Date: ${new Date().toISOString().split("T")[0]}`;
  precipitation.textContent = `${data.clouds.all}%`;
  humidity.textContent = `${data.main.humidity}%`;
  wind.textContent = `${data.wind.speed} km/h`;
  airQuality.textContent = "moderate";
}

document.getElementById("current-date").textContent = new Date()
  .toISOString()
  .split("T")[0];

let searchButton = document.getElementById("city-search-weather");
searchButton.addEventListener("click", async () => {
  const city = searchInput.value.trim();
  if (city) {
    await fetchWeatherData(city);
  }
});

searchInput.addEventListener("keypress", async (event) => {
  if (event.key === "Enter") {
    const city = searchInput.value.trim();
    if (city) {
      await fetchWeatherData(city);
    }
  }
});
