(() => {
  const groups = [
    'ABCDEFGHJKLMNPQRSTUVWXYZ',
    'abcdefghijkmnopqrstuvwxyz',
    '23456789',
    '!@#$%&*+-=?_'
  ];
  const alphabet = groups.join('');

  function secureIndex(max) {
    if (!window.crypto || !window.crypto.getRandomValues) {
      throw new Error('Secure random generation is unavailable.');
    }
    const limit = Math.floor(0x100000000 / max) * max;
    const value = new Uint32Array(1);
    do {
      window.crypto.getRandomValues(value);
    } while (value[0] >= limit);
    return value[0] % max;
  }

  function generatePassword(length = 24) {
    const chars = groups.map(group => group[secureIndex(group.length)]);
    while (chars.length < length) {
      chars.push(alphabet[secureIndex(alphabet.length)]);
    }
    for (let i = chars.length - 1; i > 0; i -= 1) {
      const j = secureIndex(i + 1);
      [chars[i], chars[j]] = [chars[j], chars[i]];
    }
    return chars.join('');
  }

  function announce(message) {
    const status = document.getElementById('passwordToolStatus');
    if (status) status.textContent = message;
  }

  document.addEventListener('click', event => {
    const generateButton = event.target.closest('[data-generate-password]');
    if (generateButton) {
      const input = document.getElementById(generateButton.dataset.generatePassword);
      if (!input) return;
      try {
        input.value = generatePassword();
        input.dispatchEvent(new Event('input', { bubbles: true }));
        announce('A strong password was generated. Reveal it before copying or sharing it.');
      } catch (error) {
        announce(error.message || 'Password generation failed.');
      }
      return;
    }

    const toggleButton = event.target.closest('[data-toggle-password]');
    if (toggleButton) {
      const input = document.getElementById(toggleButton.dataset.togglePassword);
      if (!input) return;
      const visible = input.type === 'password';
      input.type = visible ? 'text' : 'password';
      toggleButton.setAttribute('aria-pressed', visible ? 'true' : 'false');
      toggleButton.textContent = visible ? 'Hide' : 'Show';
      announce(visible ? 'Password is visible.' : 'Password is hidden.');
    }
  });
})();
