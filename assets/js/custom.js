// custom.js
function playNotify(){
  const a = document.getElementById('notif-audio');
  if(a) a.play().catch(()=>{});
}

setInterval(()=>{
  fetch('/api/check_waiting.php')
    .then(r=>r.json())
    .then(j=>{ if(j.waiting>0){
        // simple visual notification
        if(window.Notification && Notification.permission === 'granted'){
          new Notification('E-Tiketing', { body: 'Ada '+j.waiting+' transaksi menunggu konfirmasi' });
        } else {
          alert('Ada '+j.waiting+' transaksi menunggu konfirmasi!');
        }
        playNotify();
    } });
}, 15000);
