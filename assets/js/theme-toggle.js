/**
 * ============================================
 * LIGHT/DARK MODE THEME TOGGLE WITH GSAP
 * ============================================
 * Features:
 * - Smooth GSAP animations
 * - localStorage persistence
 * - System preference detection
 * - Premium visual effects
 */

class ThemeToggle {
  constructor() {
    this.theme = null;
    this.toggleBtn = null;
    this.init();
  }

  /**
   * Initialize the theme toggle
   */
  init() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => this.setup());
    } else {
      this.setup();
    }
  }

  /**
   * Setup theme toggle functionality
   */
  setup() {
    // Get the theme from localStorage or system preference
    this.theme = this.getInitialTheme();
    
    // Apply theme immediately (before page renders)
    this.applyTheme(this.theme, false);
    
    // Setup toggle button
    this.setupToggleButton();
    
    // Listen for system theme changes
    this.watchSystemTheme();
  }

  /**
   * Get initial theme based on localStorage or system preference
   */
  getInitialTheme() {
    // Check localStorage first
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      return savedTheme;
    }

    // Check system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark';
    }

    return 'light';
  }

  /**
   * Apply theme to the document
   */
  applyTheme(theme, animate = true) {
    const html = document.documentElement;
    const body = document.body;

    // Store current theme
    this.theme = theme;

    if (animate && typeof gsap !== 'undefined') {
      // Add switching class for animation
      body.classList.add('theme-switching');

      // GSAP Timeline for smooth theme transition
      const tl = gsap.timeline({
        onComplete: () => {
          body.classList.remove('theme-switching');
        }
      });

      // Animate theme change with GSAP
      tl.to(body, {
        duration: 0.3,
        opacity: 0.95,
        ease: 'power2.inOut',
        onStart: () => {
          html.setAttribute('data-theme', theme);
        }
      })
      .to(body, {
        duration: 0.3,
        opacity: 1,
        ease: 'power2.inOut'
      });

      // Animate cards with stagger effect
      const cards = document.querySelectorAll('.product-card, .feature-box');
      if (cards.length > 0) {
        gsap.from(cards, {
          duration: 0.4,
          y: 10,
          opacity: 0.8,
          stagger: 0.05,
          ease: 'power2.out',
          delay: 0.2
        });
      }

      // Animate hero section
      const heroSection = document.querySelector('.hero-section');
      if (heroSection) {
        gsap.from(heroSection, {
          duration: 0.5,
          scale: 0.98,
          opacity: 0.9,
          ease: 'power2.out',
          delay: 0.1
        });
      }

      // Animate navbar
      const navbar = document.querySelector('.navbar');
      if (navbar) {
        gsap.from(navbar, {
          duration: 0.4,
          y: -10,
          opacity: 0.9,
          ease: 'power2.out'
        });
      }

    } else {
      // No animation, just apply theme
      html.setAttribute('data-theme', theme);
    }

    // Save to localStorage
    localStorage.setItem('theme', theme);

    // Update toggle button state
    this.updateToggleButton();
  }

  /**
   * Setup the toggle button event listener
   */
  setupToggleButton() {
    this.toggleBtn = document.getElementById('themeToggle');
    
    if (!this.toggleBtn) {
      console.warn('Theme toggle button not found');
      return;
    }

    this.toggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      this.toggleTheme();
    });

    // Initial button state
    this.updateToggleButton();
  }

  /**
   * Toggle between light and dark theme
   */
  toggleTheme() {
    const newTheme = this.theme === 'light' ? 'dark' : 'light';
    
    // Animate toggle button with GSAP
    if (typeof gsap !== 'undefined') {
      const slider = this.toggleBtn.querySelector('.theme-toggle-slider');
      const sunIcon = this.toggleBtn.querySelector('.theme-icon.sun');
      const moonIcon = this.toggleBtn.querySelector('.theme-icon.moon');

      // Button press animation
      gsap.to(this.toggleBtn, {
        duration: 0.1,
        scale: 0.95,
        ease: 'power2.inOut',
        yoyo: true,
        repeat: 1
      });

      // Slider animation
      gsap.to(slider, {
        duration: 0.4,
        rotation: 360,
        ease: 'back.out(1.7)'
      });

      // Icon transitions
      if (newTheme === 'dark') {
        gsap.to(sunIcon, {
          duration: 0.3,
          rotation: -180,
          scale: 0,
          opacity: 0,
          ease: 'power2.in'
        });
        gsap.to(moonIcon, {
          duration: 0.3,
          rotation: 0,
          scale: 1,
          opacity: 1,
          ease: 'back.out(1.7)',
          delay: 0.1
        });
      } else {
        gsap.to(moonIcon, {
          duration: 0.3,
          rotation: 180,
          scale: 0,
          opacity: 0,
          ease: 'power2.in'
        });
        gsap.to(sunIcon, {
          duration: 0.3,
          rotation: 0,
          scale: 1,
          opacity: 1,
          ease: 'back.out(1.7)',
          delay: 0.1
        });
      }
    }

    // Apply the new theme
    this.applyTheme(newTheme, true);

    // Trigger custom event
    this.dispatchThemeChangeEvent(newTheme);
  }

  /**
   * Update toggle button visual state
   */
  updateToggleButton() {
    if (!this.toggleBtn) return;

    const slider = this.toggleBtn.querySelector('.theme-toggle-slider');
    const sunIcon = this.toggleBtn.querySelector('.theme-icon.sun');
    const moonIcon = this.toggleBtn.querySelector('.theme-icon.moon');

    if (this.theme === 'dark') {
      this.toggleBtn.setAttribute('aria-label', 'Switch to light mode');
      this.toggleBtn.setAttribute('title', 'Switch to light mode');
    } else {
      this.toggleBtn.setAttribute('aria-label', 'Switch to dark mode');
      this.toggleBtn.setAttribute('title', 'Switch to dark mode');
    }
  }

  /**
   * Watch for system theme changes
   */
  watchSystemTheme() {
    if (!window.matchMedia) return;

    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Listen for changes
    mediaQuery.addEventListener('change', (e) => {
      // Only auto-switch if user hasn't manually set a preference
      const savedTheme = localStorage.getItem('theme');
      if (!savedTheme) {
        const newTheme = e.matches ? 'dark' : 'light';
        this.applyTheme(newTheme, true);
      }
    });
  }

  /**
   * Dispatch custom theme change event
   */
  dispatchThemeChangeEvent(theme) {
    const event = new CustomEvent('themechange', {
      detail: { theme }
    });
    window.dispatchEvent(event);
  }

  /**
   * Get current theme
   */
  getCurrentTheme() {
    return this.theme;
  }

  /**
   * Set theme programmatically
   */
  setTheme(theme) {
    if (theme === 'light' || theme === 'dark') {
      this.applyTheme(theme, true);
    }
  }
}

// Initialize theme toggle
const themeToggle = new ThemeToggle();

// Export for use in other scripts
if (typeof window !== 'undefined') {
  window.themeToggle = themeToggle;
}

// Listen for theme changes (for debugging or custom handlers)
window.addEventListener('themechange', (e) => {
  console.log(`Theme changed to: ${e.detail.theme}`);
});

/**
 * ============================================
 * ADDITIONAL GSAP ENHANCEMENTS
 * ============================================
 */

// Add entrance animations when page loads
document.addEventListener('DOMContentLoaded', () => {
  if (typeof gsap !== 'undefined') {
    
    // Animate theme toggle button entrance
    const toggleBtn = document.getElementById('themeToggle');
    if (toggleBtn) {
      gsap.from(toggleBtn, {
        duration: 0.6,
        scale: 0,
        rotation: -180,
        opacity: 0,
        ease: 'back.out(1.7)',
        delay: 0.5
      });
    }

    // Add hover animations to theme toggle
    if (toggleBtn) {
      toggleBtn.addEventListener('mouseenter', () => {
        gsap.to(toggleBtn, {
          duration: 0.3,
          scale: 1.1,
          ease: 'power2.out'
        });
      });

      toggleBtn.addEventListener('mouseleave', () => {
        gsap.to(toggleBtn, {
          duration: 0.3,
          scale: 1,
          ease: 'power2.out'
        });
      });
    }
  }
});

/**
 * ============================================
 * UTILITY FUNCTIONS
 * ============================================
 */

// Function to get current theme (can be called from anywhere)
function getCurrentTheme() {
  return window.themeToggle ? window.themeToggle.getCurrentTheme() : 'light';
}

// Function to set theme (can be called from anywhere)
function setTheme(theme) {
  if (window.themeToggle) {
    window.themeToggle.setTheme(theme);
  }
}

// Export utility functions
if (typeof window !== 'undefined') {
  window.getCurrentTheme = getCurrentTheme;
  window.setTheme = setTheme;
}
