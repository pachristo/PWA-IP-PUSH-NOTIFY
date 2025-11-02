<div class="pwa-pachristo-xloax">
  <div x-data="{open:false}" x-cloak>
    <button @click="open=true" class="btn">Enable Push Notifications</button>

    <div x-show="open" class="modal" @click.away="open=false">
      <div class="card" @click.stop>
        <div class="header"><h2>PWA Push Notify</h2><a href="#" @click.prevent="open=false" class="close">×</a></div>
        <div class="body">
          <p>No login • Offline • IP-based Push</p>
          <button x-ref="install" @click="install()" id="install-btn">Install</button>
          <button @click="subscribe()" id="notify-btn">Allow Push</button>
          <div x-text="msg"></div>
        </div>
        <div class="footer">Free forever</div>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let p; window.addEventListener('beforeinstallprompt',e=>{e.preventDefault();p=e;document.getElementById('install-btn').style.display='inline-block';});
async function install(){p.prompt();const {outcome}=await p.userChoice;$dispatch('msg', outcome==='accepted'?'Installed!':'Cancelled');}
async function subscribe(){
  const reg=await navigator.serviceWorker.register('/pwa-push/sw.js');
  const {key}=await (await fetch('/pwa-push/vapid')).json();
  const sub=await reg.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:key});
  await fetch('/pwa-push/subscribe',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(sub.toJSON())});
  document.getElementById('notify-btn').style.display='none';
  $dispatch('msg','Test push in 3s…');
  setTimeout(()=>location.href='/pwa-push/send',3000);
}
if('serviceWorker'in navigator)navigator.serviceWorker.register('/pwa-push/sw.js');
</script>
@endpush
