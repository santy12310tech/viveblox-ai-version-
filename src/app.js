const games = [
  {name:"ViveBlox World", players:"Alpha 1.1 · Single-player", icon:"◆", playable:true},
  {name:"Block Valley", players:"Servidor demo · Próximamente", icon:"⬢", playable:false},
  {name:"Sky Islands", players:"Próximamente", icon:"◇", playable:false},
  {name:"City Life", players:"Próximamente", icon:"▣", playable:false}
];

const gamesEl = document.querySelector("#games");
const modal = document.querySelector("#modal");
const launch = document.querySelector("#launch");

function openModal(game) {
  modal.classList.remove("hidden");
  modal.querySelector("h2").textContent = game.name;
  launch.disabled = !game.playable;
  launch.textContent = game.playable ? "Entrar al mundo" : "Próximamente";
  launch.onclick = () => {
    if (game.playable) location.href = "client/index.html";
  };
}

games.forEach((game) => {
  const el = document.createElement("article");
  el.className = "game";
  el.innerHTML = `<div class="thumb">${game.icon}</div><div class="game-info"><b>${game.name}</b><small>${game.players}</small></div>`;
  el.onclick = () => openModal(game);
  gamesEl.appendChild(el);
});

document.querySelector("#play").onclick = () => openModal(games[0]);
document.querySelector("#close").onclick = () => modal.classList.add("hidden");

modal.addEventListener("click", (event) => {
  if (event.target === modal) modal.classList.add("hidden");
});

document.querySelector("#search").addEventListener("input", (event) => {
  const q = event.target.value.toLowerCase();
  document.querySelectorAll(".game").forEach((card, i) => {
    card.style.display = games[i].name.toLowerCase().includes(q) ? "" : "none";
  });
});