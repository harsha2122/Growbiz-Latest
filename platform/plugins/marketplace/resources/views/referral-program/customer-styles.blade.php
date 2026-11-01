<style>
.customer-refer-account {min-width:0;}
.customer-refer-account .refer-intro {color:#6b7280;font-size:14px;margin:0 0 24px;line-height:1.6;}
.customer-refer-account .refer-stats {display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:24px;}
.customer-refer-account .refer-stat {min-width:0;padding:20px;border-radius:12px;}
.customer-refer-account .refer-stat:nth-child(2) {background:#f1f8f4;border-color:#dcebe1;}
.customer-refer-account .refer-stat strong {font-size:25px;font-weight:600;color:#172b35;overflow-wrap:anywhere;}
.customer-refer-account .refer-stat .refer-muted {font-size:12px;}
.customer-refer-account .refer-panel {padding:24px;margin-bottom:20px;}
.customer-refer-account .refer-panel h2 {font-size:17px;font-weight:600;}
.customer-refer-account .refer-store-card {margin-top:20px;padding-top:18px;border-top:1px solid #edf0f3;}
.customer-refer-account .refer-store-heading {display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;font-size:14px;}
.customer-refer-account .refer-badge {white-space:nowrap;font-size:11px;}
.customer-refer-account .refer-badge.is-paused {background:#f3f4f6;color:#6b7280;}
.customer-refer-account [data-referral-status="pending"],.customer-refer-account [data-referral-status="tracked"] {background:#fff7e7;color:#8a6222;}
.customer-refer-account [data-referral-status="rejected"],.customer-refer-account [data-referral-status="reversed"] {background:#fdf0ef;color:#a3443b;}
.customer-refer-account .refer-copy-row {display:flex;gap:8px;align-items:center;}
.customer-refer-account .refer-copy-row .refer-link {flex:1;min-width:0;margin:0!important;height:42px;font-size:12px;background:#f8f9fb;text-overflow:ellipsis;}
.customer-refer-account .refer-copy-row .refer-btn {flex-shrink:0;margin:0!important;min-height:42px;padding:10px 16px;font-size:12px;}
.customer-refer-account .refer-empty {text-align:center;padding:28px 12px;display:flex;flex-direction:column;align-items:center;color:#6b7280;}
.customer-refer-account .refer-empty>.icon {width:28px;height:28px;color:#d77d68;margin-bottom:12px;}
.customer-refer-account .refer-empty p {color:#172b35;font-weight:500;margin:0 0 6px;font-size:14px;}
.customer-refer-account .refer-empty span {font-size:12px;line-height:1.6;max-width:320px;}
.customer-refer-account .refer-table {font-size:12px;}
.customer-refer-account .refer-table th {color:#6b7280;font-weight:500;font-size:11px;}
.customer-refer-account .refer-table td {padding:15px 10px;}
@media(max-width:575px){
.customer-refer-account .refer-intro {font-size:13px;margin-bottom:18px;}
.customer-refer-account .refer-stats {gap:7px;margin-bottom:18px;}
.customer-refer-account .refer-stat {padding:13px 9px;}
.customer-refer-account .refer-stat strong {font-size:18px;}
.customer-refer-account .refer-stat .refer-muted {font-size:11px;}
.customer-refer-account .refer-panel {padding:16px;}
.customer-refer-account .refer-copy-row .refer-btn {padding:10px 12px;}
.customer-refer-account .refer-table {min-width:0;}
.customer-refer-account .refer-table thead {position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;}
.customer-refer-account .refer-table,.customer-refer-account .refer-table tbody {display:block;width:100%;}
.customer-refer-account .refer-table tr {display:grid;grid-template-columns:1fr 1fr;border-top:1px solid #edf0f3;padding:12px 0;gap:8px;}
.customer-refer-account .refer-table td {display:block;padding:0;border:0;min-width:0;overflow-wrap:anywhere;}
.customer-refer-account .refer-table td[data-label]::before {content:attr(data-label);display:block;font-size:10px;color:#6b7280;margin-bottom:4px;}
.customer-refer-account .refer-table td:nth-child(2) {text-align:right;}
.customer-refer-account .refer-table td:last-child {grid-column:1/-1;}
.customer-refer-account .refer-table td[colspan] {grid-column:1/-1;text-align:center;padding:16px 0;}
}
</style>
