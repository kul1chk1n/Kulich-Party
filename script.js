// ====== НАСТРОЙКИ ПРИГЛАШЕНИЯ ======
const EVENT_DATE = "2026-08-24T18:00:00+09:00";
const MAP_URL = "https://yandex.ru/maps/?text=Якутск";
const HOST_NAME = "[ ИМЯ ]";

document.title = `День рождения — ${HOST_NAME}`;

const unlockBtn = document.getElementById("unlockBtn");
unlockBtn.addEventListener("click", () => {
  document.body.classList.add("unlocked");
  document.querySelector(".intro")?.scrollIntoView({behavior:"smooth"});
});

// Progress bar
const progress = document.querySelector(".progress span");
window.addEventListener("scroll", () => {
  const max = document.documentElement.scrollHeight - innerHeight;
  progress.style.width = `${max > 0 ? (scrollY / max) * 100 : 0}%`;
});

// Reveal animations
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add("visible"); });
},{threshold:.08});
document.querySelectorAll(".reveal").forEach(el => observer.observe(el));

// Countdown
function updateCountdown(){
  const end = new Date(EVENT_DATE).getTime();
  let diff = Math.max(0, end - Date.now());
  const d = Math.floor(diff/86400000); diff -= d*86400000;
  const h = Math.floor(diff/3600000); diff -= h*3600000;
  const m = Math.floor(diff/60000); diff -= m*60000;
  const s = Math.floor(diff/1000);
  const vals = [String(d).padStart(2,"0"),String(h).padStart(2,"0"),String(m).padStart(2,"0"),String(s).padStart(2,"0")];
  document.querySelectorAll("#countdown strong").forEach((el,i)=>el.textContent=vals[i]);
}
updateCountdown(); setInterval(updateCountdown,1000);

// Map
document.getElementById("mapLink").href = MAP_URL;

// Demo RSVP: stores locally. Replace with Telegram/Google Sheets/backend later.
document.getElementById("rsvpForm").addEventListener("submit", e=>{
  e.preventDefault();
  const data = Object.fromEntries(new FormData(e.target).entries());
  data.drinks = [...e.target.querySelectorAll('input[name="drink"]:checked')].map(x=>x.value);
  const saved = JSON.parse(localStorage.getItem("birthday_rsvp") || "[]");
  saved.push({...data, createdAt:new Date().toISOString()});
  localStorage.setItem("birthday_rsvp", JSON.stringify(saved));
  document.getElementById("success").style.display="block";
  e.target.reset();
  document.getElementById("success").scrollIntoView({behavior:"smooth",block:"center"});
});
