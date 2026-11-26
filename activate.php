<?php
declare(strict_types=1);
?><!doctype html>
<html lang="ru"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Установка или сброс пароля</title>
<style>body{font-family:Arial,sans-serif;background:#F0F7F7;color:#1A2E35;margin:0;padding:24px}.card{max-width:440px;margin:8vh auto;background:white;padding:28px;border-radius:16px;box-shadow:0 12px 48px #0d737722}h2{color:#0A5C5F}label{display:block;margin:14px 0 6px}input{box-sizing:border-box;width:100%;padding:12px;border:1px solid #b8cece;border-radius:8px;font-size:16px}.btn{margin-top:18px;width:100%;padding:12px;border:0;border-radius:8px;background:#0D7377;color:white;font-weight:bold;font-size:16px;cursor:pointer}.muted{color:#5A7A80}.err{color:#b42318}.ok{color:#047857}</style><link rel="stylesheet" href="site-theme.css">
</head>
<body><main class="card"><h2>Установка или смена пароля</h2><p id="who" class="muted">Проверяем ссылку…</p>
<form id="form" hidden><label for="password">Новый пароль (не менее 8 символов)</label><input id="password" type="password" minlength="8" autocomplete="new-password" required><label for="confirm">Повторите пароль</label><input id="confirm" type="password" minlength="8" autocomplete="new-password" required><button class="btn">Сохранить пароль</button></form><p id="msg" role="status"></p></main>
<script>
const token=new URLSearchParams(location.search).get('token')||'';
const who=document.getElementById('who'), form=document.getElementById('form'), msg=document.getElementById('msg');
async function init(){try{const r=await fetch('api.php?action=activate_info&token='+encodeURIComponent(token));const d=await r.json();if(!r.ok)throw Error(d.error||'Ссылка недействительна.');who.textContent='Пользователь: '+d.full_name+' (логин '+d.username+'). Установите новый пароль для входа.';form.hidden=false;}catch(e){who.textContent=e.message;msg.className='err';}}
form.addEventListener('submit',async e=>{e.preventDefault();const p=document.getElementById('password').value;if(p!==document.getElementById('confirm').value){msg.className='err';msg.textContent='Пароли не совпадают.';return;}try{const r=await fetch('api.php?action=activate_complete',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token,password:p})});const d=await r.json();if(!r.ok)throw Error(d.error||'Не удалось сохранить пароль.');form.hidden=true;msg.className='ok';msg.textContent='Пароль сохранён. Теперь можно войти на страницу отчётов.';}catch(e){msg.className='err';msg.textContent=e.message;}});
init();
</script></body></html>