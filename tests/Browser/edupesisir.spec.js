import { test, expect } from '@playwright/test';

async function selectSubject(page, label) {
    // Subjects repeat across levels; select the enabled option in the current level.
    const value = await page.locator('[name=id_mapel] option').evaluateAll((options, name) =>
        options.find(option => option.label === name && !option.disabled && !option.hidden)?.value, label);
    expect(value).toBeTruthy();
    await page.selectOption('[name=id_mapel]', value);
}

test('public navigation, illustrations and responsive desktop home', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.setViewportSize({ width: 1440, height: 1000 });
    await page.goto('/');
    await expect(page.locator('h1')).toContainText(/belajar/i);
    await expect(page.locator('.navbar')).toBeVisible();
    const brokenImages = await page.locator('img').evaluateAll(images => images.filter(img => img.complete && img.naturalWidth === 0).map(img => img.src));
    expect(brokenImages).toEqual([]);
    await page.screenshot({ path: 'artifacts/home-desktop.png', fullPage: true });
    await page.goto('/tempat-belajar');
    await expect(page.locator('main')).toContainText('Pantai Panjang');
    await page.goto('/tempat-belajar/1');
    await expect(page.locator('.leaflet-container')).toBeVisible();
    await expect(page.locator('.leaflet-marker-icon').first()).toBeVisible();
    expect(errors).toEqual([]);
});

test('school selection calculates ranking, snapshot matrices, and map', async ({ page }) => {
    await page.goto('/rekomendasi');
    await page.selectOption('[name=id_jenjang]', { label: 'SD' });
    await selectSubject(page, 'IPA');
    const school = await page.locator('[name=id_sekolah] option:not([disabled])').evaluateAll(options => options.find(option => option.value && !option.hidden)?.value);
    await page.selectOption('[name=id_sekolah]', school);
    await page.locator('form[action$="/rekomendasi"] button[type=submit]').click();
    await expect(page).toHaveURL(/\/rekomendasi\/hasil\/[\w-]+/);
    await expect(page.locator('main')).toContainText(/Rekomendasi utama/i);
    await expect(page.locator('.leaflet-marker-icon').first()).toBeVisible();
    await expect(page.locator('main')).toContainText('Matriks');
    const alternatives = await page.locator('.ranking-card').count();
    await page.getByRole('button', { name: /Matriks keputusan/ }).click();
    await expect(page.locator('#calc-matrix')).toBeVisible();
    await expect(page.locator('#calc-matrix tbody tr')).toHaveCount(alternatives);
    await page.getByRole('button', { name: /Matriks normalisasi/ }).click();
    await expect(page.locator('#calc-normalized')).toBeVisible();
    await expect(page.locator('#calc-normalized tbody tr')).toHaveCount(alternatives);
    await page.getByRole('button', { name: /Kontribusi kriteria/ }).click();
    await expect(page.locator('#calc-preferences')).toBeVisible();
    await page.screenshot({ path: 'artifacts/recommendation-desktop.png', fullPage: true });
});

test('mobile layout, navigation and denied location preserve school workflow', async ({ page, context }) => {
    await context.clearPermissions();
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/');
    await page.locator('.navbar-toggler').click();
    await expect(page.locator('#public-nav')).toBeVisible();
    await page.screenshot({ path: 'artifacts/home-mobile.png', fullPage: true });
    await page.goto('/rekomendasi');
    await page.selectOption('[name=id_jenjang]', { label: 'SMP' });
    await selectSubject(page, 'IPA');
    const school = await page.locator('[name=id_sekolah] option:not([disabled])').evaluateAll(options => options.find(option => option.value && !option.hidden)?.value);
    await page.selectOption('[name=id_sekolah]', school);
    await page.getByRole('button', { name: /Gunakan Lokasi Saya/i }).click();
    await expect(page.locator('[data-geo-status]')).toContainText(/ditolak|belum dapat diperoleh/);
    await expect(page.locator('[name=use_location]')).toHaveValue('0');
    await page.locator('form[action$="/rekomendasi"] button[type=submit]').click();
    await expect(page).toHaveURL(/\/rekomendasi\/hasil\//);
    const dimensions = await page.evaluate(() => ({ viewport: innerWidth, content: document.documentElement.scrollWidth }));
    expect(dimensions.content).toBeLessThanOrEqual(dimensions.viewport + 1);
    await page.screenshot({ path: 'artifacts/recommendation-mobile.png', fullPage: true });
});

test('admin login, level CRUD and logout work in a real browser', async ({ page }) => {
    await page.goto('/admin/login');
    await page.fill('[name=email]', 'admin@edupesisir.test');
    await page.fill('[name=password]', 'password123');
    await page.locator('button[type=submit]').click();
    await expect(page).toHaveURL(/\/admin\/dashboard/);
    await page.screenshot({ path: 'artifacts/admin-desktop.png', fullPage: true });
    const name = `Uji Browser ${Date.now()}`;
    await page.goto('/admin/jenjang/create');
    await page.fill('[name=nama_jenjang]', name);
    await page.locator('form button[type=submit]').last().click();
    await expect(page).toHaveURL(/\/admin\/jenjang$/);
    await page.goto('/admin/jenjang?q=' + encodeURIComponent(name));
    const row = page.locator('tr').filter({ hasText: name });
    await expect(row).toBeVisible();
    await row.locator('a[href$="/edit"]').click();
    await page.fill('[name=nama_jenjang]', name + ' Edit');
    await page.locator('form button[type=submit]').last().click();
    await page.goto('/admin/jenjang?q=' + encodeURIComponent(name));
    page.once('dialog', dialog => dialog.accept());
    await page.locator('tr').filter({ hasText: name }).locator('form button[type=submit]').click();
    await expect(page.locator('main')).not.toContainText(name + ' Edit');
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/admin/dashboard');
    await page.locator('#admin-menu-toggle').click();
    await expect(page.locator('body')).toHaveClass(/sidebar-open/);
    await page.keyboard.press('Escape');
    await expect(page.locator('body')).not.toHaveClass(/sidebar-open/);
    await page.screenshot({ path: 'artifacts/admin-mobile.png', fullPage: true });
    expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(391);
    await page.locator('form[action$="/admin/logout"]').evaluate(form => form.requestSubmit());
    await expect(page).toHaveURL(/\/admin\/login/);
    await page.goto('/admin/dashboard');
    await expect(page).toHaveURL(/\/admin\/login/);
});

test('CSRF middleware rejects untrusted POST', async ({ request }) => {
    const response = await request.post('/rekomendasi', { form: { id_jenjang: '1', id_mapel: '1', id_sekolah: '1' } });
    expect(response.status()).toBe(419);
});

test('catalog and recommendation form fit tablet and small phone screens', async ({ page }) => {
    for (const width of [768, 320]) {
        await page.setViewportSize({ width, height: 900 });
        for (const path of ['/tempat-belajar', '/rekomendasi']) {
            await page.goto(path);
            await expect(page.locator('h1')).toBeVisible();
            expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(width + 1);
        }
    }
});
