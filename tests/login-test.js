// tests/login-test.js
// Playwright standalone script that runs against Chromium.
// Usage: set TEST_USER_EMAIL and TEST_USER_PASSWORD in the environment (PowerShell examples in the instructions).

const { chromium } = require('playwright');

(async () => {
  const email = process.env.TEST_USER_EMAIL || '22i371@psgtech.ac.in'; // change default if you want
  const password = process.env.TEST_USER_PASSWORD || 'Swt@123';

  if (!email || !password) {
    console.error('Please set TEST_USER_EMAIL and TEST_USER_PASSWORD environment variables.');
    process.exit(1);
  }

  const baseUrl = process.env.BASE_URL || 'http://localhost/foodie';
  const loginUrl = `${baseUrl}/login.php`;

  const browser = await chromium.launch({ headless: false }); // set to true to run headless
  const context = await browser.newContext({
    viewport: { width: 1280, height: 800 }
  });
  const page = await context.newPage();

  try {
    console.log('Going to login page:', loginUrl);
    await page.goto(loginUrl, { waitUntil: 'networkidle' });

    // Fill login form - uses input name attributes from your login.php
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="password"]', password);

    // Click the login button (it's a form button in your page)
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle', timeout: 5000 }).catch(() => null),
      page.click('button[type="submit"]')
    ]);

    // Validation: check for a logout button or redirect to index.php
    // Adjust selectors if your UI differs.
    const loggedIn = await page.$('a.btn.btn-danger[href="logout.php"], a.btn.btn-danger[href="logout_admin.php"]');

    if (loggedIn) {
      console.log('Login seems successful: logout button present.');
      const path = `tests/login-success-${Date.now()}.png`;
      await page.screenshot({ path, fullPage: true });
      console.error('Saved screenshot to', path);
      process.exitCode = 1;
    } else {
      // fallback check: URL changed to index.php
      const url = page.url();
      if (url.includes('index.php')) {
        console.log('Login seems successful: redirected to index.php');
      } else {
        console.error('Login did not appear successful. Current URL:', url);
        // Capture a screenshot for debugging
        const path = `tests/login-failure-${Date.now()}.png`;
        await page.screenshot({ path, fullPage: true });
        console.error('Saved screenshot to', path);
        process.exitCode = 2;
      }
    }
  } catch (err) {
    console.error('Test error:', err);
    const path = `tests/login-error-${Date.now()}.png`;
    try { await page.screenshot({ path, fullPage: true }); console.error('Saved screenshot to', path); } catch(e){}
    process.exitCode = 3;
  } finally {
    await browser.close();
  }
})();