const { test, expect } = require('@playwright/test');

test.describe('Plugin Activation', () => {
	test('Plugin should be active', async ({ page }) => {
		await page.goto('/wp-admin/plugins.php');

		// Check if the plugin is in the list and active
		const pluginRow = page.locator('tr[data-slug="wp-pfadi-manager"]');
		await expect(pluginRow).toBeVisible();

		// If it has a "Deactivate" link, it is active
		const deactivateLink = pluginRow.locator('a.deactivate');
		await expect(deactivateLink).toBeVisible();
	});
});
