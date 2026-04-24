<!DOCTYPE html>
<html lang="<?php echo lang_code(); ?>" prefix="og: https://ogp.me/ns#">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php view_head(); ?>
</head>

<body style="margin:0;background:linear-gradient(160deg,#f0f9ff 0%,#e0f2fe 40%,#dbeafe 100%);min-height:100vh;font-family:sans-serif;">
<style>
.v2-auth-grid{display:grid;grid-template-columns:1fr 1fr;min-height:100vh;align-items:stretch;}
@media (max-width:900px){.v2-auth-grid{grid-template-columns:1fr;}.v2-auth-right{display:none !important;}}
.v2-auth-form-col{display:flex;align-items:center;justify-content:center;padding:2rem;}
.v2-auth-form-wrap{width:100%;max-width:400px;}
.v2-auth-card{background:#fff;border-radius:24px;box-shadow:0 4px 24px rgba(30,64,175,0.08);padding:2rem 1.75rem;margin-top:1.5rem;}
.v2-auth-card .v2-auth-head{margin-bottom:1.5rem;}
.v2-auth-card .v2-auth-head h1{margin:0 0 0.75rem;font-size:1.5rem;font-weight:700;color:#1d4ed8;line-height:1.25;}
.v2-auth-card .v2-auth-head p{margin:0;color:#64748b;}
.v2-auth-mode-switch{display:flex;gap:0;width:100%;padding:4px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:14px;box-sizing:border-box;margin-top:0.25rem;}
.v2-auth-mode-btn{flex:1;min-width:0;text-align:center;padding:0.65rem 0.5rem;font-size:0.8125rem;font-weight:600;line-height:1.25;border-radius:10px;text-decoration:none;color:#475569;background:transparent;border:none;cursor:pointer;transition:background .15s ease,color .15s ease,box-shadow .15s ease;font-family:inherit;}
a.v2-auth-mode-btn{color:#475569;}
.v2-auth-mode-btn:hover:not(.v2-auth-mode-btn--active){background:#e2e8f0;color:#1e293b;}
.v2-auth-mode-btn:focus-visible{outline:2px solid #2563eb;outline-offset:2px;}
.v2-auth-mode-btn--active{background:#fff;color:#2563eb;box-shadow:0 1px 4px rgba(30,64,175,0.12);cursor:default;}
span.v2-auth-mode-btn--active{user-select:none;}
@media (min-width:420px){.v2-auth-mode-btn{font-size:0.875rem;padding:0.7rem 0.75rem;}}
.v2-auth-back{display:inline-flex;align-items:center;gap:0.35rem;padding:0.55rem 1rem;font-size:0.875rem;font-weight:600;color:#1d4ed8;text-decoration:none;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 1px 3px rgba(30,64,175,0.08);transition:background .15s ease,border-color .15s ease,box-shadow .15s ease,color .15s ease;}
.v2-auth-back:hover{background:#f8fafc;border-color:#cbd5e1;color:#1d4ed8;box-shadow:0 2px 8px rgba(30,64,175,0.12);text-decoration:none;}
.v2-auth-back:focus-visible{outline:2px solid #2563eb;outline-offset:2px;}
.v2-auth-form-wrap > .v2-auth-back{margin-bottom:0.75rem;}
.v2-auth-btn-outline{display:block;width:100%;box-sizing:border-box;padding:0.75rem 1rem;text-align:center;font-size:0.875rem;font-weight:600;color:#2563eb;text-decoration:none;background:#fff;border:1px solid #bfdbfe;border-radius:12px;transition:background .15s ease,border-color .15s ease,color .15s ease;}
.v2-auth-btn-outline:hover{background:#eff6ff;border-color:#93c5fd;color:#1d4ed8;text-decoration:none;}
.v2-auth-btn-outline:focus-visible{outline:2px solid #2563eb;outline-offset:2px;}
.v2-auth-head-actions{margin-top:0.75rem;}
.v2-auth-head-actions .v2-auth-btn-outline{margin-top:0;}
.v2-auth-after-form{margin-top:1.25rem;}
.v2-auth-card .v2-auth-divider{margin:1.25rem 0;color:#94a3b8;text-align:center;}
.v2-auth-card .v2-auth-form{margin-top:1.25rem;}
.v2-auth-card .v2-auth-field{margin-bottom:1.25rem;}
.v2-auth-card .v2-auth-field label{display:block;margin-bottom:0.375rem;color:#475569;}
.v2-auth-card .v2-auth-actions{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.5rem;}
.v2-auth-card .v2-auth-footer{margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0;text-align:center;}
.v2-auth-input{width:100%;padding:0.75rem 1rem;border:1px solid #e2e8f0;border-radius:12px;box-sizing:border-box;background:#fff;}
.v2-auth-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,0.15);}
.v2-auth-btn{width:100%;padding:0.875rem 1rem;background:#2563eb;color:#fff;border:none;border-radius:12px;cursor:pointer;}
.v2-auth-btn:hover{background:#1d4ed8;}
.v2-auth-link{color:#2563eb;text-decoration:none;}
.v2-auth-link:hover{text-decoration:underline;}
.v2-auth-err{color:#b91c1c;margin-top:0.25rem;display:block;}
.v2-auth-msg{margin-bottom:1.25rem;padding:0.875rem 1rem;border-radius:12px;}
.v2-auth-msg.err{background:#fef2f2;color:#b91c1c;}
.v2-auth-msg.ok{background:#f0fdf4;color:#15803d;}
.v2-auth-right{display:flex;flex-direction:column;justify-content:center;background:rgba(255,255,255,0.5);backdrop-filter:blur(8px);padding:2rem 2.5rem;position:relative;overflow:hidden;}
.v2-auth-right::before{content:'';position:absolute;width:220px;height:220px;border-radius:50%;background:rgba(147,197,253,0.35);top:8%;right:5%;filter:blur(48px);animation:v2-float 10s ease-in-out infinite;}
.v2-auth-right::after{content:'';position:absolute;width:180px;height:180px;border-radius:50%;background:rgba(191,219,254,0.45);bottom:15%;left:8%;filter:blur(40px);animation:v2-float 8s ease-in-out infinite reverse;}
@keyframes v2-float{0%,100%{transform:translate(0,0) scale(1);}50%{transform:translate(12px,-10px) scale(1.05);}}
.v2-auth-right-inner{max-width:360px;margin:0 auto;position:relative;z-index:1;}
.v2-auth-right-shapes{position:absolute;inset:0;pointer-events:none;z-index:0;}
.v2-auth-right-shapes span{position:absolute;border-radius:50%;background:rgba(255,255,255,0.6);}
.v2-auth-right-shapes span:nth-child(1){width:12px;height:12px;top:25%;right:15%;animation:v2-dot 4s ease-in-out infinite;}
.v2-auth-right-shapes span:nth-child(2){width:8px;height:8px;top:60%;right:25%;animation:v2-dot 3s ease-in-out infinite 0.5s;}
.v2-auth-right-shapes span:nth-child(3){width:16px;height:16px;bottom:30%;left:12%;animation:v2-dot 5s ease-in-out infinite 1s;}
.v2-auth-right-shapes span:nth-child(4){width:6px;height:6px;bottom:20%;right:20%;animation:v2-dot 3.5s ease-in-out infinite;}
.v2-auth-right-shapes span:nth-child(5){width:10px;height:10px;top:15%;left:20%;animation:v2-dot 4.5s ease-in-out infinite 0.3s;}
@keyframes v2-dot{0%,100%{opacity:0.5;transform:translateY(0);}50%{opacity:0.9;transform:translateY(-6px);}}
.v2-auth-google{display:block;width:100%;height: 48px;line-height: 48px;margin-bottom:1.25rem;border:1px solid #e2e8f0;color:#475569;text-align:center;text-decoration:none;background:#fff;border-radius:12px;box-sizing:border-box;}
.v2-auth-google:hover{background:#f1f5f9;}
</style>
