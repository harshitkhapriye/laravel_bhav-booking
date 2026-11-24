<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* JU Order pipeline — new UI. Ported 1:1 from the approved design (classes prefixed jp-). */
.jp-root { --bg: #FFFFFF; --surface: #FFFFFF; --col: #FFFFFF; --colhead: #FFFFFF; --line: #ECEEF2; --dash: #D9DDE3; --text: #1F2937; --muted: #6B7280; --soft: #A3A3A3; --orange: #F07A1A; --orange-dark: #D9620A; --orange-soft: #FFE7D1; --navy: #F07A1A; --gold: #A9790B; --gold-bg: #FDF3D7; --silver: #55606E; --silver-bg: #EDF0F4; --blue: #D9620A; --purple: #F07A1A; --green: #16A34A; --red: #DC2626; --shadow: 0 2px 10px rgba(240,122,26,.08) }
.jp-root * { box-sizing: border-box; }
.jp-root {  color: var(--text); font-family: Outfit, system-ui, -apple-system, "Segoe UI", sans-serif }
.jp-root b, .jp-root strong { font-weight: 700; }
.jp-root * { scrollbar-width: thin; scrollbar-color: rgb(245, 180, 126) transparent; }
.jp-root .jp-om-body, .jp-root .jp-itwrap, .jp-root .jp-dtbl-wrap, .jp-root .jp-modal { overscroll-behavior: contain; }
.jp-root button { font-family: inherit; cursor: pointer; }
.jp-root .jp-btn-new { display: flex; align-items: center; gap: 6px; border: 1px solid var(--orange); background: var(--orange); color: rgb(255, 255, 255); font-weight: 700; padding: 9px 16px; border-radius: 999px; font-size: 13px; box-shadow: rgba(240, 122, 26, 0.3) 0px 2px 8px; }
.jp-root .jp-board-wrap { padding: 8px 24px 16px; max-width: 1340px; margin: 0px auto; width: 100%; flex: 1 1 0%; min-height: 0px; display: flex; flex-direction: column; box-sizing: border-box; }
.jp-root .jp-board { display: grid; grid-template-columns: repeat(3, minmax(300px, 1fr)); gap: 22px; overflow-x: auto; padding: 0px 2px 2px; flex: 1 1 0%; min-height: 0px; grid-template-rows: minmax(420px, 1fr); }
.jp-root .jp-col { position: relative; background: rgb(255, 255, 255); border: 1px solid var(--line); border-radius: 16px; height: auto; min-height: 420px; display: flex; flex-direction: column; padding: 12px 6px 12px 12px; transition: background 0.15s, border-color 0.15s; }
.jp-root .jp-col-head { flex-shrink: 0; margin-right: 6px !important; }
.jp-root .jp-col-list { flex: 1 1 0%; min-height: 0px; overflow-y: auto; padding: 2px 6px 6px 0px; scrollbar-gutter: stable; overscroll-behavior: contain; }
.jp-root .jp-col:not(:last-child)::after { content: "➜"; position: absolute; right: -17px; top: 30px; width: 22px; height: 22px; border-radius: 50%; background: var(--orange); color: rgb(255, 255, 255); display: grid; place-items: center; font-size: 11px; z-index: 3; box-shadow: rgba(240, 122, 26, 0.4) 0px 2px 6px; }
.jp-root .jp-col.jp-over { background: rgb(255, 243, 232); border-color: var(--orange); box-shadow: rgba(240, 122, 26, 0.15) 0px 0px 0px 3px; }
.jp-root .jp-col[data-stage="karigar"].jp-over { background: rgb(243, 238, 255); border-color: rgb(139, 92, 246); box-shadow: rgba(139, 92, 246, 0.15) 0px 0px 0px 3px; }
.jp-root .jp-col-head { display: flex; align-items: center; gap: 10px; background: rgb(255, 255, 255); border-top-color: ; border-top-style: ; border-top-width: ; border-right-color: ; border-right-style: ; border-right-width: ; border-left-color: ; border-left-style: ; border-left-width: ; border-image-source: ; border-image-slice: ; border-image-width: ; border-image-outset: ; border-image-repeat: ; border-bottom: 3px solid var(--orange); border-radius: 12px; padding: 12px 14px; margin-bottom: 14px; box-shadow: var(--shadow); }
.jp-root .jp-col[data-stage="karigar"] .jp-col-head { border-bottom-color: rgb(139, 92, 246); }
.jp-root .jp-col[data-stage="karigar"] .jp-stg-ic { background: rgb(237, 233, 254); }
.jp-root .jp-col[data-stage="karigar"] .jp-count { background: rgb(139, 92, 246); }
.jp-root .jp-col[data-stage="billing"] .jp-count { background: rgb(22, 163, 74); }
.jp-root .jp-col[data-stage="billing"] .jp-col-head { border-bottom-color: var(--green); }
.jp-root .jp-col-head h2 { font-size: 15px; margin: 0px; font-weight: 700; letter-spacing: 0.2px; display: flex; align-items: center; gap: 8px; }
.jp-root .jp-stg-ic { width: 40px; height: 40px; border-radius: 11px; background: var(--orange-soft); display: grid; place-items: center; font-size: 19px; flex: 0 0 40px; }
.jp-root .jp-col[data-stage="billing"] .jp-stg-ic { background: rgb(220, 252, 231); }
.jp-root .jp-stg-t small { display: block; font-size: 11.5px; color: var(--muted); margin-top: 2px; font-weight: 500; }
.jp-root .jp-col-head h2 { font-weight: 800; }
.jp-root .jp-stats { display: flex; flex-wrap: wrap; gap: 8px; margin-left: auto; }
.jp-root .jp-stat { display: flex; align-items: center; gap: 8px; background: rgb(255, 255, 255); border: 1px solid var(--line); border-radius: 10px; padding: 5px 10px 5px 6px; box-shadow: var(--shadow); }
.jp-root .jp-stat .jp-si { width: 28px; height: 28px; border-radius: 8px; background: var(--orange-soft); display: grid; place-items: center; font-size: 14px; flex: 0 0 28px; }
.jp-root .jp-stat b { display: block; font-size: 14px; line-height: 1.1; font-weight: 800; }
.jp-root .jp-stat span { font-size: 10.5px; color: var(--muted); font-weight: 600; white-space: nowrap; }
.jp-root .jp-stat.jp-red { background: linear-gradient(135deg, rgb(255, 241, 241), rgb(255, 255, 255)); border-color: rgb(254, 202, 202); }
.jp-root .jp-stat.jp-red .jp-si { background: rgb(254, 226, 226); }
.jp-root .jp-stat.jp-red b { color: rgb(220, 38, 38); }
.jp-root .jp-count { background: var(--orange); color: rgb(255, 255, 255); font-weight: 700; font-size: 12px; border-radius: 6px; padding: 2px 8px; }
.jp-root .jp-col-sum { margin-left: auto; text-align: right; font-size: 12px; line-height: 1.35; color: var(--muted); }
.jp-root .jp-col-sum strong { color: var(--text); font-weight: 700; }
.jp-root .jp-csum3 { display: flex; flex-direction: column; align-items: flex-end; gap: 0px; font-size: 9.5px; line-height: 1.25; color: var(--muted); }
.jp-root .jp-csum3 span { white-space: nowrap; }
.jp-root .jp-csum3 strong { font-size: 10.5px; margin-left: 3px; font-weight: 800; color: rgb(17, 24, 39) !important; }
.jp-root .jp-csum3 .jp-cp strong { color: var(--green) !important; }
.jp-root .jp-csum3 .jp-cr strong { color: rgb(220, 38, 38) !important; }
.jp-root .jp-csum3 .jp-ck strong { color: rgb(124, 58, 237) !important; }
.jp-root .jp-col-head { min-height: 68px; box-sizing: border-box; }
.jp-root .jp-plus { width: 32px; height: 32px; border-radius: 50%; border: 0px; background: var(--orange); color: rgb(255, 255, 255); font-size: 20px; line-height: 1; display: grid; place-items: center; margin-left: 8px; box-shadow: rgba(240, 122, 26, 0.4) 0px 2px 6px; }
.jp-root .jp-card { background: var(--surface); border-right-color: ; border-right-style: ; border-right-width: ; border-bottom-color: ; border-bottom-style: ; border-bottom-width: ; border-left-color: ; border-left-style: ; border-left-width: ; border-image-source: ; border-image-slice: ; border-image-width: ; border-image-outset: ; border-image-repeat: ; border-top: 3px solid rgb(224, 165, 38); border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; box-shadow: var(--shadow); cursor: grab; position: relative; }
.jp-root .jp-card[data-stage="order"] { border-top-color: rgb(240, 122, 26); background: rgb(255, 255, 255); }
.jp-root .jp-card[data-stage="karigar"] { border-top-color: rgb(139, 92, 246); background: rgb(255, 255, 255); }
.jp-root .jp-card[data-stage="billing"] { border-top-color: rgb(22, 163, 74); background: rgb(255, 255, 255); }
.jp-root .jp-card[data-stage="karigar"] .jp-av { background: linear-gradient(135deg, rgb(139, 92, 246), rgb(196, 181, 253)); }
.jp-root .jp-card[data-stage="billing"] .jp-av { background: linear-gradient(135deg, rgb(22, 163, 74), rgb(134, 239, 172)); }
.jp-root .jp-card[data-stage="karigar"] .jp-prod { color: rgb(124, 58, 237); }
.jp-root .jp-card[data-stage="billing"] .jp-prod { color: rgb(21, 128, 61); }
.jp-root .jp-card[data-stage="karigar"] .jp-act.jp-green { background: rgb(139, 92, 246); border-color: rgb(139, 92, 246); }
.jp-root .jp-card[data-stage="billing"] .jp-act.jp-green { background: rgb(22, 163, 74); border-color: rgb(22, 163, 74); }
.jp-root .jp-card[data-stage="karigar"]:hover { box-shadow: rgba(139, 92, 246, 0.15) 0px 6px 18px; }
.jp-root .jp-card[data-stage="billing"]:hover { box-shadow: rgba(22, 163, 74, 0.15) 0px 6px 18px; }
.jp-root .jp-card:hover { border-left-color: var(--dash); border-right-color: var(--dash); border-bottom-color: var(--dash); box-shadow: rgba(240, 122, 26, 0.12) 0px 6px 18px; }
.jp-root .jp-card.jp-dragging { opacity: 0.45; }
.jp-root .jp-card-top { display: flex; align-items: center; gap: 8px; }
.jp-root .jp-sno { font-size: 12px; color: var(--muted); font-weight: 700; }
.jp-root .jp-metal { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; }
.jp-root .jp-metal.jp-gold { background: var(--gold-bg); color: var(--gold); }
.jp-root .jp-metal.jp-silver { background: var(--silver-bg); color: var(--silver); }
.jp-root .jp-dots { margin-left: auto; border: 0px; background: transparent; color: var(--muted); font-size: 18px; width: 28px; height: 28px; border-radius: 6px; }
.jp-root .jp-card-top .jp-act { margin-left: auto; white-space: nowrap; flex: 0 0 auto; font-size: 10.5px; padding: 3px 8px; border-radius: 6px; line-height: 1.3; }
.jp-root .jp-card-top .jp-act + .jp-dots { margin-left: 0px; }
.jp-root .jp-card-top .jp-cust { flex: 1 1 0%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.jp-root .jp-dots:hover { background: var(--orange-soft); }
.jp-root .jp-menu { position: absolute; right: 12px; top: 40px; background: var(--surface); border: 1px solid var(--line); border-radius: 10px; box-shadow: rgba(0, 0, 0, 0.12) 0px 8px 24px; z-index: 5; min-width: 130px; padding: 4px; display: none; }
.jp-root .jp-menu.jp-open { display: block; animation: 0.18s ease 0s 1 normal none running jp-menuIn; transform-origin: right top; }
@keyframes jp-menuIn { 
  0% { opacity: 0; transform: translateY(-6px) scale(0.96); }
  100% { opacity: 1; transform: none; }
}
.jp-root .jp-menu button { transition: background 0.15s, color 0.15s, padding-left 0.15s; }
.jp-root .jp-menu button:hover { padding-left: 16px; }
.jp-root .jp-menu button { display: block; width: 100%; text-align: left; border: 0px; background: transparent; color: var(--text); padding: 8px 12px; border-radius: 6px; font-size: 13px; }
.jp-root .jp-menu button:hover { background: var(--orange-soft); }
.jp-root .jp-menu button.jp-del { color: var(--red); }
.jp-root .jp-cust { margin: 0px; font-weight: 800; font-size: 15px; line-height: 1.2; min-width: 0px; }
.jp-root .jp-cust small { font-weight: 500; }
.jp-root .jp-cust small { display: block; font-weight: 400; color: var(--muted); font-size: 12px; }
.jp-root .jp-prod { color: var(--blue); font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.2px; }
.jp-root .jp-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0px 12px; margin-top: 10px; padding-top: 7px; border-top: 1px dashed var(--line); }
.jp-root .jp-f span { display: block; font-size: 11px; color: var(--soft); font-weight: 600; }
.jp-root .jp-f b { font-weight: 700; font-size: 14px; }
.jp-root .jp-f .jp-plus-amt { color: var(--green); font-weight: 800; }
.jp-root .jp-f .jp-minus-amt { color: var(--red); font-weight: 800; }
.jp-root .jp-act { border: 1px solid var(--orange); background: var(--orange-soft); color: var(--orange-dark); font-weight: 600; font-size: 12px; padding: 5px 10px; border-radius: 7px; }
.jp-root .jp-act:hover { background: var(--orange); color: rgb(255, 255, 255); }
.jp-root .jp-act.jp-green { border-color: var(--orange); background: var(--orange); color: rgb(255, 255, 255); }
.jp-root .jp-hint { display: none; }
.jp-root .jp-mid { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin: 10px 0px 4px; }
.jp-root .jp-mid .jp-prod { flex: 1 1 0%; min-width: 0px; padding-top: 2px; line-height: 1.35; }
.jp-root .jp-mid .jp-ml { flex: 1 1 0%; min-width: 0px; }
.jp-root .jp-mid .jp-tags { flex-direction: column; align-items: flex-end; gap: 4px; margin: 0px; flex-shrink: 0; }
.jp-root .jp-col-head .jp-stg-ic, .jp-root .jp-col-head .jp-stg-t small { display: none; }
.jp-root .jp-col[data-stage="order"] .jp-col-head { background: rgb(255, 241, 228); }
.jp-root .jp-col[data-stage="karigar"] .jp-col-head { background: rgb(243, 238, 255); }
.jp-root .jp-col[data-stage="billing"] .jp-col-head { background: rgb(233, 251, 239); }
.jp-root .jp-col-head .jp-col-sum strong { color: rgb(17, 24, 39); }
.jp-root .jp-card .jp-grid .jp-f { padding: 3px 0px; }
.jp-root .jp-card .jp-grid .jp-f:nth-child(n+3) { max-height: 0px; opacity: 0; padding: 0px; overflow: hidden; transform: translateY(-4px); transition: max-height 0.35s, opacity 0.25s, padding 0.3s, transform 0.3s; }
.jp-root .jp-card.jp-open .jp-grid .jp-f:nth-child(n+3) { max-height: 70px; opacity: 1; padding: 3px 0px; transform: none; }
.jp-root .jp-card .jp-grid .jp-f:nth-child(-n+2) { cursor: pointer; }
.jp-root .jp-card[data-stage="karigar"] .jp-dtg { color: rgb(124, 58, 237); }
.jp-root .jp-card[data-stage="karigar"] .jp-dtg:hover { background: rgb(243, 238, 255); border-color: rgb(139, 92, 246); }
.jp-root .jp-card[data-stage="karigar"].jp-open .jp-dtg { background: rgb(139, 92, 246); border-color: rgb(139, 92, 246); color: rgb(255, 255, 255); }
.jp-root .jp-card[data-stage="billing"] .jp-dtg { color: rgb(21, 128, 61); }
.jp-root .jp-card[data-stage="billing"] .jp-dtg:hover { background: rgb(236, 253, 243); border-color: rgb(22, 163, 74); }
.jp-root .jp-card[data-stage="billing"].jp-open .jp-dtg { background: rgb(22, 163, 74); border-color: rgb(22, 163, 74); color: rgb(255, 255, 255); }
.jp-root .jp-ftot { position: relative; padding-right: 34px; }
.jp-root .jp-dtg { position: absolute; right: 0px; top: 50%; transform: translateY(-50%); width: 26px; height: 26px; border-radius: 50%; border: 1px solid var(--line); background: rgb(255, 255, 255); color: var(--orange-dark); font-size: 13px; font-weight: 800; line-height: 1; display: grid; place-items: center; transition: transform 0.25s, background 0.2s, border-color 0.2s; }
.jp-root .jp-dtg:hover { background: var(--orange-soft); border-color: var(--orange); }
.jp-root .jp-card.jp-open .jp-dtg { transform: translateY(-50%) rotate(180deg); background: var(--orange); border-color: var(--orange); color: rgb(255, 255, 255); }
@keyframes jp-cardDrop { 
  0% { opacity: 0; transform: translateY(-6px); }
  100% { opacity: 1; transform: none; }
}
.jp-root .jp-itact { border: 1px solid var(--line); background: rgb(255, 255, 255); border-radius: 8px; width: 30px; height: 30px; font-size: 16px; font-weight: 800; color: var(--muted); transition: 0.2s; }
.jp-root .jp-itact:hover { border-color: var(--orange); color: var(--orange-dark); background: var(--orange-soft); }
.jp-root .jp-menu.jp-itm { position: fixed; right: auto; min-width: 150px; z-index: 70; }
.jp-root .jp-col[data-stage="order"] .jp-col-list { scrollbar-color: rgb(247, 181, 122) transparent; }
.jp-root .jp-col[data-stage="order"] .jp-col-list::-webkit-scrollbar-thumb { background: padding-box rgb(247, 181, 122); }
.jp-root .jp-col[data-stage="order"] .jp-col-list::-webkit-scrollbar-thumb:hover { background: padding-box rgb(240, 122, 26); }
.jp-root .jp-col[data-stage="karigar"] .jp-col-list { scrollbar-color: rgb(196, 181, 253) transparent; }
.jp-root .jp-col[data-stage="karigar"] .jp-col-list::-webkit-scrollbar-thumb { background: padding-box rgb(196, 181, 253); }
.jp-root .jp-col[data-stage="karigar"] .jp-col-list::-webkit-scrollbar-thumb:hover { background: padding-box rgb(139, 92, 246); }
.jp-root .jp-col[data-stage="billing"] .jp-col-list { scrollbar-color: rgb(134, 239, 172) transparent; }
.jp-root .jp-col[data-stage="billing"] .jp-col-list::-webkit-scrollbar-thumb { background: padding-box rgb(134, 239, 172); }
.jp-root .jp-col[data-stage="billing"] .jp-col-list::-webkit-scrollbar-thumb:hover { background: padding-box rgb(22, 163, 74); }
.jp-root .jp-overlay { position: fixed; inset: 0px; background: rgba(15, 18, 30, 0.45); display: none; align-items: center; justify-content: center; z-index: 50; padding: 16px; }
.jp-root .jp-overlay.jp-open { display: flex; animation: 0.2s ease 0s 1 normal none running jp-ovIn; }
.jp-root .jp-overlay.jp-open .jp-modal { animation: 0.28s cubic-bezier(0.2, 0.9, 0.3, 1.2) 0s 1 normal none running jp-mdIn; }
.jp-root .jp-dov.jp-open { animation: 0.2s ease 0s 1 normal none running jp-ovIn; }
@keyframes jp-ovIn { 
  0% { opacity: 0; }
  100% { opacity: 1; }
}
@keyframes jp-ovOut { 
  100% { opacity: 0; }
}
@keyframes jp-mdIn { 
  0% { opacity: 0; transform: translateY(14px) scale(0.97); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-mdOut { 
  100% { opacity: 0; transform: translateY(10px) scale(0.98); }
}
@keyframes jp-mdSwap { 
  0% { opacity: 0.4; transform: scale(0.985); }
  100% { opacity: 1; transform: none; }
}
.jp-root button { transition: background-color 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s, transform 0.15s; }
.jp-root button:active { transform: scale(0.97); }
.jp-root .jp-card { transition: box-shadow 0.25s, transform 0.25s, border-color 0.25s; }
.jp-root .jp-card:hover { transform: translateY(-2px); z-index: 3; }
.jp-root .jp-card:has(.jp-menu.jp-open) { z-index: 4; }
.jp-root .jp-col-head, .jp-root .jp-stat, .jp-root .jp-ds, .jp-root .jp-va { transition: box-shadow 0.25s, transform 0.25s; }
.jp-root .jp-pc, .jp-root .jp-metal { transition: transform 0.2s; }
.jp-root .jp-card:hover .jp-pc { transform: translateX(-1px); }
.jp-root .jp-modal { background: var(--surface); border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow: auto; box-shadow: rgba(0, 0, 0, 0.25) 0px 20px 50px; }
.jp-root .jp-modal-h { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--line); }
.jp-root .jp-modal-h h3 { margin: 0px; font-size: 18px; }
.jp-root .jp-x { border: 0px; background: var(--orange-soft); color: var(--text); width: 32px; height: 32px; border-radius: 8px; font-size: 16px; }
.jp-root .jp-modal-b { padding: 18px 20px; }
.jp-root .jp-modal-f { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--line); }
.jp-root .jp-b { border: 1px solid var(--line); background: var(--surface); color: var(--text); padding: 9px 16px; border-radius: 9px; font-weight: 600; font-size: 13px; }
.jp-root .jp-b.jp-pri { background: var(--orange); border-color: var(--orange); color: rgb(255, 255, 255); }
.jp-root .jp-b.jp-danger { background: var(--red); border-color: var(--red); color: rgb(255, 255, 255); }
.jp-root .jp-summary-box { background: var(--colhead); border: 1px solid var(--line); border-radius: 10px; padding: 10px 12px; margin-bottom: 14px; font-size: 13px; }
.jp-root .jp-summary-box b { font-weight: 600; }
.jp-root .jp-pc { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; white-space: nowrap; }
.jp-root .jp-pc.jp-w { background: rgb(255, 237, 213); color: rgb(194, 65, 12); }
.jp-root .jp-pc.jp-p { background: rgb(237, 233, 254); color: rgb(109, 40, 217); }
.jp-root .jp-pc.jp-g { background: rgb(220, 252, 231); color: rgb(21, 128, 61); }
.jp-root .jp-vitems { display: flex; flex-direction: column; gap: 6px; }
.jp-root .jp-vitem { display: flex; align-items: center; gap: 10px; border: 1px solid var(--line); border-radius: 10px; padding: 8px 10px; font-size: 13px; }
.jp-root .jp-vitem .jp-in { flex: 1 1 0%; min-width: 0px; }
.jp-root .jp-vitem small { display: block; color: var(--muted); font-size: 11.5px; font-weight: 500; }
.jp-root .jp-modal.jp-vw { max-width: 700px; }
.jp-root .jp-vh { display: flex; gap: 14px; align-items: center; padding: 18px 20px; background: rgb(255, 255, 255); border-bottom: 1px solid var(--line); }
.jp-root .jp-vh .jp-av { width: 54px; height: 54px; flex: 0 0 54px; font-size: 18px; box-shadow: rgba(240, 122, 26, 0.3) 0px 4px 12px; }
.jp-root .jp-vh h3 { margin: 0px; font-size: 20px; font-weight: 800; }
.jp-root .jp-vh small { color: var(--muted); font-weight: 600; font-size: 13px; }
.jp-root .jp-vtags { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; margin-top: 7px; }
.jp-root .jp-vst { font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 999px; background: var(--orange-soft); color: var(--orange-dark); }
.jp-root .jp-vst.jp-karigar { background: rgb(255, 237, 213); color: rgb(194, 65, 12); }
.jp-root .jp-vst.jp-billing { background: rgb(220, 252, 231); color: rgb(21, 128, 61); }
.jp-root .jp-vst.jp-delivered { background: rgb(22, 163, 74); color: rgb(255, 255, 255); }
.jp-root .jp-vbody { padding: 16px 20px; display: flex; flex-direction: column; gap: 14px; }
.jp-root .jp-vamt { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.jp-root .jp-va { border-radius: 12px; padding: 11px 14px; border: 1px solid var(--line); background: rgb(255, 255, 255); }
.jp-root .jp-va span { font-size: 12px; font-weight: 700; color: var(--muted); }
.jp-root .jp-va b { display: block; font-size: 21px; font-weight: 800; margin-top: 2px; }
.jp-root .jp-va.jp-k b { color: rgb(17, 24, 39); }
.jp-root .jp-va.jp-g { background: rgb(240, 253, 244); border-color: rgb(187, 247, 208); }
.jp-root .jp-va.jp-g b { color: var(--green); }
.jp-root .jp-va.jp-r { background: rgb(254, 242, 242); border-color: rgb(254, 202, 202); }
.jp-root .jp-va.jp-r b { color: rgb(220, 38, 38); }
.jp-root .jp-vsec { border: 1px solid var(--line); border-radius: 12px; padding: 12px 14px; background: rgb(255, 255, 255); }
.jp-root .jp-vsec h5 { margin: 0px 0px 10px; font-size: 13px; font-weight: 800; color: var(--orange-dark); }
.jp-root .jp-vgrid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px 14px; }
.jp-root .jp-vf span { display: block; font-size: 11px; color: var(--soft); font-weight: 600; }
.jp-root .jp-vf b { font-size: 14px; font-weight: 700; }
.jp-root .jp-vf.jp-o b { color: var(--orange-dark); }
.jp-root .jp-vf.jp-g b { color: var(--green); }
.jp-root .jp-vf.jp-k b { color: rgb(17, 24, 39); font-weight: 800; }
.jp-root .jp-card { cursor: pointer; }
.jp-root .jp-btn-del { display: flex; align-items: center; gap: 6px; border: 1.5px solid var(--orange); background: rgb(255, 255, 255); color: var(--orange-dark); font-weight: 700; padding: 8px 14px; border-radius: 999px; font-size: 13px; transition: 0.2s; }
.jp-root .jp-btn-del:hover { background: var(--orange-soft); }
.jp-root .jp-dov { position: fixed; inset: 0px; background: rgba(15, 18, 30, 0.45); display: none; align-items: center; justify-content: center; z-index: 45; }
.jp-root .jp-dov.jp-open { display: flex; }
.jp-root .jp-bigm { width: 80vw; height: 80vh; background: rgb(255, 255, 255); border-radius: 18px; display: flex; flex-direction: column; overflow: hidden; box-shadow: rgba(0, 0, 0, 0.25) 0px 24px 60px; animation: 0.3s ease 0s 1 normal none running jp-zoomIn; }
.jp-root .jp-bm-head { display: flex; align-items: center; gap: 16px; padding: 16px 24px; border-bottom: 1px solid var(--line); }
.jp-root .jp-bm-head h3 { margin: 0px; font-size: 21px; }
.jp-root .jp-bm-head p { margin: 2px 0px 0px; font-size: 13px; color: var(--muted); }
.jp-root .jp-tabs { display: flex; gap: 4px; background: var(--col); border: 1px solid var(--line); border-radius: 999px; padding: 4px; margin-left: auto; }
.jp-root .jp-tabs button { border: 0px; background: transparent; padding: 7px 14px; border-radius: 999px; font-weight: 600; color: var(--muted); font-size: 13px; transition: 0.2s; }
.jp-root .jp-tabs button.jp-on { background: var(--orange); color: rgb(255, 255, 255); }
.jp-root .jp-fbar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; padding: 12px 24px; background: var(--col); border-bottom: 1px solid var(--line); }
.jp-root .jp-fbar input, .jp-root .jp-fbar select { border: 1px solid var(--line); border-radius: 9px; padding: 7px 10px; font-family: inherit; font-size: 13px; background: rgb(255, 255, 255); color: var(--text); }
.jp-root .jp-fbar input:focus, .jp-root .jp-fbar select:focus { outline: 0px; border-color: var(--orange); box-shadow: rgba(240, 122, 26, 0.15) 0px 0px 0px 3px; }
.jp-root .jp-fbar .jp-srch { flex: 1 1 0%; min-width: 200px; max-width: 290px; }
.jp-root .jp-chip { border: 1px solid var(--line); background: rgb(255, 255, 255); color: var(--muted); border-radius: 999px; padding: 6px 12px; font-size: 12px; font-weight: 600; transition: 0.2s; }
.jp-root .jp-drange { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--muted); font-weight: 600; }
.jp-root .jp-dsum { display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; padding: 14px 24px; }
.jp-root .jp-ds { border: 1px solid var(--line); border-radius: 12px; padding: 10px 14px; background: rgb(255, 255, 255); }
.jp-root .jp-ds span { font-size: 12px; color: var(--muted); font-weight: 600; }
.jp-root .jp-ds b { display: block; font-size: 19px; margin-top: 2px; color: rgb(17, 24, 39); font-weight: 800; }
.jp-root .jp-ds.jp-g { background: rgb(240, 253, 244); border-color: rgb(187, 247, 208); }
.jp-root .jp-ds.jp-g b { color: var(--green); }
.jp-root .jp-ds.jp-r { background: rgb(254, 242, 242); border-color: rgb(254, 202, 202); }
.jp-root .jp-ds.jp-r b { color: rgb(220, 38, 38); }
.jp-root .jp-ds.jp-o { background: var(--orange-soft); border-color: rgb(251, 213, 176); }
.jp-root .jp-ds.jp-o b { color: var(--orange-dark); }
.jp-root .jp-dtbl-wrap { flex: 1 1 0%; min-height: 0px; overflow: auto; margin: 0px 24px 10px; border: 1px solid var(--line); border-radius: 12px; }
.jp-root .jp-dpg { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 0px 24px 16px; font-size: 13px; font-weight: 600; color: var(--muted); }
.jp-root .jp-dpg b { color: var(--text); }
.jp-root .jp-pgs { display: flex; gap: 4px; align-items: center; flex-wrap: wrap; }
.jp-root .jp-pgb { border: 1px solid var(--line); background: rgb(255, 255, 255); color: var(--text); border-radius: 8px; min-width: 34px; padding: 6px 10px; font-size: 12.5px; font-weight: 700; transition: 0.2s; }
.jp-root .jp-pgb:hover:not(:disabled) { border-color: var(--orange); color: var(--orange-dark); }
.jp-root .jp-pgb.jp-on { background: var(--orange); border-color: var(--orange); color: rgb(255, 255, 255); }
.jp-root .jp-pgb:disabled { opacity: 0.45; cursor: not-allowed; }
.jp-root .jp-flbl { display: flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 700; color: var(--muted); white-space: nowrap; }
.jp-root .jp-flbl select { font-weight: 600; }
.jp-root .jp-dtbl { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 1050px; }
.jp-root .jp-dtbl th { position: sticky; top: 0px; background: var(--colhead); color: var(--orange-dark); text-align: left; padding: 10px 12px; font-weight: 700; font-size: 12px; z-index: 1; white-space: nowrap; box-shadow: inset 0 -2px 0 var(--line); }
.jp-root .jp-dtbl td { padding: 10px 12px; border-bottom: 1px solid var(--line); vertical-align: middle; font-weight: 600; }
.jp-root .jp-cs b { font-weight: 800; }
.jp-root .jp-dtbl tbody tr { animation: 0.5s ease 0s 1 normal none running jp-rowIn; }
.jp-root .jp-dtbl tbody tr:hover td { background: rgb(250, 250, 250); }
.jp-root .jp-cs small { display: block; color: var(--muted); font-size: 11.5px; }
.jp-root .jp-amt-k { color: rgb(17, 24, 39); font-weight: 800; }
.jp-root .jp-amt-g { color: var(--green); font-weight: 800; }
.jp-root .jp-amt-r { color: rgb(220, 38, 38); font-weight: 800; }
.jp-root .jp-pst { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 999px; white-space: nowrap; }
.jp-root .jp-pst.jp-paid { background: rgb(220, 252, 231); color: rgb(21, 128, 61); }
.jp-root .jp-pst.jp-due { background: rgb(254, 226, 226); color: rgb(220, 38, 38); }
.jp-root .jp-osum { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.jp-root .jp-osec { border: 1px solid var(--line); border-radius: 12px; padding: 12px; background: rgb(255, 255, 255); }
.jp-root .jp-osec.jp-full { grid-column: 1 / -1; }
.jp-root .jp-ostats { grid-template-columns: repeat(5, 1fr); gap: 10px; display: grid !important; margin: 0px !important; }
.jp-root .jp-ostats .jp-stat { padding: 10px 12px; }
.jp-root .jp-ostats .jp-stat .jp-si { width: 36px; height: 36px; flex-basis: 36px; font-size: 17px; }
.jp-root .jp-ostats .jp-stat b { font-size: 18px; }
.jp-root .jp-ostats .jp-stat span { font-size: 11.5px; }
.jp-root .jp-osec h5 { margin: 0px 0px 10px; font-size: 13.5px; font-weight: 800; color: var(--orange-dark); }
.jp-root .jp-osec .jp-dtbl tbody tr { animation: auto ease 0s 1 normal none running none; }
.jp-root .jp-tabs button.jp-on[data-t="summary"] { background: var(--orange-dark); }
@keyframes jp-zoomIn { 
  0% { opacity: 0; transform: scale(0.96); }
  100% { opacity: 1; transform: none; }
}
.jp-root .jp-metal.jp-urg { background: rgb(254, 226, 226); color: rgb(220, 38, 38); }
.jp-root .jp-av { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, rgb(240, 122, 26), rgb(253, 186, 116)); color: rgb(255, 255, 255); display: grid; place-items: center; font-weight: 700; font-size: 13px; flex: 0 0 38px; }
.jp-root .jp-tags { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin: 10px 0px 6px; }
.jp-root .jp-due-c { font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 999px; background: rgb(243, 244, 246); color: var(--muted); }
.jp-root .jp-due-c.jp-late { background: rgb(254, 226, 226); color: rgb(220, 38, 38); }
@keyframes jp-detHl { 
  0% { box-shadow: rgba(240, 122, 26, 0) 0px 0px 0px 0px; background: transparent; }
  25% { box-shadow: rgba(240, 122, 26, 0.18) 0px 0px 0px 8px; background: rgb(255, 247, 238); }
  100% { box-shadow: rgba(240, 122, 26, 0) 0px 0px 0px 0px; background: transparent; }
}
.jp-root .jp-cur { background: var(--colhead); border-radius: 10px; padding: 9px 14px; font-size: 13px; margin-bottom: 12px; display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
.jp-root .jp-cur b { color: var(--orange-dark); }
.jp-root .jp-b.jp-pri { box-shadow: rgba(240, 122, 26, 0.3) 0px 3px 10px; transition: transform 0.15s; }
.jp-root .jp-b.jp-pri:hover, .jp-root .jp-b.jp-save:hover { transform: translateY(-1px); }
@keyframes jp-ring { 
  0% { box-shadow: rgba(240, 122, 26, 0.5) 0px 0px 0px 0px; }
  70% { box-shadow: rgba(240, 122, 26, 0) 0px 0px 0px 10px; }
  100% { box-shadow: rgba(240, 122, 26, 0) 0px 0px 0px 0px; }
}
@keyframes jp-slideIn { 
  0% { opacity: 0; transform: translateX(24px); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-slideBack { 
  0% { opacity: 0; transform: translateX(-24px); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-flow { 
  100% { background-position: 200% 0px; }
}
@keyframes jp-glow { 
  0%, 100% { box-shadow: rgba(239, 68, 68, 0.45) 0px 0px 0px 0px; }
  50% { box-shadow: rgba(249, 115, 22, 0.55) 0px 0px 14px 2px; }
}
@keyframes jp-zap { 
  0%, 100% { transform: rotate(0deg) scale(1); }
  25% { transform: rotate(-15deg) scale(1.2); }
  75% { transform: rotate(15deg) scale(1.2); }
}
@keyframes jp-pulseb { 
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.07); }
}
@keyframes jp-shake { 
  0%, 100% { transform: translateX(0px); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
@keyframes jp-pop { 
  0% { transform: scale(1); }
  50% { transform: scale(1.12); }
  100% { transform: scale(1); }
}
@keyframes jp-rowIn { 
  0% { background: var(--orange-soft); }
  100% { background: transparent; }
}
@media (max-width: 700px) { .jp-root .jp-board-wrap { padding: 8px 14px 20px; } }
@media (max-width: 700px) { .jp-root .jp-board { grid-template-columns: repeat(3, 85vw); } }
@media (max-width: 700px) { .jp-root .jp-stats { margin-left: 0px; } }
@media (max-width: 700px) { .jp-root .jp-bigm { width: 96vw; height: 92vh; } }
@media (max-width: 700px) { .jp-root .jp-dsum { grid-template-columns: 1fr 1fr; padding: 12px 14px; } }
@media (max-width: 700px) { .jp-root .jp-fbar, .jp-root .jp-bm-head { padding-left: 14px; padding-right: 14px; } }
@media (max-width: 700px) { .jp-root .jp-dtbl-wrap { margin: 0px 14px 14px; } }
@media (prefers-reduced-motion: reduce) { .jp-root * { transition: none !important; animation: auto ease 0s 1 normal none running none !important; } }
.jp-root .jp-col[data-stage="order"] .jp-col-head { background: linear-gradient(135deg, rgb(255, 227, 200) 0%, rgb(255, 246, 238) 55%, rgb(255, 255, 255) 100%); }
.jp-root .jp-col[data-stage="karigar"] .jp-col-head { background: linear-gradient(135deg, rgb(230, 220, 255) 0%, rgb(246, 242, 255) 55%, rgb(255, 255, 255) 100%); }
.jp-root .jp-col[data-stage="billing"] .jp-col-head { background: linear-gradient(135deg, rgb(207, 245, 221) 0%, rgb(239, 252, 244) 55%, rgb(255, 255, 255) 100%); }
.jp-root .jp-count { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); }
.jp-root .jp-col[data-stage="karigar"] .jp-count { background: linear-gradient(135deg, rgb(124, 58, 237), rgb(167, 139, 250)); }
.jp-root .jp-col[data-stage="billing"] .jp-count { background: linear-gradient(135deg, rgb(21, 128, 61), rgb(74, 222, 128)); }
.jp-root .jp-plus { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 176, 112)); }
.jp-root .jp-col:not(:last-child)::after { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 176, 112)); }
.jp-root .jp-btn-new { background: linear-gradient(135deg, rgb(240, 122, 26) 0%, rgb(255, 158, 79) 100%); border-color: rgb(240, 122, 26); }
.jp-root .jp-btn-new:hover { background: linear-gradient(135deg, rgb(217, 98, 10) 0%, rgb(245, 138, 53) 100%); }
.jp-root .jp-btn-del { background: linear-gradient(135deg, rgb(255, 255, 255) 30%, rgb(255, 235, 216) 100%); }
.jp-root .jp-card[data-stage="order"] { background: linear-gradient(rgb(255, 248, 242) 0px, rgb(255, 255, 255) 60px); }
.jp-root .jp-card[data-stage="karigar"] { background: linear-gradient(rgb(250, 247, 255) 0px, rgb(255, 255, 255) 60px); }
.jp-root .jp-card[data-stage="billing"] { background: linear-gradient(rgb(244, 253, 247) 0px, rgb(255, 255, 255) 60px); }
.jp-root .jp-card .jp-act { background: linear-gradient(135deg, rgb(255, 244, 234), rgb(255, 224, 196)); }
.jp-root .jp-card .jp-act:hover { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); color: rgb(255, 255, 255); }
.jp-root .jp-card[data-stage="karigar"] .jp-act.jp-green { background: linear-gradient(135deg, rgb(124, 58, 237), rgb(167, 139, 250)); }
.jp-root .jp-card[data-stage="billing"] .jp-act.jp-green { background: linear-gradient(135deg, rgb(21, 128, 61), rgb(74, 222, 128)); }
.jp-root .jp-card .jp-av { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 196, 143)); }
.jp-root .jp-card.jp-open .jp-dtg { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); }
.jp-root .jp-card[data-stage="karigar"].jp-open .jp-dtg { background: linear-gradient(135deg, rgb(124, 58, 237), rgb(167, 139, 250)); }
.jp-root .jp-card[data-stage="billing"].jp-open .jp-dtg { background: linear-gradient(135deg, rgb(21, 128, 61), rgb(74, 222, 128)); }
.jp-root .jp-pc.jp-w { background: linear-gradient(135deg, rgb(255, 228, 204), rgb(255, 244, 234)); }
.jp-root .jp-pc.jp-p { background: linear-gradient(135deg, rgb(233, 223, 255), rgb(247, 243, 255)); }
.jp-root .jp-pc.jp-g { background: linear-gradient(135deg, rgb(209, 247, 223), rgb(240, 253, 245)); }
.jp-root .jp-metal.jp-urg { background: linear-gradient(135deg, rgb(254, 202, 202), rgb(255, 241, 241)); }
.jp-root .jp-b.jp-pri { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); border-color: rgb(240, 122, 26); }
.jp-root .jp-b.jp-pri:hover { background: linear-gradient(135deg, rgb(217, 98, 10), rgb(245, 138, 53)); }
.jp-root .jp-b.jp-danger { background: linear-gradient(135deg, rgb(220, 38, 38), rgb(248, 113, 113)); border-color: rgb(220, 38, 38); }
.jp-root .jp-pane-h .jp-ic, .jp-root .jp-stg-ic { background: linear-gradient(135deg, rgb(255, 224, 196), rgb(255, 247, 240)); }
.jp-root .jp-va.jp-k { background: linear-gradient(135deg, rgb(243, 244, 246), rgb(255, 255, 255)); }
.jp-root .jp-va.jp-g, .jp-root .jp-ds.jp-g { background: linear-gradient(135deg, rgb(217, 248, 228), rgb(247, 254, 249)); }
.jp-root .jp-va.jp-r, .jp-root .jp-ds.jp-r { background: linear-gradient(135deg, rgb(254, 218, 218), rgb(255, 247, 247)); }
.jp-root .jp-ds.jp-o { background: linear-gradient(135deg, rgb(255, 224, 196), rgb(255, 247, 240)); }
.jp-root .jp-vh { background: linear-gradient(135deg, rgb(255, 241, 228) 0%, rgb(255, 255, 255) 60%); }
.jp-root .jp-tabs button.jp-on { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); }
.jp-root .jp-tabs button.jp-on[data-t="summary"] { background: linear-gradient(135deg, rgb(217, 98, 10), rgb(245, 158, 11)); }
.jp-root .jp-chip.jp-on, .jp-root .jp-pgb.jp-on { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); }
.jp-root .jp-dtbl th { background: linear-gradient(rgb(255, 246, 238), rgb(255, 255, 255)); }
.jp-root .jp-mid .jp-ml { flex: 1 1 0%; min-width: 0px; }
.jp-root .jp-subrow { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; margin-top: 5px; }
.jp-root .jp-sid { font-size: 10.5px; font-weight: 700; color: var(--muted); background: rgb(245, 246, 248); border: 1px solid var(--line); border-radius: 6px; padding: 1px 6px; letter-spacing: 0.2px; }
.jp-root .jp-sit { font-size: 10.5px; font-weight: 700; color: rgb(75, 85, 99); background: linear-gradient(135deg, rgb(243, 244, 246), rgb(255, 255, 255)); border: 1px solid var(--line); border-radius: 6px; padding: 1px 6px; }
.jp-root .jp-metal.jp-urg.jp-sm { font-size: 10px; padding: 1px 6px; }
.jp-root .jp-mid .jp-tags .jp-pc { font-size: 9.5px; padding: 1px 6px; font-weight: 700; opacity: 0.92; }
.jp-root .jp-mid .jp-tags .jp-pc.jp-w { background: linear-gradient(135deg, rgb(255, 240, 226), rgb(255, 250, 245)); color: rgb(224, 132, 62); }
.jp-root .jp-mid .jp-tags .jp-pc.jp-p { background: linear-gradient(135deg, rgb(243, 238, 255), rgb(251, 249, 255)); color: rgb(149, 117, 230); }
.jp-root .jp-mid .jp-tags .jp-pc.jp-g { background: linear-gradient(135deg, rgb(230, 250, 238), rgb(248, 254, 250)); color: rgb(61, 170, 102); }
.jp-root .jp-mid .jp-tags { gap: 3px; }
.jp-root .jp-card .jp-prod { color: rgb(194, 101, 42); font-weight: 800; }
.jp-root .jp-card[data-stage="karigar"] .jp-prod { color: rgb(118, 80, 196); }
.jp-root .jp-card[data-stage="billing"] .jp-prod { color: rgb(43, 133, 80); }
.jp-root .jp-card { padding: 9px 11px; margin-bottom: 9px; border-radius: 11px; }
.jp-root .jp-card-top { gap: 7px; }
.jp-root .jp-card .jp-av { width: 32px; height: 32px; flex: 0 0 32px; font-size: 11.5px; }
.jp-root .jp-card .jp-cust { font-size: 14px; line-height: 1.15; }
.jp-root .jp-card .jp-cust small { font-size: 11px; }
.jp-root .jp-card .jp-dots { width: 24px; height: 24px; font-size: 16px; }
.jp-root .jp-card .jp-mid { margin: 6px 0px 2px; gap: 8px; }
.jp-root .jp-card .jp-prod { font-size: 12px; }
.jp-root .jp-card .jp-subrow { margin-top: 3px; }
.jp-root .jp-card .jp-grid { margin-top: 6px; padding-top: 4px; }
.jp-root .jp-card .jp-grid .jp-f { padding: 2px 0px; }
.jp-root .jp-card.jp-open .jp-grid .jp-f:nth-child(n+3) { padding: 2px 0px; }
.jp-root .jp-card .jp-f span { font-size: 10.5px; }
.jp-root .jp-card .jp-f b { font-size: 13px; }
.jp-root .jp-card .jp-dtg { width: 22px; height: 22px; font-size: 11px; }
.jp-root .jp-ftot { padding-right: 28px; }
.jp-root .jp-dsum { gap: 8px; padding: 10px 24px; }
.jp-root .jp-ds { padding: 6px 11px; border-radius: 10px; }
.jp-root .jp-ds span { font-size: 10.5px; }
.jp-root .jp-ds b { font-size: 15px; margin-top: 0px; }
.jp-root .jp-ostats { gap: 8px !important; }
.jp-root .jp-ostats .jp-stat { padding: 5px 9px 5px 6px; border-radius: 10px; }
.jp-root .jp-ostats .jp-stat .jp-si { width: 26px; height: 26px; flex-basis: 26px; font-size: 13px; border-radius: 7px; }
.jp-root .jp-ostats .jp-stat b { font-size: 14px; }
.jp-root .jp-ostats .jp-stat span { font-size: 10px; }
.jp-root .jp-osec { padding: 9px 11px; }
.jp-root .jp-osec h5 { margin-bottom: 7px; font-size: 12.5px; }
.jp-root .jp-bm-head { padding: 11px 20px; gap: 12px; }
.jp-root .jp-bm-head h3 { font-size: 17px; }
.jp-root .jp-bm-head p { font-size: 11.5px; margin-top: 1px; }
.jp-root .jp-tabs button { padding: 5px 11px; font-size: 12px; }
.jp-root .jp-fbar { padding: 8px 20px; gap: 8px; }
.jp-root .jp-fbar input, .jp-root .jp-fbar select { padding: 5px 8px; font-size: 12px; }
.jp-root .jp-fbar .jp-chip { padding: 4px 10px; font-size: 11px; }
.jp-root .jp-dsum { padding: 8px 20px; }
.jp-root .jp-dtbl-wrap { margin: 0px 20px 8px; }
.jp-root .jp-dpg { padding: 0px 20px 10px; font-size: 12px; }
.jp-root .jp-dtbl { font-size: 12px; }
.jp-root .jp-dtbl th { padding: 7px 9px; font-size: 11px; }
.jp-root .jp-dtbl td { padding: 7px 9px; }
.jp-root #jp-dv-tb > tr > td[colspan] { padding: 8px !important; }
.jp-root .jp-osum { gap: 8px; }
.jp-root .jp-osec .jp-dtbl th { padding: 5px 8px; font-size: 10.5px; }
.jp-root .jp-osec .jp-dtbl td { padding: 5px 8px; font-size: 11.5px; }
.jp-root .jp-osec .jp-vst { font-size: 10px; padding: 1px 7px; }
.jp-root .jp-osec .jp-metal { font-size: 10px; padding: 1px 7px; }
.jp-root .jp-col:not(:last-child)::after { display: none !important; content: none !important; }
.jp-root .jp-modal.jp-vw { max-width: 620px; }
.jp-root .jp-vh { padding: 12px 16px; gap: 11px; }
.jp-root .jp-vh .jp-av { width: 42px; height: 42px; flex: 0 0 42px; font-size: 14px; }
.jp-root .jp-vh h3 { font-size: 17px; }
.jp-root .jp-vh small { font-size: 12px; }
.jp-root .jp-vtags { margin-top: 4px; gap: 5px; }
.jp-root .jp-vtags .jp-sno, .jp-root .jp-vtags .jp-metal, .jp-root .jp-vtags .jp-vst, .jp-root .jp-vtags .jp-due-c { font-size: 10.5px; }
.jp-root .jp-vbody { padding: 11px 16px; gap: 9px; }
.jp-root .jp-vamt { gap: 8px; }
.jp-root .jp-va { padding: 7px 11px; border-radius: 10px; }
.jp-root .jp-va span { font-size: 11px; }
.jp-root .jp-va b { font-size: 17px; }
.jp-root .jp-vsec { padding: 8px 11px; border-radius: 10px; }
.jp-root .jp-vsec h5 { font-size: 12px; margin-bottom: 7px; }
.jp-root .jp-vgrid { gap: 6px 12px; grid-template-columns: repeat(4, 1fr); }
.jp-root .jp-vf span { font-size: 10.5px; }
.jp-root .jp-vf b { font-size: 13px; }
.jp-root .jp-vitems { gap: 5px; }
.jp-root .jp-vitem { padding: 6px 9px; font-size: 12px; gap: 8px; }
.jp-root .jp-vitem small { font-size: 10.5px; }
.jp-root .jp-modal.jp-vw .jp-modal-f { padding: 10px 16px; }
.jp-root .jp-modal.jp-vw .jp-modal-f .jp-b { padding: 7px 13px; font-size: 12px; }
.jp-root .jp-vbody { gap: 7px; padding: 9px 14px; }
.jp-root .jp-vamt { gap: 6px; }
.jp-root .jp-va { padding: 5px 10px; }
.jp-root .jp-va span { font-size: 10.5px; }
.jp-root .jp-va b { font-size: 15px; }
.jp-root .jp-vsec { padding: 7px 10px; }
.jp-root .jp-vsec h5 { margin-bottom: 5px; font-size: 11.5px; }
.jp-root .jp-vgrid { gap: 4px 10px; }
.jp-root .jp-vf b { font-size: 12.5px; }
.jp-root .jp-card[data-stage="karigar"] .jp-card-top .jp-act.jp-green { background: linear-gradient(135deg, rgb(243, 238, 255), rgb(228, 217, 255)); border: 1px solid rgb(139, 92, 246); color: rgb(109, 40, 217); }
.jp-root .jp-card[data-stage="karigar"] .jp-card-top .jp-act.jp-green:hover { background: linear-gradient(135deg, rgb(124, 58, 237), rgb(167, 139, 250)); color: rgb(255, 255, 255); }
.jp-root .jp-card[data-stage="billing"] .jp-card-top .jp-act.jp-green { background: linear-gradient(135deg, rgb(236, 253, 243), rgb(209, 247, 223)); border: 1px solid rgb(22, 163, 74); color: rgb(21, 128, 61); }
.jp-root .jp-card[data-stage="billing"] .jp-card-top .jp-act.jp-green:hover { background: linear-gradient(135deg, rgb(21, 128, 61), rgb(74, 222, 128)); color: rgb(255, 255, 255); }
.jp-root .jp-card { opacity: 1; filter: none; transition: opacity 0.25s, filter 0.25s, box-shadow 0.25s, transform 0.25s, border-color 0.25s; }
.jp-root .jp-card:hover, .jp-root .jp-card.jp-open, .jp-root .jp-card:has(.jp-menu.jp-open) { opacity: 1; filter: none; }
.jp-root .jp-card.jp-dragging { opacity: 0.45; }
.jp-root .jp-board { background: linear-gradient(90deg, rgb(30, 34, 53) 0%, rgb(138, 109, 30) 35%, rgb(201, 162, 39) 50%, rgb(138, 109, 30) 65%, rgb(30, 34, 53) 100%) left top / 100% 4px no-repeat, rgb(255, 255, 255); border: 1px solid rgb(203, 210, 220); border-radius: 16px; box-shadow: rgba(30, 34, 53, 0.08) 0px 4px 18px; gap: 16px; padding: 18px 14px 14px !important; }
.jp-root .jp-board .jp-col { background: rgb(245, 248, 253); border-color: rgb(227, 233, 243); }
.jp-root .jp-prod .jp-more { display: inline-block; margin-left: 5px; font-size: 9.5px; font-weight: 700; text-transform: none; letter-spacing: 0px; color: rgb(107, 114, 128); background: rgb(243, 244, 246); border: 1px dashed rgb(201, 207, 216); border-radius: 999px; padding: 0px 7px; vertical-align: 1px; }
.jp-root .jp-card[data-stage="order"] .jp-prod .jp-more { color: rgb(194, 101, 42); background: rgb(255, 244, 234); border-color: rgb(245, 199, 158); }
.jp-root .jp-card[data-stage="karigar"] .jp-prod .jp-more { color: rgb(118, 80, 196); background: rgb(245, 241, 255); border-color: rgb(205, 189, 245); }
.jp-root .jp-card[data-stage="billing"] .jp-prod .jp-more { color: rgb(43, 133, 80); background: rgb(239, 251, 243); border-color: rgb(169, 227, 190); }
@keyframes jp-fadeUp { 
  0% { opacity: 0; transform: translateY(10px); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-fadeIn { 
  0% { opacity: 0; }
  100% { opacity: 1; }
}
@keyframes jp-flashO { 
  0% { box-shadow: rgba(240, 122, 26, 0.55) 0px 0px 0px 0px; }
  60% { box-shadow: rgba(240, 122, 26, 0) 0px 0px 0px 8px; }
  100% { box-shadow: var(--shadow); }
}
@keyframes jp-flashP { 
  0% { box-shadow: rgba(139, 92, 246, 0.55) 0px 0px 0px 0px; }
  60% { box-shadow: rgba(139, 92, 246, 0) 0px 0px 0px 8px; }
  100% { box-shadow: var(--shadow); }
}
@keyframes jp-flashG { 
  0% { box-shadow: rgba(22, 163, 74, 0.55) 0px 0px 0px 0px; }
  60% { box-shadow: rgba(22, 163, 74, 0) 0px 0px 0px 8px; }
  100% { box-shadow: var(--shadow); }
}
.jp-root .jp-board { animation: 0.5s ease 0s 1 normal backwards running jp-fadeUp; }
.jp-root .jp-count { transition: transform 0.2s; }
.jp-root .jp-col-head:hover .jp-count { transform: scale(1.08); }
.jp-root .jp-col-head { transition: box-shadow 0.25s, transform 0.25s; }
.jp-root .jp-col-head:hover { box-shadow: rgba(30, 34, 53, 0.08) 0px 6px 16px; }
.jp-root .jp-plus { transition: transform 0.25s, box-shadow 0.25s; }
.jp-root .jp-plus:hover { transform: rotate(90deg) scale(1.06); box-shadow: rgba(240, 122, 26, 0.45) 0px 4px 12px; }
.jp-root .jp-btn-new, .jp-root .jp-btn-del { transition: transform 0.2s, box-shadow 0.2s, background 0.2s; }
.jp-root .jp-btn-new:hover, .jp-root .jp-btn-del:hover { transform: translateY(-1px); box-shadow: rgba(240, 122, 26, 0.3) 0px 6px 16px; }
.jp-root #jp-dv-sum .jp-ds { animation: 0.35s ease 0s 1 normal backwards running jp-fadeUp; }
.jp-root #jp-dv-sum .jp-ds:nth-child(2) { animation-delay: 0.04s; }
.jp-root #jp-dv-sum .jp-ds:nth-child(3) { animation-delay: 0.08s; }
.jp-root #jp-dv-sum .jp-ds:nth-child(4) { animation-delay: 0.12s; }
.jp-root #jp-dv-sum .jp-ds:nth-child(5) { animation-delay: 0.16s; }
.jp-root .jp-osec { animation: 0.4s ease 0s 1 normal backwards running jp-fadeUp; }
.jp-root .jp-osec:nth-child(2) { animation-delay: 0.06s; }
.jp-root .jp-osec:nth-child(3) { animation-delay: 0.12s; }
.jp-root .jp-osec:nth-child(4) { animation-delay: 0.18s; }
.jp-root .jp-ds, .jp-root .jp-stat, .jp-root .jp-osec { transition: transform 0.2s, box-shadow 0.2s; }
.jp-root .jp-ds:hover, .jp-root .jp-stat:hover { transform: translateY(-2px); box-shadow: rgba(30, 34, 53, 0.08) 0px 6px 14px; }
.jp-root .jp-tabs button { transition: background 0.25s, color 0.25s, transform 0.15s; }
.jp-root .jp-vamt .jp-va, .jp-root .jp-vsec, .jp-root .jp-vitem { animation: 0.35s ease 0s 1 normal backwards running jp-fadeUp; }
.jp-root .jp-vsec:nth-of-type(2) { animation-delay: 0.1s; }
.jp-root .jp-vsec:nth-of-type(3) { animation-delay: 0.15s; }
.jp-root .jp-vsec:nth-of-type(4) { animation-delay: 0.2s; }
.jp-root .jp-summary-box, .jp-root .jp-form { animation: 0.35s ease 0s 1 normal backwards running jp-fadeIn; }
.jp-root .jp-dtg { transition: transform 0.3s cubic-bezier(0.3, 1.4, 0.5, 1), background 0.2s, border-color 0.2s; }
.jp-root .jp-col-list { scroll-behavior: smooth; }
.jp-root .jp-board .jp-mid .jp-tags .jp-pc { color: rgb(17, 24, 39) !important; }
.jp-root .jp-board .jp-card .jp-mid { margin: 5px 0px 1px; }
.jp-root .jp-board .jp-card .jp-prod { font-size: 11px; }
.jp-root .jp-board .jp-card .jp-prod .jp-more { font-size: 8.5px; padding: 0px 6px; }
.jp-root .jp-board .jp-card .jp-subrow { margin-top: 2px; gap: 4px; }
.jp-root .jp-board .jp-card .jp-sid, .jp-root .jp-board .jp-card .jp-sit { font-size: 9.5px; padding: 0px 5px; }
.jp-root .jp-board .jp-card .jp-metal.jp-urg.jp-sm { font-size: 9px; }
.jp-root .jp-board .jp-mid .jp-tags .jp-pc { font-size: 8.5px !important; padding: 1px 5px !important; }
.jp-root .jp-board .jp-card { border-width: 0px 1px 0px 3px !important; border-style: none solid !important; border-color: currentcolor rgb(240, 122, 26) !important; border-image: none !important; background: rgb(255, 255, 255) !important; }
.jp-root .jp-board .jp-card[data-stage="karigar"] { border-left-color: rgb(139, 92, 246) !important; border-right-color: rgb(139, 92, 246) !important; }
.jp-root .jp-board .jp-card[data-stage="billing"] { border-left-color: rgb(22, 163, 74) !important; border-right-color: rgb(22, 163, 74) !important; }
.jp-root .jp-board .jp-col-head { border-width: 0px 0px 3px !important; border-style: none none solid !important; border-color: currentcolor currentcolor rgb(240, 122, 26) !important; border-image: none !important; min-height: 56px !important; padding: 7px 12px !important; margin-bottom: 10px !important; }
.jp-root .jp-board .jp-col-head h2 { font-size: 14px; }
.jp-root .jp-board .jp-col[data-stage="karigar"] .jp-col-head { border-bottom-color: rgb(139, 92, 246) !important; }
.jp-root .jp-board .jp-col[data-stage="billing"] .jp-col-head { border-bottom-color: rgb(22, 163, 74) !important; }
.jp-root .jp-board .jp-plus { width: 28px; height: 28px; font-size: 17px; }
.jp-root .jp-board .jp-col.jp-over { background: rgb(255, 243, 232); border-color: var(--orange); }
.jp-root .jp-board .jp-col[data-stage="karigar"].jp-over { background: rgb(243, 238, 255); border-color: rgb(139, 92, 246); }
.jp-root .jp-modal-h { padding: 11px 16px; }
.jp-root .jp-modal-h h3 { font-size: 16px; }
.jp-root .jp-modal-b { padding: 11px 16px; }
.jp-root .jp-modal-f { padding: 10px 16px; gap: 6px; }
.jp-root .jp-modal-f .jp-b { padding: 7px 13px; font-size: 12.5px; }
.jp-root .jp-summary-box { padding: 7px 10px; margin-bottom: 9px; font-size: 12px; }
.jp-root .jp-bmain { flex: 1 1 0%; min-height: 0px; display: flex; flex-direction: column; background: linear-gradient(90deg, rgb(30, 34, 53) 0%, rgb(138, 109, 30) 35%, rgb(201, 162, 39) 50%, rgb(138, 109, 30) 65%, rgb(30, 34, 53) 100%) left top / 100% 4px no-repeat, rgb(255, 255, 255); border: 1px solid rgb(203, 210, 220); border-radius: 16px; box-shadow: rgba(30, 34, 53, 0.08) 0px 4px 18px; padding: 14px; animation: 0.5s ease 0s 1 normal backwards running jp-fadeUp; }
.jp-root .jp-bmain .jp-board { border-radius: 0px; animation: auto ease 0s 1 normal none running none; background: none !important; border: 0px !important; box-shadow: none !important; padding: 0px !important; }
.jp-root .jp-bhead { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 2px 4px 12px; flex-wrap: wrap; flex-shrink: 0; }
.jp-root .jp-bh-r { display: flex; gap: 10px; align-items: center; }
@keyframes jp-wNeed { 
  0%, 100% { box-shadow: rgba(240, 122, 26, 0) 0px 0px 0px 0px; }
  40% { box-shadow: rgba(240, 122, 26, 0.28) 0px 0px 0px 6px; }
}
@keyframes jp-juMpIn { 
  0% { opacity: 0; transform: translateY(-6px); }
  100% { opacity: 1; transform: none; }
}
.jp-root .jp-card.jp-dragging { opacity: 1 !important; transform: none; }
.jp-root .jp-col { transition: filter 0.25s, opacity 0.25s, background 0.15s, border-color 0.15s; }
.jp-root .jp-bhead .jp-bh-r:first-child { display: flex; gap: 10px; }
.jp-root .jp-vit-asg { border: 1.5px solid var(--orange); background: linear-gradient(135deg, rgb(255, 255, 255), rgb(255, 235, 216)); color: var(--orange-dark); font-family: inherit; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 999px; cursor: pointer; white-space: nowrap; transition: transform 0.15s, box-shadow 0.15s; }
.jp-root .jp-vit-asg:hover { transform: translateY(-1px); box-shadow: rgba(240, 122, 26, 0.25) 0px 4px 10px; }
.jp-root .jp-menu.jp-dvm { z-index: 400; }
.jp-root .jp-dtbl td.jp-dv-act { width: 44px; text-align: center; padding-left: 4px !important; padding-right: 8px !important; }
.jp-root .jp-dtbl td.jp-dv-act .jp-itact { width: 28px; height: 28px; font-size: 15px; }
.jp-root #jp-modal :is(input, select, textarea):not(:focus):not([type="checkbox"]):not([type="radio"]) { border-color: rgb(195, 202, 213); }
.jp-root #jp-modal :is(#jp-nf-f, #jp-nf-tf, #jp-mp-f, #jp-mp-a) { border-color: rgb(134, 217, 166); }
.jp-root .jp-ad-wrap { position: relative; }
.jp-root .jp-btn-del.jp-ad-dots { width: auto; min-width: 42px; height: 38px; padding: 0px 12px; font-size: 20px; font-weight: 900; line-height: 1; justify-content: center; gap: 6px; }
.jp-root .jp-btn-del.jp-ad-dots .jp-count { font-size: 11px; }
.jp-root .jp-menu.jp-ad-menu { right: 0px; left: auto; top: calc(100% + 6px); min-width: 200px; z-index: 30; }
.jp-root .jp-osrch { position: relative; flex: 1 1 260px; min-width: 220px; max-width: 380px; }
.jp-root .jp-osrch input { width: 100%; height: 38px; border: 1.5px solid rgb(232, 174, 122); border-radius: 999px; padding: 0px 14px; font-family: inherit; font-size: 13px; font-weight: 600; color: var(--text); background: rgb(255, 255, 255); outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
.jp-root .jp-osrch input:focus { border-color: var(--orange); box-shadow: rgba(240, 122, 26, 0.18) 0px 0px 0px 3px; }
.jp-root .jp-os-res { position: absolute; left: 0px; top: calc(100% + 6px); width: min(560px, 90vw); max-height: 420px; overflow: auto; background: rgb(255, 255, 255); border: 1px solid var(--line); border-radius: 14px; box-shadow: rgba(0, 0, 0, 0.16) 0px 14px 36px; z-index: 40; padding: 6px; }
.jp-root .jp-os-it { display: flex; align-items: center; gap: 10px; padding: 9px 10px; border-radius: 10px; cursor: pointer; }
.jp-root .jp-os-it:hover { background: rgb(255, 246, 238); }
.jp-root .jp-os-it + .jp-os-it { border-top: 1px dashed var(--line); }
.jp-root .jp-os-av { flex: 0 0 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; color: rgb(255, 255, 255); background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); }
.jp-root .jp-os-av.jp-st-karigar { background: linear-gradient(135deg, rgb(124, 58, 237), rgb(167, 139, 250)); }
.jp-root .jp-os-av.jp-st-billing { background: linear-gradient(135deg, rgb(22, 163, 74), rgb(74, 222, 128)); }
.jp-root .jp-os-tx { flex: 1 1 0%; min-width: 0px; display: flex; flex-direction: column; gap: 1px; }
.jp-root .jp-os-tx b { font-size: 14px; color: var(--text); }
.jp-root .jp-os-tx small { font-size: 11.5px; color: var(--muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.jp-root .jp-os-tx mark, .jp-root .jp-os-tx b mark { background: rgb(255, 227, 199); color: inherit; border-radius: 3px; padding: 0px 1px; }
.jp-root .jp-os-id { font-weight: 800; color: var(--orange-dark); }
.jp-root .jp-os-st { font-weight: 800; font-size: 10.5px; padding: 1px 7px; border-radius: 999px; background: rgb(255, 241, 228); color: var(--orange-dark); }
.jp-root .jp-os-st.jp-st-karigar { background: rgb(243, 238, 255); color: rgb(109, 40, 217); }
.jp-root .jp-os-st.jp-st-billing { background: rgb(236, 253, 243); color: rgb(21, 128, 61); }
.jp-root .jp-os-urg { color: rgb(220, 38, 38); font-weight: 700; }
.jp-root .jp-os-cs { flex: 0 0 auto; border: 1.5px solid var(--orange); background: linear-gradient(135deg, rgb(255, 255, 255), rgb(255, 235, 216)); color: var(--orange-dark); font-family: inherit; font-size: 11.5px; font-weight: 700; padding: 6px 11px; border-radius: 999px; cursor: pointer; white-space: nowrap; }
.jp-root .jp-os-cs:hover { box-shadow: rgba(240, 122, 26, 0.25) 0px 4px 10px; }
.jp-root .jp-modal.jp-csm { max-width: 440px; }
.jp-root .jp-cs-list { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
.jp-root .jp-cs-opt { display: flex; align-items: center; gap: 12px; text-align: left; border: 1.5px solid var(--line); background: rgb(255, 255, 255); border-radius: 12px; padding: 10px 12px; cursor: pointer; font-family: inherit; transition: border-color 0.15s, background 0.15s; }
.jp-root .jp-cs-opt:hover:not(:disabled) { border-color: var(--orange); background: rgb(255, 246, 238); }
.jp-root .jp-cs-opt.jp-cur { background: rgb(248, 250, 252); opacity: 0.75; cursor: default; }
.jp-root .jp-cs-ic { font-size: 20px; }
.jp-root .jp-cs-opt b { display: block; font-size: 13.5px; color: var(--text); }
.jp-root .jp-cs-opt small { font-size: 11.5px; color: var(--muted); }
@media (max-width: 620px) { .jp-root .jp-osrch { order: 9; flex-basis: 100%; max-width: none; } }
@media (max-width: 620px) { .jp-root .jp-os-it { flex-wrap: wrap; position: relative; padding-bottom: 42px; } }
@media (max-width: 620px) { .jp-root .jp-os-it .jp-os-cs { position: absolute; right: 10px; bottom: 8px; } }
@media (max-width: 620px) { .jp-root .jp-os-tx small { white-space: normal; } }
.jp-root .jp-bhead .jp-bh-r > button { white-space: nowrap; flex-shrink: 0; }
.jp-root .jp-bhead .jp-bh-r:first-child { flex: 1 1 0%; min-width: 0px; flex-wrap: wrap; }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r > button { white-space: normal; flex-shrink: 1; } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r:first-child { flex-wrap: wrap; } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r:first-child > button { flex: 1 1 0px; min-width: 0px; } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r:first-child { flex: 1 1 calc(100% - 70px); } }
@media (max-width: 620px) { .jp-root .jp-osrch { width: 100%; flex: 1 1 100% !important; } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r:first-child > button { border-radius: 12px; font-size: 12px; padding: 8px; line-height: 1.25; } }
.jp-root .jp-os-fab { display: none; }
@media (max-width: 620px) { .jp-root .jp-osrch { display: none !important; } }
@media (max-width: 620px) { .jp-root .jp-os-fab { display: grid; place-items: center; position: fixed; right: calc(16px + env(safe-area-inset-right,0px)); bottom: calc(18px + env(safe-area-inset-bottom,0px)); width: 54px; height: 54px; border-radius: 50%; border: 0px; background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); color: rgb(255, 255, 255); font-size: 22px; box-shadow: rgba(240, 122, 26, 0.45) 0px 10px 24px; z-index: 35; cursor: pointer; } }
@media (max-width: 620px) { .jp-root .jp-os-fab:active { transform: scale(0.94); } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r > .jp-os-fab { border-radius: 50% !important; font-size: 22px !important; padding: 0px !important; width: 54px !important; height: 54px !important; flex: 0 0 auto !important; } }
.jp-root .jp-modal.jp-vw .jp-vgrid { grid-template-columns: repeat(2, minmax(0px, 1fr)) !important; gap: 0px 22px !important; }
.jp-root .jp-modal.jp-vw .jp-vf { justify-content: space-between; align-items: baseline; gap: 10px; padding: 7px 0px; border-bottom: 1px dashed var(--line); display: flex !important; flex-direction: row !important; }
.jp-root .jp-modal.jp-vw .jp-vf span { font-size: 12.5px; color: var(--muted); font-weight: 600; }
.jp-root .jp-modal.jp-vw .jp-vf b { font-size: 13.5px; text-align: right; }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vgrid { grid-template-columns: 1fr !important; } }
.jp-root .jp-btn-del.jp-ad-dots .jp-count { display: none !important; }
.jp-root .jp-btn-del.jp-ad-dots { min-width: 40px; padding: 0px 10px; }
.jp-root .jp-modal.jp-vw:not(.jp-qv) { max-width: 560px !important; }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vgrid { align-items: baseline; grid-template-columns: max-content minmax(0px, 1fr) max-content minmax(0px, 1fr) !important; gap: 8px 10px !important; } }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vgrid > .jp-vf { display: contents !important; } }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vgrid > .jp-vf > span::after { content: " :"; color: var(--soft); } }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vgrid > .jp-vf > b { text-align: left !important; } }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vgrid > .jp-vf:nth-child(2n) > span { margin-left: 40px; } }
@media (max-width: 620px) { .jp-root .jp-board { scroll-snap-type: x mandatory; overscroll-behavior-x: contain; } }
@media (max-width: 620px) { .jp-root .jp-board .jp-col { scroll-snap-align: start; scroll-snap-stop: always; } }
@media (max-width: 620px) { .jp-root .jp-board .jp-col-list, .jp-root .jp-board .jp-card { touch-action: pan-y; } }
@media (max-width: 620px) { .jp-root .jp-card { user-select: none; } }
@media (max-width: 620px) { .jp-root .jp-overlay { top: var(--vvt,0) !important; bottom: auto !important; height: var(--vvh,100%) !important; padding: 8px !important; } }
@media (max-width: 620px) { .jp-root .jp-overlay .jp-modal { max-height: calc(var(--vvh,100vh) - 16px) !important; } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r:first-child > .jp-btn-new { flex: 0 0 auto; padding: 9px 16px; font-size: 13px; border-radius: 999px !important; } }
.jp-root .jp-sdots { display: none; }
@media (max-width: 620px) { .jp-root .jp-board { scrollbar-width: none; } }
@media (max-width: 620px) { .jp-root .jp-board::-webkit-scrollbar { display: none; height: 0px; } }
@media (max-width: 620px) { .jp-root .jp-sdots { display: flex; gap: 6px; justify-content: center; padding: 10px 0px 2px; flex-shrink: 0; } }
@media (max-width: 620px) { .jp-root .jp-sdots button { border: 1px solid var(--line); background: rgb(255, 255, 255); color: var(--muted); font-family: inherit; font-size: 11.5px; font-weight: 700; padding: 6px 12px; border-radius: 999px; cursor: pointer; transition: background 0.25s, color 0.25s, border-color 0.25s, transform 0.15s; } }
@media (max-width: 620px) { .jp-root .jp-sdots button.jp-on { background: linear-gradient(135deg, rgb(240, 122, 26), rgb(255, 164, 92)); border-color: transparent; color: rgb(255, 255, 255); box-shadow: rgba(240, 122, 26, 0.3) 0px 4px 10px; } }
@media (max-width: 620px) { .jp-root .jp-sdots button:active { transform: scale(0.95); } }
@media (max-width: 620px) { .jp-root .jp-bhead .jp-bh-r > .jp-os-fab, .jp-root .jp-os-fab { bottom: calc(78px + env(safe-area-inset-bottom,0px)) !important; } }
.jp-root .jp-act.jp-act-edit { border-color: rgb(147, 197, 253) !important; color: rgb(29, 78, 216) !important; background: linear-gradient(135deg, rgb(255, 255, 255), rgb(239, 246, 255)) !important; }
.jp-root .jp-card .jp-grid .jp-f.jp-frem { grid-column: 1 / -1; }
.jp-root .jp-card .jp-grid .jp-f.jp-frem b { font-size: 13px; font-weight: 600; color: var(--text); white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35; }
.jp-root .jp-card .jp-grid .jp-f.jp-fgap { display: none; }
.jp-root .jp-cs-row { display: flex; gap: 8px; align-items: stretch; }
.jp-root .jp-cs-row .jp-cs-opt { flex: 1 1 0%; }
.jp-root .jp-cs-skip { flex: 0 0 auto; border: 1.5px dashed rgb(147, 197, 253); background: rgb(239, 246, 255); color: rgb(29, 78, 216); font-family: inherit; font-size: 12px; font-weight: 800; border-radius: 12px; padding: 0px 12px; cursor: pointer; white-space: nowrap; }
.jp-root .jp-cs-skip:hover { background: rgb(219, 234, 254); border-style: solid; }
.jp-root .jp-cs-note { margin: 10px 2px 0px; font-size: 11.5px; color: var(--muted); line-height: 1.45; }
.jp-root .jp-menu.jp-ad-menu { background: rgb(255, 255, 255); min-width: 270px !important; padding: 8px !important; border-radius: 14px !important; border: 1px solid rgb(245, 199, 158) !important; box-shadow: rgba(240, 122, 26, 0.18) 0px 18px 40px, rgba(0, 0, 0, 0.08) 0px 4px 12px !important; }
.jp-root .jp-adm-h { font-size: 11px; font-weight: 800; letter-spacing: 0.4px; color: var(--orange-dark); text-transform: uppercase; padding: 4px 8px 8px; border-bottom: 1px dashed rgb(242, 211, 182); margin-bottom: 6px; }
.jp-root .jp-menu.jp-ad-menu .jp-adm-it { align-items: center; gap: 10px; width: 100%; border-radius: 10px; border: 1px solid transparent; background: transparent; text-align: left; cursor: pointer; transition: background 0.15s, border-color 0.15s, transform 0.15s; display: flex !important; padding: 9px 10px !important; }
.jp-root .jp-menu.jp-ad-menu .jp-adm-it + .jp-adm-it { margin-top: 4px; }
.jp-root .jp-adm-ic { flex: 0 0 34px; height: 34px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; }
.jp-root .jp-adm-it.jp-sum .jp-adm-ic { background: linear-gradient(135deg, rgb(255, 241, 228), rgb(255, 224, 196)); }
.jp-root .jp-adm-it.jp-del .jp-adm-ic { background: linear-gradient(135deg, rgb(254, 242, 242), rgb(254, 202, 202)); }
.jp-root .jp-adm-it.jp-dlv .jp-adm-ic { background: linear-gradient(135deg, rgb(236, 253, 243), rgb(187, 247, 208)); }
.jp-root .jp-adm-tx { flex: 1 1 0%; min-width: 0px; display: flex; flex-direction: column; }
.jp-root .jp-adm-tx b { font-size: 13.5px; color: var(--text); font-weight: 800; }
.jp-root .jp-adm-tx small { font-size: 11px; color: var(--muted); font-weight: 600; }
.jp-root .jp-adm-it em { font-style: normal; font-size: 11.5px; font-weight: 800; min-width: 26px; text-align: center; padding: 2px 8px; border-radius: 999px; }
.jp-root .jp-adm-it.jp-sum em { background: rgb(255, 241, 228); color: var(--orange-dark); }
.jp-root .jp-adm-it.jp-del em { background: rgb(254, 226, 226); color: rgb(185, 28, 28); }
.jp-root .jp-adm-it.jp-dlv em { background: rgb(220, 252, 231); color: rgb(21, 128, 61); }
.jp-root .jp-menu.jp-ad-menu .jp-adm-it.jp-sum:hover { background: rgb(255, 247, 240); border-color: rgb(245, 199, 158); }
.jp-root .jp-menu.jp-ad-menu .jp-adm-it.jp-del:hover { background: rgb(255, 245, 245); border-color: rgb(254, 202, 202); }
.jp-root .jp-menu.jp-ad-menu .jp-adm-it.jp-dlv:hover { background: rgb(243, 253, 246); border-color: rgb(187, 247, 208); }
.jp-root .jp-menu.jp-ad-menu .jp-adm-it:hover { transform: translateX(2px); }
@media (max-width: 620px) { .jp-root #jp-bigm .jp-bm-head { position: relative; gap: 10px; flex-wrap: wrap !important; padding-right: 56px !important; } }
@media (max-width: 620px) { .jp-root #jp-bigm .jp-bm-head > .jp-x { position: absolute; top: 12px; right: 12px; margin: 0px; } }
@media (max-width: 620px) { .jp-root #jp-bigm .jp-bm-head .jp-tabs { order: 3; flex: 1 1 100%; display: flex; } }
@media (max-width: 620px) { .jp-root #jp-bigm .jp-bm-head .jp-tabs button { flex: 1 1 0%; min-width: 0px; } }
@media (max-width: 620px) { .jp-root .jp-osum { min-width: 0px; width: 100%; grid-template-columns: minmax(0px, 1fr) !important; } }
@media (max-width: 620px) { .jp-root .jp-osec { min-width: 0px; overflow-x: auto; } }
@media (max-width: 620px) { .jp-root .jp-ostats { grid-template-columns: repeat(2, minmax(0px, 1fr)) !important; } }
@media (max-width: 620px) { .jp-root .jp-osec .jp-dtbl { min-width: 420px; } }
@media (min-width: 621px) and (max-width: 1000px) { .jp-root .jp-ostats { grid-template-columns: repeat(3, minmax(0px, 1fr)) !important; } }
.jp-root .jp-dtbl:has(.jp-osum) { width: 100%; table-layout: fixed; min-width: 0px !important; }
.jp-root .jp-dtbl:has(.jp-osum) > tbody > tr > td { padding: 0px !important; }
@keyframes jp-cardIn { 
  0% { opacity: 0; transform: translateY(10px) scale(0.97); }
  60% { opacity: 1; transform: translateY(-2px) scale(1.01); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-dropIn { 
  0% { opacity: 0; transform: translateY(-6px) scale(0.98); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-numPop { 
  0% { transform: scale(1); }
  40% { transform: scale(1.12); }
  100% { transform: scale(1); }
}
@keyframes jp-chipIn { 
  0% { opacity: 0; transform: scale(0.8); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-slideDown { 
  0% { opacity: 0; transform: translateY(-8px); }
  100% { opacity: 1; transform: none; }
}
@keyframes jp-fadeUpS { 
  0% { opacity: 0; transform: translateY(8px); }
  100% { opacity: 1; transform: none; }
}
.jp-root .jp-csug:not([hidden]), .jp-root .jp-os-res:not([hidden]), .jp-root #jp-q-sug:not([hidden]) { animation: 0.18s ease 0s 1 normal both running jp-dropIn; }
.jp-root .jp-modal.jp-n2 .jp-n2-olr, .jp-root .jp-adm-it { animation: 0.25s ease 0s 1 normal both running jp-chipIn; }
.jp-root .jp-menu.jp-ad-menu.jp-open .jp-adm-it:nth-of-type(2) { animation-delay: 0.04s; }
.jp-root .jp-menu.jp-ad-menu.jp-open .jp-adm-it:nth-of-type(3) { animation-delay: 0.08s; }
.jp-root .jp-modal.jp-vw .jp-vbody > * { animation: 0.35s ease 0s 1 normal both running jp-fadeUpS; }
.jp-root .jp-cs-row { animation: 0.3s ease 0s 1 normal both running jp-fadeUpS; }
.jp-root .jp-cs-row:nth-child(2) { animation-delay: 0.04s; }
.jp-root .jp-cs-row:nth-child(3) { animation-delay: 0.08s; }
.jp-root .jp-cs-row:nth-child(4) { animation-delay: 0.12s; }
.jp-root .jp-os-it { animation: 0.25s ease 0s 1 normal both running jp-fadeUpS; }
.jp-root .jp-qk-acc-h, .jp-root .jp-cs-opt, .jp-root .jp-cs-skip, .jp-root .jp-os-cs, .jp-root .jp-sdots button, .jp-root .jp-ad-dots, .jp-root .jp-os-fab, .jp-root .jp-n2-onl-add, .jp-root .jp-itact, .jp-root .jp-dtg, .jp-root .jp-adm-it { transition: background 0.2s, border-color 0.2s, color 0.2s, box-shadow 0.2s, transform 0.15s; }
.jp-root .jp-b, .jp-root .jp-btn-new, .jp-root .jp-btn-del, .jp-root .jp-act, .jp-root .jp-os-cs, .jp-root .jp-cs-skip, .jp-root .jp-n2-onl-add, .jp-root .jp-urgent, .jp-root .jp-qk-acc-h, .jp-root .jp-os-fab, .jp-root .jp-sdots button, .jp-root .jp-itact { -webkit-tap-highlight-color: transparent; }
.jp-root .jp-b:active, .jp-root .jp-btn-new:active, .jp-root .jp-btn-del:active, .jp-root .jp-act:active, .jp-root .jp-os-cs:active, .jp-root .jp-cs-skip:active, .jp-root .jp-n2-onl-add:active, .jp-root .jp-urgent:active, .jp-root .jp-itact:active, .jp-root .jp-os-fab:active, .jp-root .jp-cs-opt:active { transform: scale(0.96); }
.jp-root #jp-bigm .jp-tabs button { transition: background 0.25s, color 0.25s, box-shadow 0.25s; }
.jp-root .jp-dtbl tbody tr { transition: background 0.15s; }
.jp-root input, .jp-root select, .jp-root textarea { transition: border-color 0.2s, box-shadow 0.2s, background 0.2s; }
.jp-root .jp-os-fab { animation: 0.4s ease 0.3s 1 normal both running jp-chipIn; }
@media (prefers-reduced-motion: reduce) { .jp-root *, .jp-root ::before, .jp-root ::after { animation: auto ease 0s 1 normal none running none !important; transition: none !important; } }
.jp-root .jp-modal.jp-vw .jp-vdates { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px; }
.jp-root .jp-modal.jp-vw .jp-vdates span { font-size: 11px; font-weight: 700; color: var(--muted); background: rgb(255, 255, 255); border: 1px solid var(--line); border-radius: 999px; padding: 2px 9px; }
.jp-root .jp-modal.jp-vw .jp-vdates b { color: var(--text); font-weight: 800; }
.jp-root .jp-modal.jp-vw .jp-vh { position: relative; }
.jp-root .jp-modal.jp-vw .jp-vh > div:first-of-type { padding-right: 0px; }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vdates { position: absolute; top: 12px; right: 64px; margin: 0px; flex-direction: column; align-items: flex-end; gap: 4px; } }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vh h3 { padding-right: 190px; } }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vdates { position: absolute; top: 12px; right: 54px; margin: 0px; flex-direction: column; align-items: flex-end; gap: 3px; } }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vdates span { font-size: 9.5px; padding: 1px 7px; } }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vh h3, .jp-root .jp-modal.jp-vw .jp-vh > div > small { padding-right: 118px; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; } }
.jp-root .jp-ic { display: inline-grid; place-items: center; width: 20px; height: 20px; border-radius: 6px; background: rgb(255, 241, 228); color: rgb(217, 98, 10); vertical-align: -5px; margin-right: 6px; flex: 0 0 auto; line-height: 0; }
.jp-root .jp-ic svg { width: 12px; height: 12px; fill: none; stroke: currentcolor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.jp-root .jp-ic.jp-t-r { background: rgb(254, 226, 226); color: rgb(185, 28, 28); }
.jp-root .jp-ic.jp-t-g { background: rgb(220, 252, 231); color: rgb(21, 128, 61); }
.jp-root h3 .jp-ic, .jp-root h5 .jp-ic, .jp-root .jp-qk-acc-h .jp-ic, .jp-root .jp-n2-sub .jp-ic, .jp-root .jp-ilh .jp-ic, .jp-root .jp-osec h5 .jp-ic, .jp-root .jp-vsec h5 .jp-ic, .jp-root .jp-qv-rem > span .jp-ic, .jp-root .jp-balh .jp-ic, .jp-root .jp-n2-ph .jp-ic, .jp-root .jp-mp-h .jp-ic, .jp-root .jp-n2-mh .jp-ic, .jp-root .jp-adm-h .jp-ic { width: 22px; height: 22px; border-radius: 7px; }
.jp-root h3 .jp-ic svg, .jp-root h5 .jp-ic svg, .jp-root .jp-qk-acc-h .jp-ic svg { width: 13px; height: 13px; }
.jp-root button .jp-ic, .jp-root .jp-b .jp-ic, .jp-root .jp-btn-new .jp-ic, .jp-root .jp-act .jp-ic, .jp-root .jp-chip .jp-ic, .jp-root .jp-pc .jp-ic, .jp-root .jp-metal .jp-ic, .jp-root .jp-vst .jp-ic, .jp-root .jp-sno .jp-ic, .jp-root .jp-vdates .jp-ic, .jp-root small .jp-ic, .jp-root .jp-toast .jp-ic, .jp-root .jp-n2-pchips .jp-ic, .jp-root .jp-sdots .jp-ic, .jp-root .jp-dchip .jp-ic, .jp-root .jp-os-st .jp-ic, .jp-root .jp-qk-tag .jp-ic, .jp-root .jp-urgent .jp-ic, .jp-root .jp-n2-olb .jp-ic, .jp-root .jp-n2-strip .jp-ic, .jp-root .jp-sit .jp-ic, .jp-root .jp-subrow .jp-ic, .jp-root .jp-tags .jp-ic, .jp-root .jp-flbl .jp-ic, .jp-root .jp-rc .jp-ic, .jp-root .jp-n2-md .jp-ic, .jp-root .jp-vdates span .jp-ic { width: auto; height: auto; margin-right: 4px; vertical-align: -2px; color: inherit; background: none !important; }
.jp-root button .jp-ic svg, .jp-root .jp-b .jp-ic svg, .jp-root .jp-btn-new .jp-ic svg, .jp-root .jp-act .jp-ic svg, .jp-root .jp-urgent .jp-ic svg { width: 14px; height: 14px; }
.jp-root .jp-chip .jp-ic svg, .jp-root .jp-pc .jp-ic svg, .jp-root .jp-metal .jp-ic svg, .jp-root .jp-vdates .jp-ic svg, .jp-root small .jp-ic svg, .jp-root .jp-n2-pchips .jp-ic svg, .jp-root .jp-subrow .jp-ic svg, .jp-root .jp-tags .jp-ic svg, .jp-root .jp-sit .jp-ic svg, .jp-root .jp-n2-md .jp-ic svg, .jp-root .jp-flbl .jp-ic svg { width: 12px; height: 12px; }
.jp-root .jp-n2-pchips .jp-ic, .jp-root .jp-vdates .jp-ic, .jp-root .jp-n2-md .jp-ic { color: var(--orange-dark); }
.jp-root .jp-menu button .jp-ic { width: 22px; height: 22px; border-radius: 7px; margin-right: 8px; color: rgb(217, 98, 10); vertical-align: -6px; background: rgb(255, 241, 228) !important; }
.jp-root .jp-menu button.jp-del .jp-ic, .jp-root .jp-menu button .jp-ic.jp-t-r { color: rgb(185, 28, 28); background: rgb(254, 226, 226) !important; }
.jp-root .jp-menu button .jp-ic svg { width: 13px; height: 13px; }
.jp-root .jp-adm-ic .jp-ic, .jp-root .jp-cs-ic .jp-ic, .jp-root .jp-si .jp-ic, .jp-root .jp-os-fab .jp-ic, .jp-root .jp-mp-tab .jp-ic, .jp-root .jp-cs-av .jp-ic { margin: 0px; width: auto; height: auto; color: inherit; background: none !important; }
.jp-root .jp-adm-ic .jp-ic svg, .jp-root .jp-cs-ic .jp-ic svg, .jp-root .jp-si .jp-ic svg { width: 18px; height: 18px; }
.jp-root .jp-adm-it.jp-sum .jp-adm-ic { color: rgb(217, 98, 10); }
.jp-root .jp-adm-it.jp-del .jp-adm-ic { color: rgb(185, 28, 28); }
.jp-root .jp-adm-it.jp-dlv .jp-adm-ic { color: rgb(21, 128, 61); }
.jp-root .jp-cs-ic { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 10px; background: rgb(255, 241, 228); color: rgb(217, 98, 10); }
.jp-root .jp-os-fab .jp-ic svg { width: 24px; height: 24px; stroke: rgb(255, 255, 255); stroke-width: 2.2; }
.jp-root .jp-si { color: rgb(217, 98, 10); }
.jp-root .jp-btn-new .jp-ic, .jp-root .jp-b.jp-pri .jp-ic, .jp-root .jp-b.jp-save .jp-ic, .jp-root .jp-os-fab .jp-ic, .jp-root .jp-sdots button.jp-on .jp-ic { color: rgb(255, 255, 255); }
.jp-root .jp-qf > span .jp-ic, .jp-root .jp-lb .jp-ic, .jp-root .jp-vf > span .jp-ic, .jp-root .jp-f > span .jp-ic, .jp-root label > .jp-ic { margin-right: 6px; }
.jp-root input.jp-ph-ic { background-image: var(--phi); background-repeat: no-repeat; background-size: 16px 16px; background-position: 12px center; padding-left: 36px !important; }
.jp-root input.jp-ph-ic:not(:placeholder-shown) { background-image: none; padding-left: 14px !important; }
.jp-root .jp-osrch input.jp-ph-ic { background-position: 14px center; padding-left: 38px !important; }
.jp-root .jp-card .jp-grid .jp-f > span .jp-ic { width: auto; height: auto; margin-right: 3px; vertical-align: -2px; background: none !important; }
.jp-root .jp-card .jp-grid .jp-f > span .jp-ic svg { width: 11px; height: 11px; }
.jp-root .jp-vdates .jp-ic { margin-right: 3px !important; }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vh .jp-vtags { padding-right: 180px; } }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vh .jp-vtags { padding-right: 0px; } }
.jp-root .jp-vtags .jp-ic svg, .jp-root .jp-dchip .jp-ic svg { width: 11px; height: 11px; }
.jp-root .jp-card .jp-grid .jp-f > span .jp-ic, .jp-root .jp-f span.jp-ic, .jp-root .jp-vf span.jp-ic, .jp-root .jp-qf span.jp-ic, .jp-root .jp-lb span.jp-ic { display: inline-grid !important; }
.jp-root .jp-vdates span.jp-ic, .jp-root .jp-n2-pchips em .jp-ic, .jp-root .jp-chip span.jp-ic { border-radius: 0px; border: 0px !important; padding: 0px !important; }
.jp-root input.jp-ph-ic, .jp-root .jp-modal input.jp-ph-ic, .jp-root .jp-modal.jp-n2 .jp-n2-top .jp-qf input.jp-ph-ic { background-image: var(--phi) !important; background-repeat: no-repeat !important; background-size: 16px 16px !important; background-position: 13px center !important; padding-left: 38px !important; }
.jp-root input.jp-ph-ic:not(:placeholder-shown), .jp-root .jp-modal.jp-n2 .jp-n2-top .jp-qf input.jp-ph-ic:not(:placeholder-shown) { background-image: none !important; padding-left: 14px !important; }
@media (max-width: 620px) { .jp-root #jp-board, .jp-root #jp-board * { user-select: none; } }
.jp-root .jp-modal.jp-vw .jp-vnm { display: flex; align-items: baseline; gap: 10px; flex-wrap: wrap; min-width: 0px; }
.jp-root .jp-modal.jp-vw .jp-vnm h3 { margin: 0px; min-width: 0px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 0px !important; }
.jp-root .jp-modal.jp-vw .jp-vnm small { white-space: nowrap; font-size: 12.5px; color: var(--muted); font-weight: 600; padding-right: 0px !important; }
@media (min-width: 561px) { .jp-root .jp-modal.jp-vw .jp-vnm { padding-right: 180px; } }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vnm { padding-right: 112px; flex-direction: row; gap: 0px 8px; } }
@media (max-width: 560px) { .jp-root .jp-modal.jp-vw .jp-vnm small { font-size: 11.5px; } }
@media (max-width: 620px) { .jp-root .jp-bmain .jp-board { grid-template-columns: repeat(3, 84%) !important; gap: 8px !important; } }
@media (max-width: 620px) { .jp-root .jp-board .jp-col:first-child { scroll-snap-align: start; } }
@media (max-width: 620px) { .jp-root .jp-board .jp-col:nth-child(2) { scroll-snap-align: center; } }
@media (max-width: 620px) { .jp-root .jp-board .jp-col:last-child { scroll-snap-align: end; } }
@media (max-width: 620px) { .jp-root .jp-bmain .jp-board { touch-action: pan-y; } }
@media (max-width: 620px) { .jp-root .jp-bmain .jp-board .jp-col { touch-action: pan-y; } }
@media (max-width: 620px) { .jp-root .jp-bmain .jp-board { grid-template-columns: 92% 84% 92% !important; } }
@media (max-width: 620px) { .jp-root #jp-board .jp-card { -webkit-user-drag: none; } }
@media (hover: hover) and (pointer: fine) { .jp-root button, .jp-root .jp-b, .jp-root .jp-act, .jp-root .jp-btn-new, .jp-root .jp-btn-del, .jp-root .jp-ad-dots, .jp-root .jp-os-cs, .jp-root .jp-cs-skip, .jp-root .jp-cs-opt, .jp-root .jp-n2-onl-add, .jp-root .jp-n2-onl-del, .jp-root .jp-itact, .jp-root .jp-dtg, .jp-root .jp-dots, .jp-root .jp-x, .jp-root .jp-urgent, .jp-root .jp-qk-acc-h, .jp-root .jp-mp-tab, .jp-root .jp-adm-it, .jp-root .jp-sdots button, .jp-root .jp-plus, .jp-root .jp-ibtn, .jp-root .jp-tabs button, .jp-root .jp-chip, .jp-root .jp-os-it, .jp-root .jp-menu button { transition: background 0.18s, border-color 0.18s, color 0.18s, box-shadow 0.2s, transform 0.18s, filter 0.18s !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-btn-new:hover, .jp-root .jp-b.jp-pri:hover, .jp-root .jp-plus:hover { transform: translateY(-2px); filter: brightness(1.06) saturate(1.05); box-shadow: rgba(240, 122, 26, 0.38) 0px 8px 20px !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-b.jp-danger:hover { transform: translateY(-2px); filter: brightness(1.05); box-shadow: rgba(220, 38, 38, 0.35) 0px 8px 20px !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-b:not(.jp-pri):not(.jp-save):not(.jp-danger):hover, .jp-root .jp-qk-print:hover, .jp-root .jp-btn-del:hover, .jp-root .jp-ad-dots:hover, .jp-root .jp-ibtn:hover { transform: translateY(-1px); background: rgb(255, 241, 228) !important; border-color: var(--orange) !important; color: var(--orange-dark) !important; box-shadow: rgba(240, 122, 26, 0.18) 0px 4px 12px !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-act:hover { transform: translateY(-1px); filter: brightness(0.97); box-shadow: rgba(0, 0, 0, 0.12) 0px 4px 12px !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-act.jp-act-edit:hover { background: rgb(219, 234, 254) !important; border-color: rgb(59, 130, 246) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-os-cs:hover, .jp-root .jp-n2-onl-add:hover { transform: translateY(-1px); background: var(--orange) !important; color: rgb(255, 255, 255) !important; border-color: var(--orange) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-os-cs:hover .jp-ic, .jp-root .jp-n2-onl-add:hover .jp-ic { color: rgb(255, 255, 255); } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-cs-skip:hover { background: rgb(59, 130, 246) !important; color: rgb(255, 255, 255) !important; border-color: rgb(59, 130, 246) !important; border-style: solid !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-cs-skip:hover .jp-ic { color: rgb(255, 255, 255); } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-cs-opt:hover:not(:disabled) { transform: translateX(3px); box-shadow: rgba(240, 122, 26, 0.15) 0px 4px 12px; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-itact:hover, .jp-root .jp-dots:hover, .jp-root .jp-dtg:hover { transform: scale(1.08); background: rgb(255, 241, 228) !important; border-color: var(--orange) !important; color: var(--orange-dark) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-x:hover { transform: rotate(90deg); background: rgb(254, 226, 226) !important; color: rgb(185, 28, 28) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-tabs button:not(.jp-on):hover, .jp-root .jp-sdots button:not(.jp-on):hover { background: rgb(255, 241, 228) !important; color: var(--orange-dark) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-menu button:hover { padding-left: 14px; background: rgb(255, 244, 234) !important; color: var(--orange-dark) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-menu button.jp-del:hover { background: rgb(254, 242, 242) !important; color: rgb(185, 28, 28) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-os-it:hover { background: rgb(255, 244, 234) !important; } }
@media (hover: hover) and (pointer: fine) { .jp-root .jp-card .jp-act:hover, .jp-root .jp-card .jp-dots:hover { z-index: 2; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw { scrollbar-width: none; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw::-webkit-scrollbar { display: none; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vbody { gap: 7px !important; padding: 9px 16px 10px !important; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vsec { padding: 7px 11px !important; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vsec h5 { margin-bottom: 5px !important; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vgrid { row-gap: 4px !important; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vamt .jp-va { padding: 7px 11px; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vitem { padding: 6px 10px; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vitems { gap: 5px; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-vh { padding-top: 11px !important; padding-bottom: 11px !important; } }
@media (min-width: 621px) { .jp-root .jp-modal.jp-vw .jp-modal-f { padding-top: 9px; padding-bottom: 9px; } }
.jp-root .jp-dr-wrap { position: relative; }
.jp-root .jp-dr-btn { height: 38px; font-size: 13px; padding: 0px 14px !important; }
.jp-root .jp-menu.jp-dr-menu { right: 0px; left: auto; top: calc(100% + 6px); min-width: 280px; max-height: 360px; overflow: auto; z-index: 30; padding: 8px !important; border-radius: 14px !important; border: 1px solid rgb(245, 199, 158) !important; }
.jp-root .jp-dr-it { display: flex; align-items: center; gap: 8px; padding: 8px 10px; border-radius: 10px; cursor: pointer; transition: background 0.15s; }
.jp-root .jp-dr-it:hover { background: rgb(255, 244, 234); }
.jp-root .jp-dr-tx { flex: 1 1 0%; min-width: 0px; display: flex; flex-direction: column; }
.jp-root .jp-dr-tx b { font-size: 13.5px; }
.jp-root .jp-dr-tx small { font-size: 11px; color: var(--muted); }
.jp-root .jp-dr-del { flex: 0 0 26px; height: 26px; border-radius: 8px; border: 1px solid rgb(254, 202, 202); background: rgb(254, 242, 242); color: rgb(185, 28, 28); cursor: pointer; }
.jp-root .jp-dr-del:hover { background: rgb(220, 38, 38); color: rgb(255, 255, 255); }
.jp-root .jp-drq { position: fixed; inset: 0px; z-index: 300; display: none; align-items: center; justify-content: center; background: rgba(15, 18, 30, 0.35); padding: 16px; }
.jp-root .jp-drq.jp-open { display: flex; animation: 0.18s ease 0s 1 normal none running jp-ovIn; }
.jp-root .jp-drq-box { background: var(--surface,#fff); border-radius: 16px; max-width: 380px; width: 100%; padding: 18px 18px 14px; text-align: center; box-shadow: rgba(0, 0, 0, 0.25) 0px 24px 60px; animation: 0.25s cubic-bezier(0.2, 0.9, 0.3, 1.2) 0s 1 normal none running jp-mdIn; }
.jp-root .jp-drq-ic { width: 44px; height: 44px; margin: 0px auto 6px; display: grid; place-items: center; border-radius: 12px; background: rgb(255, 241, 228); font-size: 20px; }
.jp-root .jp-drq-ic .jp-ic { margin: 0px; background: none !important; }
.jp-root .jp-drq-ic .jp-ic svg { width: 22px; height: 22px; }
.jp-root .jp-drq-box h4 { margin: 4px 0px 6px; font-size: 17px; color: var(--text); }
.jp-root .jp-drq-box p { margin: 0px 0px 14px; font-size: 12.5px; color: var(--muted); line-height: 1.5; }
.jp-root .jp-drq-f { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; }
.jp-root .jp-drq-f .jp-b { padding: 8px 14px; font-size: 12.5px; }
.jp-root .jp-menu.jp-dr-menu .jp-dr-del { height: 26px; place-items: center; flex: 0 0 26px !important; width: 26px !important; padding: 0px !important; display: grid !important; border-radius: 8px !important; border: 1px solid rgb(254, 202, 202) !important; background: rgb(254, 242, 242) !important; color: rgb(185, 28, 28) !important; margin: 0px !important; }
.jp-root .jp-menu.jp-dr-menu .jp-dr-del:hover { background: rgb(220, 38, 38) !important; color: rgb(255, 255, 255) !important; padding-left: 0px !important; }
.jp-root .jp-drq-box { max-width: 440px; }
.jp-root .jp-drq-f { flex-wrap: nowrap; }
@media (max-width: 420px) { .jp-root .jp-drq-f { flex-wrap: wrap; } }
@media (max-width: 420px) { .jp-root .jp-drq-f .jp-b { flex: 1 1 100%; } }
.jp-root .jp-sbar, .jp-root .jp-dedge { display: none; }
@media (max-width: 620px) { .jp-root .jp-sbar { display: block; position: relative; height: 6px; margin: 8px auto 2px; width: min(240px, 70%); background: rgb(241, 228, 214); border-radius: 999px; flex-shrink: 0; touch-action: none; cursor: pointer; } }
@media (max-width: 620px) { .jp-root .jp-sbar::before { content: ""; position: absolute; inset: -10px 0px; } }
@media (max-width: 620px) { .jp-root .jp-sbar > i { position: absolute; top: 0px; left: 0px; height: 100%; border-radius: 999px; background: linear-gradient(90deg, rgb(240, 122, 26), rgb(255, 164, 92)); box-shadow: rgba(240, 122, 26, 0.35) 0px 2px 6px; will-change: transform; } }
@keyframes jp-dePulse { 
  0% { filter: brightness(1); }
  100% { filter: brightness(1.25); }
}

/* ===== JU additions on top of the ported design (structure glue + JU-only controls) ===== */
.jp-root { position: relative; }
.jp-root a { text-decoration: none; }
/* rows stay <tr>/<td> for the old JS; they do not affect layout */
.jp-root table.jp-tbl, .jp-root table.jp-tbl > tbody, .jp-root table.jp-tbl > tbody > tr, .jp-root table.jp-tbl > tbody > tr > td { display: contents; }
.jp-root table.jp-tbl > thead, .jp-root table.jp-tbl > tfoot { display: none !important; }
.jp-root .jp-empty-in, .jp-root .jp-loading-in { border: 1.5px dashed var(--dash, #D9DDE3); border-radius: 12px; padding: 22px 12px; text-align: center; color: var(--muted, #6B7280); font-size: 13px; font-weight: 600; background: rgba(255,255,255,.6); }
.jp-root .jp-err-in { color: var(--red, #DC2626); }
.jp-root table.jp-tbl > tbody > tr:not(.jp-row):not(.jp-empty) > td { display: block; border: 1.5px dashed var(--dash, #D9DDE3); border-radius: 12px; padding: 22px 12px; text-align: center; color: var(--muted, #6B7280); font-size: 13px; }
/* card top: checkbox + actions that are forms / links */
.jp-root .jp-card-top .jp-chk { width: 15px; height: 15px; margin: 0 2px 0 0; accent-color: var(--orange, #F07A1A); flex: 0 0 auto; cursor: pointer; }
.jp-root .jp-act-form { margin: 0; display: inline-flex; flex: 0 0 auto; }
.jp-root a.jp-act, .jp-root span.jp-act { display: inline-flex; align-items: center; }
.jp-root .jp-act-done { cursor: default; opacity: .75; }
.jp-root .jp-act-done:hover { transform: none !important; }
.jp-root .jp-menu a { display: flex; align-items: center; width: 100%; text-align: left; border: 0; background: transparent; color: var(--text, #1F2937); padding: 8px 12px; border-radius: 6px; font-size: 13px; transition: background .15s, color .15s, padding-left .15s; }
.jp-root .jp-menu a:hover { background: var(--orange-soft, #FFE7D1); padding-left: 16px; color: var(--text, #1F2937); }
.jp-root .jp-menu a.jp-del { color: var(--red, #DC2626); }
.jp-root .jp-f b small, .jp-root .jp-vf b small { font-size: 11px; color: var(--muted, #6B7280); font-weight: 600; margin-left: 4px; }
.jp-root .jp-card.jp-hl { box-shadow: 0 0 0 3px rgba(22,163,74,.35), 0 10px 22px rgba(22,163,74,.18) !important; }
/* All orders column controls (status filter + select all) */
.jp-root .jp-col-ctl { display: flex; align-items: center; gap: 8px; margin-top: 3px; }
.jp-root .jp-col-ctl select { height: 26px; border: 1px solid var(--line, #ECEEF2); border-radius: 8px; font-size: 11.5px; font-weight: 600; padding: 0 6px; background: #fff; color: var(--text, #1F2937); max-width: 130px; }
.jp-root .jp-col-ctl .jp-all { display: inline-flex; align-items: center; gap: 4px; margin: 0; font-size: 11.5px; font-weight: 600; color: var(--muted, #6B7280); cursor: pointer; }
.jp-root .jp-col-ctl .jp-all input { width: 14px; height: 14px; accent-color: var(--orange, #F07A1A); }
/* header: search input, date range, show entries */
.jp-root .jp-osrch .jp-srch-in { width: 100%; }
.jp-root .jp-dr-btn { display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; font-size: 12.5px; font-weight: 600; }
.jp-root .jp-dr-btn .jp-ic { margin-right: 2px; }
.jp-root .jp-adm-show { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 10px 4px; margin-top: 6px; border-top: 1px dashed var(--line, #ECEEF2); font-size: 12px; font-weight: 600; color: var(--muted, #6B7280); }
.jp-root .jp-adm-show select { height: 30px; border: 1px solid var(--line, #ECEEF2); border-radius: 8px; padding: 0 6px; font-weight: 700; width: auto; }
.jp-root .jp-paging { margin-top: 8px; font-size: 12px; }
.jp-root .jp-paging:empty { display: none; }
.jp-root .jp-paging .pagination { justify-content: center; flex-wrap: wrap; gap: 4px; margin: 0; }
.jp-root .jp-paging .page-link { border-radius: 8px !important; border: 1px solid var(--line, #ECEEF2); color: var(--text, #1F2937); font-weight: 600; font-size: 12px; padding: 4px 9px; }
.jp-root .jp-paging .page-item.active .page-link { background: var(--orange, #F07A1A); border-color: var(--orange, #F07A1A); color: #fff; }
/* drag & drop + touch ghost */
.jp-root .jp-board.jp-drag-on .jp-col.jp-over { outline: 2px dashed var(--orange, #F07A1A); outline-offset: -6px; }
.jp-root .jp-card.jp-dragging { opacity: .4; }
.jp-tghost { position: fixed; z-index: 2000; pointer-events: none; opacity: .95; transform: rotate(2deg) scale(1.02); }
.jp-tghost .jp-card { box-shadow: 0 18px 40px rgba(0,0,0,.25) !important; }
body.jp-tdrag { user-select: none; -webkit-user-select: none; }
/* search on phone: the 🔍 button opens the field */
.jp-root .jp-bhead.jp-srch-open .jp-osrch { display: block !important; position: absolute; left: 10px; right: 10px; top: calc(100% + 6px); z-index: 40; }
.jp-root .jp-bhead { position: relative; }
/* delivered popup */
.jp-root .jp-dv-show { height: 30px; }
.jp-root .jp-dtbl .jp-dv-act { position: relative; }
.jp-root .jp-dvm { position: fixed; display: none; z-index: 60; min-width: 160px; }
.jp-root .jp-dvm.jp-open { display: block; }
/* bulk actions bar */
.jp-root .bulk-actions-bar { position: fixed; left: 50%; bottom: calc(18px + env(safe-area-inset-bottom, 0px)); transform: translateX(-50%); z-index: 1040; background: #1F2937; color: #fff; padding: 8px 12px; border-radius: 999px; align-items: center; gap: 8px; box-shadow: 0 14px 34px rgba(0,0,0,.3); max-width: calc(100vw - 20px); flex-wrap: wrap; justify-content: center; font-size: 13px; }
.jp-root .bulk-actions-bar .count-badge { background: var(--orange, #F07A1A); width: 24px; height: 24px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; margin-right: 6px; }
.jp-root .bulk-actions-bar .btn { border-radius: 999px; font-size: 12px; font-weight: 600; height: 32px; padding: 0 12px; border: 0; color: #fff; margin: 0 2px; }
.jp-root .bulk-actions-bar #bulk_assign_btn { background: var(--orange, #F07A1A); }
.jp-root .bulk-actions-bar #bulk_delete_btn { background: #DC2626; }
.jp-root .bulk-actions-bar #bulk_print_btn { background: #0F766E; }
.jp-root .bulk-actions-bar .jp-bulk-x { color: #fff; opacity: .7; }
/* View detail: bootstrap modal shell, design inside */
.jp-root .jp-vmodal .modal-dialog { max-width: 580px; }
.jp-root .jp-vmodal .modal-content { background: transparent; border: 0; box-shadow: none; }
.jp-root .jp-vmodal .modal-body { padding: 0; }
.jp-root .jp-vmodal .modal-body > .text-center { background: #fff; border-radius: 16px; }
.jp-root .jp-vmodal .jp-modal { max-width: none !important; margin: 0 auto; }
.jp-root .jp-vmodal .jp-vimg { width: 46px; height: 46px; border-radius: 12px; object-fit: cover; cursor: zoom-in; flex: 0 0 auto; }
.jp-root .jp-vmodal .jp-vrem { margin-top: 10px; padding: 9px 12px; border-radius: 10px; background: #FFF7ED; color: #9A3412; font-size: 12.5px; font-weight: 600; display: flex; gap: 6px; align-items: flex-start; }
.jp-root .jp-vmodal .jp-modal-f a.jp-b { display: inline-flex; align-items: center; }
/* delivered popup header: close button to the right (no tabs in JU) */
.jp-root .jp-bm-head .jp-x { margin-left: auto; }
@media (max-width: 620px) {
    .jp-root .jp-dr-btn #daterange-text { display: none; }
    .jp-root .jp-bhead { flex-wrap: nowrap !important; }
    .jp-root .jp-bhead .jp-bh-r { flex-wrap: nowrap; gap: 6px; }
    .jp-root .jp-card-top .jp-chk { display: none; }
    .jp-root .jp-bhead.jp-srch-open .jp-osrch { position: fixed; top: 10px; left: 10px; right: 10px; z-index: 1050; }
    .jp-root .bulk-actions-bar { bottom: calc(74px + env(safe-area-inset-bottom, 0px)); border-radius: 16px; }
    .jp-root .jp-vmodal .modal-dialog { margin: 8px; }
}

/* ===== NEW ADDITION glue: popups that live on the board page ===== */
.jp-root .jp-overlay { z-index: 1046; }
.jp-root .jp-dov { z-index: 1044; }
.jp-root #jp-dvm.jp-dvm { z-index: 1047 !important; }
/* create / edit popup = old page inside an iframe */
.jp-root .jp-ov-order { padding: 12px; }
.jp-root .jp-ofr { position: relative; width: min(900px, 96vw); height: min(94vh, 1000px); border-radius: 18px; overflow: hidden; background: transparent; animation: jp-mdIn .28s cubic-bezier(.2,.9,.3,1.2); }
.jp-root .jp-ofr iframe { width: 100%; height: 100%; border: 0; background: transparent; display: block; }
.jp-root .jp-ofr-load { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; gap: 8px; background: #fff; border-radius: 18px; color: var(--muted, #6B7280); font-weight: 600; }
.jp-root .jp-ofr.jp-ready .jp-ofr-load { display: none; }
.jp-root .jp-ofr.jp-print { width: min(1000px, 96vw); background: #fff; }
/* drafts button + menu */
.jp-root .jp-dr-wrap { position: relative; }
.jp-root .jp-dr-btn2 { display: inline-flex; align-items: center; gap: 4px; font-size: 12.5px; font-weight: 600; white-space: nowrap; }
.jp-root .jp-dr-btn2 .jp-count { margin-left: 2px; }
.jp-root .jp-dr-menu { right: 0; left: auto; top: calc(100% + 6px); min-width: 260px; z-index: 30; }
/* change status: skip buttons only when backend route exists */
.jp-root .jp-csm .jp-cs-skip[disabled] { display: none; }
/* search results above the board */
.jp-root .jp-osrch { position: relative; }
.jp-root .jp-os-res { z-index: 45; }
@media (max-width: 620px) {
    .jp-root .jp-ov-order { padding: 0; }
    .jp-root .jp-ofr { width: 100vw; height: 100%; border-radius: 0; }
    .jp-root .jp-dr-btn2 { font-size: 0; gap: 0; }
    .jp-root .jp-dr-btn2 .jp-ic { margin: 0; }
    .jp-root .jp-dr-btn2 .jp-count { font-size: 10px; }
}

/* ===== Old popups (Assign karigar, Add karigar, Issue material): same markup, design colours ===== */
#assignModal .modal-content, #addKarigarModal .modal-content, #karigar_issue_form.modal-content {
    border-radius: 18px !important; border: 0 !important; box-shadow: 0 24px 60px rgba(30, 34, 53, .22) !important; overflow: hidden; font-family: Outfit, system-ui, -apple-system, "Segoe UI", sans-serif;
}
#assignModal .modal-header, #addKarigarModal .modal-header { background: linear-gradient(135deg, #FFF3E6, #fff); border-bottom: 1px solid #F1DFCD; }
#assignModal .btn-primary, #karigar_issue_form .btn-primary {
    background: linear-gradient(135deg, #F07A1A, #FFA45C) !important; border: 0 !important; box-shadow: 0 8px 18px rgba(240, 122, 26, .28) !important;
}
#addKarigarModal .btn-success { background: linear-gradient(135deg, #16A34A, #22C55E) !important; border: 0 !important; box-shadow: 0 8px 18px rgba(22, 163, 74, .25) !important; }
#assignModal input:focus, #addKarigarModal .form-control:focus, #karigar_issue_form .karigar-input:focus, #karigar_issue_form .karigar-textarea:focus {
    border-color: #F07A1A !important; box-shadow: 0 0 0 3px rgba(240, 122, 26, .15) !important; outline: none;
}
#karigar_issue_form .metal-tab-btn.active { border-color: #F07A1A !important; }
</style>
