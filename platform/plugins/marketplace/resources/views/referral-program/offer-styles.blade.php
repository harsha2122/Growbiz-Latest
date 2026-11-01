@once
<style>
.referral-offer {position:relative;display:grid;grid-template-columns:minmax(0,1fr) 140px;align-items:center;gap:20px;overflow:hidden;padding:18px 22px;margin:18px 0;border:1px solid #eee5ce;border-radius:14px;background:linear-gradient(115deg,#fffbec 0%,#fff5dc 54%,#f9e7e5 100%);color:#17372f;isolation:isolate;}
.referral-offer::before {content:'';position:absolute;width:220px;height:120px;border-radius:50%;background:#efc2ba40;right:-80px;top:-110px;z-index:-1;}
.referral-offer-eyebrow {display:inline-flex;gap:6px;align-items:center;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px;color:#547268;}
.referral-offer h2 {font-size:clamp(22px,2vw,28px);font-weight:700;letter-spacing:-.035em;line-height:1.15;color:#17372f;margin:0 0 6px;overflow-wrap:anywhere;}
.referral-offer-subtitle {font-size:13px;line-height:1.6;color:#657168;margin:0;max-width:440px;}
.referral-offer-friend {font-size:14px;font-weight:500;margin:6px 0 0;color:#17372f;}
.referral-offer-actions {display:flex;flex-wrap:wrap;align-items:center;gap:16px;margin-top:12px;}
.referral-offer-button {display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#f6cc51;color:#17372f!important;border:0;border-radius:9px;padding:9px 14px;font-size:13px;font-weight:600;box-shadow:0 3px 0 #dbb340;cursor:pointer;transition:transform .2s;}
.referral-offer-button:hover {transform:translateY(-2px);}
.referral-offer-terms {font-size:12px;color:#52665e;text-decoration:underline;text-underline-offset:3px;}
.referral-offer-art {width:100%;height:120px;display:block;}
.referral-offer-image {width:100%;height:120px;object-fit:contain;border-radius:14px;}
.referral-art-float {animation:referral-float 4s ease-in-out infinite;transform-origin:center;}
.referral-art-coin {animation:referral-float 4s ease-in-out 1s infinite;}
.referral-offer-unlocked {grid-column:1/-1;display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:12px 16px;background:#ffffffb8;border-radius:10px;font-size:12px;}
.referral-offer-unlocked a {color:#17372f;text-decoration:underline;}
.referral-terms-dialog {position:fixed;inset:0;margin:auto;padding:0;border:0;border-radius:16px;width:min(640px,calc(100% - 32px));max-width:calc(100% - 32px);height:min(72svh,600px);max-height:calc(100svh - 32px);overflow:hidden;box-shadow:0 20px 90px #0004;}
.referral-terms-dialog::backdrop {background:#10251fcc;backdrop-filter:blur(3px);}
.referral-terms-bar {height:58px;padding:0 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #edf0ec;}
.referral-terms-bar button {border:0;background:#f3f5f3;color:#17372f;border-radius:50%;width:32px;height:32px;font-size:22px;line-height:1;}
.referral-terms-dialog iframe {display:block;width:100%;height:calc(100% - 58px);border:0;}
@keyframes referral-float {0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-9px) rotate(2deg)}}
@media(max-width:575px){.referral-offer{grid-template-columns:minmax(0,1fr) 70px;padding:16px;gap:10px;border-radius:14px;}.referral-offer h2{font-size:23px;}.referral-offer-art,.referral-offer-image{height:85px;}.referral-offer-actions{gap:12px;margin-top:10px;}.referral-offer-subtitle{font-size:12px;}.referral-offer-button{font-size:12px;padding:9px 11px;}.referral-terms-dialog{width:calc(100% - 20px);max-width:calc(100% - 20px);height:82svh;border-radius:14px;margin:auto;}.referral-offer-friend{font-size:12px;}}
@media(prefers-reduced-motion:reduce){.referral-art-float,.referral-art-coin{animation:none;}.referral-offer-button{transition:none;}}
</style>
<dialog class="referral-terms-dialog" id="referral-terms-dialog" aria-label="{{ __('Referral terms and conditions') }}">
    <div class="referral-terms-bar"><strong>{{ __('Offer terms & conditions') }}</strong><button type="button" data-close-referral-terms aria-label="{{ __('Close terms') }}">×</button></div>
    <iframe title="{{ __('Full referral offer details') }}"></iframe>
</dialog>
<script>
(function(){
    var dialog=document.getElementById('referral-terms-dialog');var trigger=null;var overflow='';
    document.addEventListener('click',function(event){var link=event.target.closest('[data-referral-terms]');if(!link)return;event.preventDefault();trigger=link;dialog.querySelector('iframe').src=link.href;overflow=document.body.style.overflow;document.body.style.overflow='hidden';dialog.showModal();});
    dialog.querySelector('[data-close-referral-terms]').addEventListener('click',function(){dialog.close();});
    dialog.addEventListener('click',function(event){if(event.target===dialog)dialog.close();});
    dialog.addEventListener('close',function(){dialog.querySelector('iframe').removeAttribute('src');document.body.style.overflow=overflow;if(trigger)trigger.focus();});
})();
</script>
@endonce
