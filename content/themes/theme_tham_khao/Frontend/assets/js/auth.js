document.addEventListener('DOMContentLoaded', () => {
  // Utility functions
  const toggleClasses = (element, classes, add) => {
    classes.forEach(cls => element.classList[add ? 'add' : 'remove'](cls));
  };

  const dispatchEvent = (name, detail) => {
    document.dispatchEvent(new CustomEvent(name, { detail }));
  };

  // Dropdown language selector
  const dropdownConfig = {
    dropdown: document.getElementById('languageDropdown'),
    button: document.getElementById('dropdownButton'),
    menu: document.getElementById('language-menu'),
    chevron: document.getElementById('chevronIcon'),
    selectedDisplay: document.getElementById('selectedLanguageDisplay'),
    options: document.querySelectorAll('.language-option'),
  };

  if (Object.values(dropdownConfig).every(el => el)) {
    const { dropdown, button, menu, chevron, selectedDisplay, options } = dropdownConfig;
    let isOpen = false;
    let currentLanguage = { code: 'vi', name: 'Vietnamese', flag: '🇻🇳' };

    const toggleDropdown = () => {
      isOpen = !isOpen;
      toggleClasses(menu, ['dropdown-open'], isOpen);
      toggleClasses(chevron, ['chevron-rotate'], isOpen);
      button.setAttribute('aria-expanded', isOpen);
      button.setAttribute('data-state', isOpen ? 'open' : 'closed');
    };

    const selectLanguage = (languageData) => {
      currentLanguage = languageData;
      selectedDisplay.innerHTML = `
        <div class="flex items-center space-x-2">
          <span class="text-base">${languageData.flag}</span>
          <span>${languageData.name}</span>
        </div>
      `;

      options.forEach(option => {
        const isSelected = option.dataset.code === languageData.code;
        const baseClasses = 'language-option flex items-center justify-between px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors';
        option.className = `${baseClasses} ${isSelected ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-gray-100'}`;
        option.dataset.selected = isSelected;

        const checkmark = option.querySelector('svg');
        if (isSelected && !checkmark) {
          const svg = document.createElement('svg');
          svg.className = 'h-4 w-4 text-blue-600';
          svg.setAttribute('fill', 'currentColor');
          svg.setAttribute('viewBox', '0 0 20 20');
          svg.innerHTML = '<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>';
          option.appendChild(svg);
        } else if (!isSelected && checkmark) {
          checkmark.remove();
        }
      });

      toggleDropdown();
      dispatchEvent('languageChanged', languageData);
      console.log('Language changed to:', languageData.code);
    };

    button.addEventListener('click', (e) => {
      e.stopPropagation();
      toggleDropdown();
    });

    options.forEach(option => {
      const handleSelection = () => {
        selectLanguage({
          code: option.dataset.code,
          name: option.dataset.name,
          flag: option.dataset.flag,
        });
      };

      option.addEventListener('click', (e) => {
        e.stopPropagation();
        handleSelection();
      });

      option.addEventListener('keydown', (e) => {
        if (['Enter', ' '].includes(e.key)) {
          e.preventDefault();
          handleSelection();
        }
      });
    });

    document.addEventListener('click', (e) => {
      if (!dropdown.contains(e.target)) {
        isOpen && toggleDropdown();
      }
    });

    document.addEventListener('languageChanged', (e) => {
      console.log('Language selected:', e.detail);
      // Example: window.location.href = `/change-language/${e.detail.code}`;
    });
  }

  // Authentication utilities
  const setCookie = (name, value, days) => {
    document.cookie = `${name}=${value}; path=/; max-age=${days * 86400};`;
  };

  const hasToken = () => document.cookie.includes('cmsff_token');

  const handleFetch = async (url, formData) => {
    try {
      const response = await fetch(url, { method: 'POST', body: formData });
      const data = await response.json();
      if (data.status === 'success' && data.data?.access_token) {
        setCookie('cmsff_token', data.data.access_token, 7);
        return { success: true, data };
      }
      throw new Error(data.message || 'Failed!');
    } catch (error) {
      alert(error.message);
      return { success: false };
    }
  };

  // Login functionality
  const loginConfig = {
    button: document.getElementById('login-button'),
    username: document.getElementById('username'),
    password: document.getElementById('password'),
  };

  if (loginConfig.button && hasToken()) {
    window.location.href = '/';
  } else if (Object.values(loginConfig).every(el => el)) {
    const { button, username, password } = loginConfig;

    const handleLogin = async () => {
      const formData = new FormData();
      formData.append('username', username.value);
      formData.append('password', password.value);

      const result = await handleFetch('/vi/api/v1/auth/login/', formData);
      if (result.success) {
        console.log('Login successful:', result.data);
        window.location.href = '/admin/';
      }
    };

    button.addEventListener('click', handleLogin);
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        handleLogin();
      }
    });
  }

  // Registration functionality
  const registerConfig = {
    button: document.getElementById('register'),
    username: document.getElementById('username'),
    email: document.getElementById('email'),
    password: document.getElementById('password'),
    confirmPassword: document.getElementById('confirmPassword'),
    fullname: document.getElementById('fullname'),
  };

  if (registerConfig.button && hasToken()) {
    window.location.href = '/';
  } else if (registerConfig.button) {
    const { button, username, email, password, confirmPassword, fullname } = registerConfig;

    button.addEventListener('click', async () => {
      const formData = new FormData();
      formData.append('username', username.value);
      formData.append('email', email.value);
      formData.append('password', password.value);
      formData.append('password_repeat', confirmPassword.value);
      formData.append('fullname', fullname ? fullname.value : '');

      const result = await handleFetch('/api/v1/auth/register', formData);
      if (result.success) {
        console.log('Registration successful:', result.data);
        // localStorage.setItem('user', JSON.stringify(result.data.data.me));
        // window.location.href = '/';
      }
    });
  }
});