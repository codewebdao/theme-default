<?php
/**
 * Glass card + form styles: login, register, forgot, reset, confirm, validation.
 * Profile có thể require file này để dùng chung --auth-* (đặc biệt --auth-primary).
 * Include once per response (guarded).
 */
if (defined('CMSFULLFORM_AUTH_LOGIN_CARD_STYLES')) {
    return;
}
define('CMSFULLFORM_AUTH_LOGIN_CARD_STYLES', true);
?>
<style>
/**
 * Theme auth: modern dark glass / indigo blue premium
 * Gọn, sạch, hiện đại, hợp login / register / forgot password / dashboard auth.
 */
.auth-portal {
  --auth-primary: #275cee;
  --auth-primary-rgb: 39, 92, 238;
  --auth-primary-strong: #1d4ed8;
  --auth-primary-soft: rgba(var(--auth-primary-rgb), 0.18);
  --auth-primary-glow: rgba(var(--auth-primary-rgb), 0.40);
  --auth-primary-faint: rgba(var(--auth-primary-rgb), 0.10);

  --auth-accent: var(--auth-primary);
  --auth-link: #93c5fd;
  --auth-link-hover: #dbeafe;

  --auth-card-bg: rgba(8, 12, 24, 0.88);
  --auth-card-border: rgba(var(--auth-primary-rgb), 0.22);
  --auth-card-inset: rgba(255, 255, 255, 0.02);
  --auth-card-shadow:
    0 0 0 1px var(--auth-card-inset) inset,
    0 24px 60px -18px rgba(0, 0, 0, 0.72),
    0 0 90px -34px rgba(var(--auth-primary-rgb), 0.24);

  --auth-text-title: #f8fafc;
  --auth-text-label: #e2e8f0;
  --auth-text-sub: #94a3b8;
  --auth-text-muted: #64748b;

  --auth-input-bg: rgba(255, 255, 255, 0.04);
  --auth-input-border: rgba(255, 255, 255, 0.08);
  --auth-input-text: #f8fafc;
  --auth-icon: #64748b;

  --auth-focus-border: rgba(var(--auth-primary-rgb), 0.72);
  --auth-focus-ring: rgba(var(--auth-primary-rgb), 0.18);

  --auth-btn-text: #f8fafc;
  --auth-btn-gradient: linear-gradient(
    135deg,
    #1e40af 0%,
    var(--auth-primary-strong) 30%,
    var(--auth-primary) 68%,
    #60a5fa 100%
  );
  --auth-btn-shadow: 0 12px 30px -10px rgba(var(--auth-primary-rgb), 0.48);
  --auth-btn-shadow-hover: 0 16px 38px -10px rgba(96, 165, 250, 0.36);

  --auth-divider-line: rgba(255, 255, 255, 0.08);
  --auth-divider-pill-bg: rgba(8, 12, 24, 0.96);

  --auth-google-bg: rgba(255, 255, 255, 0.035);
  --auth-google-border: rgba(255, 255, 255, 0.09);

  --auth-lang-border: rgba(var(--auth-primary-rgb), 0.10);
  --auth-success-icon: #86efac;
}

.auth-login-card {
  width: 100%;
  max-width: 26rem;
  margin: 0 auto;
  padding: 2rem 1.5rem 2.25rem;
  border-radius: 1.5rem;
  background:
    linear-gradient(180deg, rgba(255,255,255,0.025), rgba(255,255,255,0.01)),
    var(--auth-card-bg);
  backdrop-filter: blur(28px);
  -webkit-backdrop-filter: blur(28px);
  border: 1px solid var(--auth-card-border);
  box-shadow: var(--auth-card-shadow);
}

.auth-login-card--wide { max-width: 28rem; }
.auth-login-card--register { max-width: 28rem; }

@media (min-width: 480px) {
  .auth-login-card {
    padding: 2.25rem 2rem 2.5rem;
    border-radius: 1.6rem;
  }
}

.auth-login-logo {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.auth-login-logo img {
  max-height: 2.75rem;
  width: auto;
  max-width: 100%;
  object-fit: contain;
}

.auth-login-title {
  margin: 0 0 0.35rem;
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.03em;
  color: var(--auth-text-title);
  text-align: center;
  line-height: 1.2;
}

.auth-login-card .auth-login-title::after {
  content: "";
  display: block;
  width: 3rem;
  height: 3px;
  margin: 0.7rem auto 0;
  border-radius: 999px;
  background: linear-gradient(
    90deg,
    var(--auth-primary-strong),
    var(--auth-primary),
    var(--auth-link-hover)
  );
  box-shadow: 0 0 16px var(--auth-primary-glow);
}

.auth-login-sub {
  margin: 0 0 1.5rem;
  font-size: 0.875rem;
  color: var(--auth-text-sub);
  text-align: center;
  line-height: 1.5;
}

.auth-login-sub a {
  color: var(--auth-link);
  font-weight: 500;
  text-decoration: none;
}

.auth-login-sub a:hover {
  color: var(--auth-link-hover);
  text-decoration: underline;
}

.auth-stack {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.auth-field-label {
  display: block;
  margin-bottom: 0.4rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--auth-text-label);
}

.auth-input-wrap {
  position: relative;
}

.auth-input-wrap > svg,
.auth-input-wrap .auth-input-icon {
  position: absolute;
  left: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1rem;
  height: 1rem;
  color: var(--auth-icon);
  pointer-events: none;
  z-index: 1;
}

.auth-input {
  display: block;
  width: 100%;
  box-sizing: border-box;
  padding: 0.78rem 0.9rem 0.78rem 2.65rem;
  font-size: 0.875rem;
  color: var(--auth-input-text);
  background: var(--auth-input-bg);
  border: 1px solid var(--auth-input-border);
  border-radius: 0.85rem;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.auth-input::placeholder {
  color: var(--auth-text-muted);
}

.auth-input:hover {
  border-color: rgba(var(--auth-primary-rgb), 0.22);
}

.auth-input:focus {
  outline: none;
  border-color: var(--auth-focus-border);
  box-shadow: 0 0 0 4px var(--auth-focus-ring);
  background: rgba(255, 255, 255, 0.05);
}

.auth-input--invalid {
  border-color: rgba(248, 113, 113, 0.7) !important;
  box-shadow: 0 0 0 4px rgba(248, 113, 113, 0.14) !important;
}

.auth-input--otp {
  padding-left: 0.9rem;
  text-align: center;
  font-size: 1.35rem;
  font-weight: 700;
  letter-spacing: 0.35em;
}

.auth-input--phone {
  padding-left: 0.75rem;
}

.auth-row-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.auth-check {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8125rem;
  color: var(--auth-text-label);
  cursor: pointer;
}

.auth-check input {
  width: 1rem;
  height: 1rem;
  accent-color: var(--auth-primary);
  border-radius: 0.25rem;
}

.auth-check--start {
  align-items: flex-start;
  line-height: 1.45;
}

.auth-check--start input {
  margin-top: 0.15rem;
  flex-shrink: 0;
}

.auth-link-muted {
  font-size: 0.8125rem;
  font-weight: 500;
  color: var(--auth-link);
  text-decoration: none;
}

.auth-link-muted:hover {
  color: var(--auth-link-hover);
  text-decoration: underline;
}

.auth-text-muted {
  font-size: 0.75rem;
  color: var(--auth-text-muted);
  text-align: center;
  margin: 0.35rem 0 0;
  line-height: 1.45;
}

.auth-text-muted--left {
  text-align: left;
}

.auth-btn-google {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  padding: 0.78rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--auth-text-label);
  background: var(--auth-google-bg);
  border: 1px solid var(--auth-google-border);
  border-radius: 0.85rem;
  cursor: pointer;
  transition: background 0.15s, border-color 0.15s, transform 0.15s;
}

.auth-btn-google:hover {
  background: rgba(255, 255, 255, 0.06);
  border-color: rgba(var(--auth-primary-rgb), 0.3);
  transform: translateY(-1px);
}

.auth-divider {
  position: relative;
  text-align: center;
  margin: 0.35rem 0;
}

.auth-divider::before {
  content: "";
  position: absolute;
  left: 0;
  right: 0;
  top: 50%;
  height: 1px;
  background: var(--auth-divider-line);
}

.auth-divider span {
  position: relative;
  padding: 0 0.65rem;
  font-size: 0.75rem;
  text-transform: capitalize;
  color: var(--auth-text-muted);
  background: var(--auth-divider-pill-bg);
}

.auth-btn-submit {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
  padding: 0.82rem 1rem;
  font-size: 0.9375rem;
  font-weight: 700;
  color: var(--auth-btn-text);
  border: none;
  border-radius: 0.9rem;
  cursor: pointer;
  background: var(--auth-btn-gradient);
  box-shadow: var(--auth-btn-shadow);
  transition: transform 0.15s, filter 0.15s, box-shadow 0.15s;
}

.auth-btn-submit:hover {
  filter: brightness(1.05) saturate(1.05);
  transform: translateY(-1px);
  box-shadow: var(--auth-btn-shadow-hover);
}

.auth-btn-submit:active {
  transform: translateY(0);
}

.auth-lang {
  margin-top: 1.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--auth-lang-border);
  display: flex;
  justify-content: center;
  opacity: 0.95;
}

.auth-alert {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.8rem 0.9rem;
  border-radius: 0.85rem;
  font-size: 0.8125rem;
  line-height: 1.45;
}

.auth-alert:has(ul) {
  align-items: flex-start;
}

.auth-alert:has(ul) > :first-child {
  margin-top: 0.15em;
}

.auth-alert > :first-child:is(svg, i) {
  flex-shrink: 0;
}

.auth-alert svg.lucide {
  display: block;
}

.auth-alert--error {
  background: rgba(127, 29, 29, 0.35);
  border: 1px solid rgba(248, 113, 113, 0.28);
  color: #fecaca;
}

.auth-alert--error strong {
  color: #fef2f2;
}

.auth-alert--ok {
  background: rgba(6, 78, 59, 0.38);
  border: 1px solid rgba(52, 211, 153, 0.28);
  color: #a7f3d0;
}

.auth-alert--warn {
  background: rgba(120, 53, 15, 0.35);
  border: 1px solid rgba(251, 191, 36, 0.3);
  color: #fde68a;
}

.auth-alert ul {
  margin: 0.35rem 0 0 1rem;
  padding: 0;
}

.auth-field-error {
  margin-top: 0.35rem;
  font-size: 0.8125rem;
  color: #fca5a5;
}

.auth-pw-hint {
  padding: 0.8rem 0.9rem;
  border-radius: 0.85rem;
  font-size: 0.75rem;
  line-height: 1.45;
  background: var(--auth-primary-faint);
  border: 1px solid rgba(var(--auth-primary-rgb), 0.18);
  color: var(--auth-text-sub);
}

.auth-pw-hint strong {
  color: var(--auth-text-label);
  display: block;
  margin-bottom: 0.35rem;
}

.auth-pw-hint ul {
  margin: 0;
  padding: 0;
  list-style: none;
}

.auth-pw-hint li {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  margin-top: 0.25rem;
}

.auth-pw-hint .auth-pw-req-icon {
  color: var(--auth-text-muted);
  flex-shrink: 0;
}

.auth-pw-hint .auth-pw-req-icon.is-ok {
  color: var(--auth-success-icon);
}

.auth-code-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
}

.auth-code-digit {
  width: 2.75rem;
  height: 3.25rem;
  border: 2px solid var(--auth-input-border);
  border-radius: 0.85rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--auth-input-text);
  background: var(--auth-input-bg);
  cursor: pointer;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}

.auth-code-digit.is-filled {
  border-color: var(--auth-focus-border);
  background: var(--auth-primary-soft);
}

.auth-code-digit.is-active {
  box-shadow: 0 0 0 4px var(--auth-focus-ring);
}

.auth-icon-ring {
  margin: 0 auto 1.25rem;
  width: 4rem;
  height: 4rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--auth-primary-faint);
  border: 1px solid rgba(var(--auth-primary-rgb), 0.28);
  color: var(--auth-link);
  box-shadow: 0 0 28px -8px var(--auth-primary-glow);
}

.auth-hidden {
  display: none !important;
}

/* intl-tel-input */
.auth-login-card .iti {
  width: 100% !important;
}

.auth-login-card .iti__selected-flag {
  background: var(--auth-input-bg);
  border-right: 1px solid var(--auth-input-border);
}

.auth-login-card .iti__selected-dial-code {
  color: var(--auth-text-label);
}

.auth-login-card .iti__arrow {
  border-top-color: var(--auth-text-muted);
}

.auth-login-card .iti__country-list {
  background: rgba(12, 16, 30, 0.98);
  border: 1px solid rgba(var(--auth-primary-rgb), 0.18);
  color: var(--auth-text-label);
  box-shadow: 0 20px 40px -20px rgba(0,0,0,0.7);
}

.auth-login-card .iti__country.iti__highlight {
  background: var(--auth-primary-soft);
}

.auth-login-card .iti__search-input {
  background: var(--auth-input-bg);
  border-color: var(--auth-input-border);
  color: var(--auth-input-text);
}
</style>