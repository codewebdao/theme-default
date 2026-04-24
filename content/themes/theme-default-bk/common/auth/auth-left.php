<?php
/**
 * Nền auth portal (toàn màn hình) — không còn panel marketing 2 cột.
 * Bọc nội dung trong: <div class="auth-portal"> … <?php echo View::include('auth-left'); ?> … </div>
 */
?>
<style>
.auth-portal {
  position: relative;
  min-height: 100vh;
  isolation: isolate;
}
.auth-atmosphere {
  position: absolute;
  inset: 0;
  z-index: 0;
  overflow: hidden;
  background: #07080d;
}
.auth-atmosphere::before {
  content: "";
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: radial-gradient(ellipse 80% 70% at 50% 40%, black, transparent);
  pointer-events: none;
}
.auth-atmosphere__glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.55;
  pointer-events: none;
}
.auth-atmosphere__glow--1 {
  width: min(42vw, 420px);
  height: min(42vw, 420px);
  background: #14b8a6;
  top: -8%;
  left: -5%;
  animation: auth-float-a 18s ease-in-out infinite;
}
.auth-atmosphere__glow--2 {
  width: min(50vw, 520px);
  height: min(50vw, 520px);
  background: #6366f1;
  bottom: -15%;
  right: -10%;
  animation: auth-float-b 22s ease-in-out infinite;
}
.auth-atmosphere__glow--3 {
  width: min(35vw, 360px);
  height: min(35vw, 360px);
  background: #f472b6;
  top: 40%;
  left: 35%;
  opacity: 0.25;
  animation: auth-float-c 25s ease-in-out infinite;
}
@keyframes auth-float-a {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(30px, 20px) scale(1.05); }
}
@keyframes auth-float-b {
  0%, 100% { transform: translate(0, 0) scale(1); }
  50% { transform: translate(-25px, -30px) scale(1.08); }
}
@keyframes auth-float-c {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(-40px, 25px); }
}
.auth-portal__center {
  position: relative;
  z-index: 1;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.25rem 1rem 2rem;
}
.auth-portal__sheet {
  width: 100%;
  max-width: 28rem;
  padding: 2rem 1.75rem 2.25rem;
  border-radius: 1.25rem;
  background: rgba(255, 255, 255, 0.94);
  box-shadow:
    0 0 0 1px rgba(255, 255, 255, 0.5) inset,
    0 32px 64px -20px rgba(0, 0, 0, 0.55);
}
@media (min-width: 640px) {
  .auth-portal__sheet {
    padding: 2.25rem 2.25rem 2.5rem;
    border-radius: 1.35rem;
  }
}
.auth-portal__sheet--wide {
  max-width: 36rem;
}
@media (prefers-reduced-motion: reduce) {
  .auth-atmosphere__glow--1,
  .auth-atmosphere__glow--2,
  .auth-atmosphere__glow--3 {
    animation: none;
  }
}
</style>
<div class="auth-atmosphere" aria-hidden="true">
    <div class="auth-atmosphere__glow auth-atmosphere__glow--1"></div>
    <div class="auth-atmosphere__glow auth-atmosphere__glow--2"></div>
    <div class="auth-atmosphere__glow auth-atmosphere__glow--3"></div>
</div>
