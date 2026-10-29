@once
<style>
.refer-panel {border:1px solid #e8eaee;border-radius:12px;background:#fff;padding:22px;margin-bottom:20px;min-width:0;overflow-wrap:anywhere;}
.refer-panel h2,.refer-panel h3 {margin:0 0 8px;font-size:18px;line-height:1.4;}
.refer-muted {color:#6b7280;font-size:13px;line-height:1.6;}
.refer-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;}
.refer-field label {display:block;font-weight:500;font-size:13px;margin-bottom:6px;}
.refer-field input,.refer-field select,.refer-field textarea,.refer-link {width:100%;min-height:42px;padding:9px 12px;border:1px solid #dce0e5;border-radius:7px;background:#fff;color:#182026;}
.refer-actions {display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:16px;}
.refer-btn {display:inline-flex;justify-content:center;align-items:center;border:0;border-radius:7px;background:#172b35;color:white!important;padding:10px 17px;font-size:13px;cursor:pointer;}
.refer-stats {display:flex;flex-wrap:wrap;gap:12px;margin-bottom:20px;}
.refer-stat {flex:1;min-width:130px;padding:16px;border:1px solid #e8eaee;border-radius:10px;background:#fff;}
.refer-stat strong {display:block;font-size:21px;margin-bottom:3px;}
.refer-table {width:100%;min-width:580px;font-size:13px;}
.refer-table th,.refer-table td {padding:12px;text-align:left;border-bottom:1px solid #edf0f3;vertical-align:top;}
.refer-badge {display:inline-block;border-radius:20px;padding:3px 9px;background:#edf4f2;color:#295b4b;font-size:12px;}
@media(max-width:575px){.refer-panel{padding:16px;}.refer-grid{grid-template-columns:1fr;gap:12px;}.refer-actions .refer-btn{width:100%;}}
</style>
@endonce
