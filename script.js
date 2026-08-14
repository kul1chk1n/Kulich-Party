const EVENT=new Date('2026-08-25T16:00:00+09:00');
const music=document.getElementById('music');
const lock=document.getElementById('lockScreen');
const content=document.getElementById('content');
document.getElementById('unlock').addEventListener('click',async()=>{try{await music.play()}catch(e){}lock.classList.add('opening');setTimeout(()=>{lock.style.display='none';content.classList.remove('hidden');document.querySelector('.hero').classList.add('visible');},650)});
function tick(){let x=Math.max(0,EVENT-Date.now());let d=Math.floor(x/864e5);x%=864e5;let h=Math.floor(x/36e5);x%=36e5;let m=Math.floor(x/6e4);let s=Math.floor((x%6e4)/1e3);document.getElementById('d').textContent=String(d).padStart(2,'0');document.getElementById('h').textContent=String(h).padStart(2,'0');document.getElementById('m').textContent=String(m).padStart(2,'0');document.getElementById('s').textContent=String(s).padStart(2,'0')}tick();setInterval(tick,1000);
const obs=new IntersectionObserver(es=>es.forEach(e=>e.isIntersecting&&e.target.classList.add('visible')),{threshold:.08});document.querySelectorAll('.reveal').forEach(x=>obs.observe(x));
addEventListener('scroll',()=>{let max=document.documentElement.scrollHeight-innerHeight;document.querySelector('.scrollbar span').style.height=(max?scrollY/max*100:0)+'%'});
document.querySelectorAll('input[name=plus_one]').forEach(r=>r.addEventListener('change',()=>document.getElementById('plusName').classList.toggle('show',r.checked&&r.value==='Да')));

document.getElementById('rsvp').addEventListener('submit', async e => {
  e.preventDefault();
  const form = e.target;
  const button = form.querySelector('button[type="submit"]');
  const ok = document.getElementById('ok');
  const data = Object.fromEntries(new FormData(form).entries());
  data.drinks = [...form.querySelectorAll('input[name="drink"]:checked')].map(x => x.value);
  data.plus_one_fee = data.plus_one === 'Да' ? 1000 : 0;

  button.disabled = true;
  const originalText = button.textContent;
  button.textContent = 'ОТПРАВЛЯЕМ…';
  ok.style.display = 'none';

  try {
    const response = await fetch('api.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(data)
    });
    const result = await response.json().catch(() => ({}));
    if (!response.ok || !result.ok) {
      throw new Error(result.error || 'server_error');
    }

    ok.textContent = 'Спасибо! Анкета принята. До встречи!';
    ok.style.display = 'block';
    form.reset();
    document.getElementById('plusName').classList.remove('show');
    ok.scrollIntoView({behavior: 'smooth', block: 'center'});
  } catch (err) {
    console.error(err);
    ok.textContent = 'Не удалось отправить анкету. Попробуйте ещё раз.';
    ok.style.display = 'block';
  } finally {
    button.disabled = false;
    button.textContent = originalText;
  }
});
