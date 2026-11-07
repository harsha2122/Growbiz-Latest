@once
@php
    $notificationPrefix = ($notificationAudience ?? 'seller') === 'seller' ? 'marketplace.vendor' : 'public';
@endphp
<link rel="manifest" href="{{ asset('growbiz-notifications.webmanifest') }}">
<style>
.growbiz-notification-bell{position:fixed;right:20px;bottom:90px;z-index:1040;display:flex;align-items:center;gap:8px;border:1px solid #e0e6ed;border-radius:28px;padding:12px 16px;background:#fff;color:#183b35;box-shadow:0 4px 18px #122b3020;cursor:pointer;font-size:14px;font-weight:600}
.growbiz-notification-bell span{background:#e67663;color:white;border-radius:20px;min-width:22px;padding:1px 5px;font-size:12px}.growbiz-notification-bell [hidden]{display:none}
.growbiz-notifications{position:fixed;inset:0 0 0 auto;margin:0 0 0 auto;padding:0;border:0;width:min(420px,100%);max-width:100%;height:100dvh;max-height:100dvh;background:#fff;color:#243449;box-shadow:-12px 0 50px #0002}
.growbiz-notifications::backdrop{background:#152e3d60}.growbiz-notification-head{display:flex;align-items:center;justify-content:space-between;padding:18px;border-bottom:1px solid #e5eaf0}.growbiz-notification-head h3{font-size:20px;margin:0}.growbiz-notifications button{cursor:pointer;min-height:40px;border:1px solid #dfe6eb;background:#fff;border-radius:8px;padding:6px 10px;color:#243449;font-size:13px}.growbiz-notification-options{padding:14px 18px;border-bottom:1px solid #e5eaf0;display:flex;flex-wrap:wrap;gap:8px}.growbiz-notification-status{width:100%;font-size:12px;color:#607080;line-height:1.5;margin:0}.growbiz-notification-list{overflow:auto;height:calc(100% - 240px);padding:0 18px}.growbiz-notification-item{display:block;width:100%;text-align:left;border:0!important;border-bottom:1px solid #e5eaf0!important;border-radius:0!important;padding:15px 10px!important;background:#fff!important}.growbiz-notification-item.is-new{background:#f1f7ff!important;border-left:3px solid #4478c8!important}.growbiz-notification-item strong,.growbiz-notification-item small{display:block}.growbiz-notification-item p{font-size:13px;line-height:1.5;margin:6px 0}.growbiz-notification-item small{font-size:11px;color:#68788b}.growbiz-notification-empty{padding:28px 10px;text-align:center;color:#607080}.growbiz-enquiry-nav-badge{display:inline-block;margin-left:6px;padding:1px 6px;border-radius:10px;background:#dfefff;color:#2256a1;font-size:11px}.growbiz-enquiry-summary{display:flex;align-items:center;flex-wrap:wrap;gap:10px;padding:14px 18px;border:1px solid #dce8f6;background:#f4f8ff;border-radius:10px;margin-bottom:20px;font-size:14px}.growbiz-enquiry-summary strong{color:#2256a1}.growbiz-enquiry-summary a{margin-left:auto;text-decoration:underline}
</style>
<button type="button" class="growbiz-notification-bell" id="growbiz-notification-bell" aria-controls="growbiz-notifications" aria-haspopup="dialog"><x-core::icon name="ti ti-bell" /> {{ __('Notifications') }} <span data-notification-badge hidden>0</span></button>
<dialog id="growbiz-notifications" class="growbiz-notifications" aria-labelledby="growbiz-notification-title">
    <div class="growbiz-notification-head"><h3 id="growbiz-notification-title">{{ __('Notifications') }}</h3><button type="button" data-notification-close aria-label="{{ __('Close notifications') }}">×</button></div>
    <div class="growbiz-notification-options">
        <button type="button" data-notification-read>{{ __('Mark all read') }}</button>
        <button type="button" data-notification-sound aria-pressed="false">{{ __('Enable sound') }}</button>
        <button type="button" data-notification-browser>{{ __('Phone alerts') }}</button>
        <p class="growbiz-notification-status" data-notification-status role="status" aria-live="polite">{{ __('The drawer refreshes every 30 seconds. Enable sound for this page, or Phone alerts for supported devices. On iPhone, add Growbiz to Home Screen and open it there first.') }}</p>
    </div>
    <div class="growbiz-notification-list" data-notification-list></div>
</dialog>
<script>
(function () {
    const feedUrl = {{ Illuminate\Support\Js::from(route($notificationPrefix.'.activity-notifications.index')) }};
    const enquiryUrl = {{ Illuminate\Support\Js::from(($notificationAudience ?? 'seller') === 'seller' ? route('marketplace.vendor.enquiries.index') : null) }};
    const readUrl = {{ Illuminate\Support\Js::from(route($notificationPrefix.'.activity-notifications.read')) }};
    const subscribeUrl = {{ Illuminate\Support\Js::from(route($notificationPrefix.'.activity-notifications.subscribe')) }};
    const unsubscribeUrl = {{ Illuminate\Support\Js::from(route($notificationPrefix.'.activity-notifications.unsubscribe')) }};
    const workerUrl = {{ Illuminate\Support\Js::from(asset('growbiz-notifications-sw.js')) }};
    const csrf = {{ Illuminate\Support\Js::from(csrf_token()) }};
    const preferenceKey = {{ Illuminate\Support\Js::from('growbiz.notifications.'.auth('customer')->id().'.'.($notificationAudience ?? 'seller')) }};
    const labels = {{ Illuminate\Support\Js::from(['empty'=>__('No notifications yet.'),'failed'=>__('Could not refresh notifications. Retrying shortly.'),'soundOn'=>__('Sound on'),'soundOff'=>__('Enable sound'),'shortNew'=>__('new'),'total'=>__('total enquiries'),'new'=>__('new enquiries'),'unsupported'=>__('System alerts are unavailable in this browser. The drawer and sound still work while this page is open.'),'permission'=>__('Allow notifications in your browser settings. Alerts work while the website is open.'),'pushUnavailable'=>__('Phone alerts are not available yet. Please contact support.'),'pushEnabled'=>__('Phone alerts enabled on this device. Delivery and sound depend on your browser and phone settings.'),'pushDisabled'=>__('Phone alerts disabled on this device.'),'pushButtonOn'=>__('Disable phone alerts'),'pushButtonOff'=>__('Phone alerts'),'soundFailed'=>__('Tap Enable sound again to allow audio in this browser.')]) }};
    const bell = document.getElementById('growbiz-notification-bell');
    const drawer = document.getElementById('growbiz-notifications');
    const status = drawer.querySelector('[data-notification-status]');
    let through = 0, latest = null, busy = false, timer, audio, overflow, pushKey = null;
    const readPreference = key => { try { return localStorage.getItem(preferenceKey+key); } catch(e) { return null; } };
    const savePreference = (key,value) => { try { localStorage.setItem(preferenceKey+key,value); } catch(e) {} };
    let sound = readPreference('.sound') === 'on';
    const soundButton = drawer.querySelector('[data-notification-sound]');
    function soundLabel() { soundButton.textContent = sound ? labels.soundOn : labels.soundOff; soundButton.setAttribute('aria-pressed',String(sound)); }
    soundLabel();
    async function unlockAudio() {
        try { const Context = window.AudioContext || window.webkitAudioContext; if(!Context) return false; audio ||= new Context(); await audio.resume(); return audio.state === 'running'; } catch(e) { return false; }
    }
    function beep() {
        if(!sound || !audio || audio.state !== 'running') return;
        const o=audio.createOscillator(), g=audio.createGain(); o.connect(g);g.connect(audio.destination);o.frequency.value=740;g.gain.setValueAtTime(.09,audio.currentTime);g.gain.exponentialRampToValueAtTime(.001,audio.currentTime+.22);o.start();o.stop(audio.currentTime+.22);
    }
    document.addEventListener('pointerdown',()=>{if(sound)unlockAudio();},{once:true});
    soundButton.addEventListener('click',async()=>{if(!sound && !await unlockAudio()){status.textContent=labels.soundFailed;return;}sound=!sound;savePreference('.sound',sound?'on':'off');soundLabel();if(sound)beep();});
    const phoneButton=drawer.querySelector('[data-notification-browser]');
    phoneButton.addEventListener('click',async()=>{
        if(!('Notification' in window)||!('serviceWorker' in navigator)||!('PushManager' in window)){status.textContent=labels.unsupported;return;}
        if(!pushKey){status.textContent=labels.pushUnavailable;return;}
        phoneButton.disabled=true;
        try {
            if(Notification.permission!=='granted' && await Notification.requestPermission()!=='granted'){status.textContent=labels.permission;return;}
            const registration=await navigator.serviceWorker.register(workerUrl);
            await navigator.serviceWorker.ready;
            let subscription=await registration.pushManager.getSubscription();
            if(readPreference('.push')==='on'&&subscription){
                const result=await fetch(unsubscribeUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({endpoint:subscription.endpoint})});
                if(!result.ok)throw new Error('unsubscribe failed');await subscription.unsubscribe();savePreference('.push','off');phoneButton.textContent=labels.pushButtonOff;status.textContent=labels.pushDisabled;return;
            }
            if(!subscription){const base64=pushKey.replace(/-/g,'+').replace(/_/g,'/');const bytes=Uint8Array.from(atob(base64+'='.repeat((4-base64.length%4)%4)),c=>c.charCodeAt(0));subscription=await registration.pushManager.subscribe({userVisibleOnly:true,applicationServerKey:bytes});}
            const result=await fetch(subscribeUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(subscription.toJSON())});
            if(!result.ok)throw new Error('subscription failed');savePreference('.push','on');phoneButton.textContent=labels.pushButtonOn;status.textContent=labels.pushEnabled;
        }catch(e){status.textContent=labels.unsupported;}finally{phoneButton.disabled=false;}
    });
    if(readPreference('.push')==='on')phoneButton.textContent=labels.pushButtonOn;
    async function markRead(data) {
        const response=await fetch(readUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(data)});
        if(!response.ok)throw new Error('read failed');
    }
    function render(data) {
        through=data.through;pushKey=data.push_key;
        const badge=bell.querySelector('[data-notification-badge]');badge.textContent=data.unread>99?'99+':data.unread;badge.hidden=!data.unread;
        const summary=document.querySelector('[data-enquiry-summary]');
        if(summary && data.enquiries){summary.querySelector('[data-enquiry-new]').textContent=data.enquiries.new+' '+labels.new;summary.querySelector('[data-enquiry-total]').textContent=data.enquiries.total+' '+labels.total;}
        if(enquiryUrl&&data.enquiries){document.querySelectorAll('a').forEach(link=>{if(link.href!==enquiryUrl)return;let badge=link.querySelector('[data-enquiry-nav-badge]');if(!badge){badge=document.createElement('span');badge.dataset.enquiryNavBadge='';badge.className='growbiz-enquiry-nav-badge';link.append(badge);}badge.textContent=data.enquiries.new+' '+labels.shortNew;badge.hidden=!data.enquiries.new;});}
        const list=drawer.querySelector('[data-notification-list]');list.replaceChildren();
        if(!data.items.length){const p=document.createElement('p');p.className='growbiz-notification-empty';p.textContent=labels.empty;list.append(p);}
        data.items.forEach(item=>{
            const button=document.createElement('button');button.type='button';button.className='growbiz-notification-item'+(item.read?'':' is-new');
            const title=document.createElement('strong'),message=document.createElement('p'),time=document.createElement('small');title.textContent=item.title;message.textContent=item.message;time.textContent=new Date(item.time).toLocaleString();button.append(title,message,time);
            button.addEventListener('click',async()=>{try{await markRead({id:item.id});}catch(e){status.textContent=labels.failed;return;}window.location.assign(item.url);});list.append(button);
        });
    }
    async function refresh() {
        clearTimeout(timer);if(busy)return;if(document.hidden){timer=setTimeout(refresh,30000);return;}busy=true;
        try {
            const response=await fetch(feedUrl,{credentials:'same-origin',headers:{Accept:'application/json'}});
            if(response.status===401||response.status===403){busy=false;return;}
            if(!response.ok)throw new Error('feed failed');const data=await response.json();render(data);
            const additions=latest===null?[]:data.items.filter(i=>i.id>latest&&!i.read);
            if(additions.length){
                const shared=Number(readPreference('.lastAlert')||0);
                if(data.through>shared){savePreference('.lastAlert',String(data.through));beep();

                }
            }
            latest=data.through;
        }catch(e){status.textContent=labels.failed;}finally{busy=false;timer=setTimeout(refresh,30000);}
    }
    bell.addEventListener('click',()=>{if(drawer.open)return;overflow=document.body.style.overflow;document.body.style.overflow='hidden';drawer.showModal();refresh();});
    drawer.querySelector('[data-notification-close]').addEventListener('click',()=>drawer.close());
    drawer.addEventListener('click',event=>{if(event.target===drawer)drawer.close();});
    drawer.addEventListener('close',()=>{document.body.style.overflow=overflow;bell.focus();});
    drawer.querySelector('[data-notification-read]').addEventListener('click',async()=>{try{await markRead({through});refresh();}catch(e){status.textContent=labels.failed;}});
    document.addEventListener('visibilitychange',()=>{if(!document.hidden)refresh();});refresh();
})();
</script>
@endonce
