import { expect, test, type Page } from "@playwright/test";
import path from "node:path";

const ADMIN_URL = process.env.E2E_ADMIN_URL ?? "http://127.0.0.1:8291/admin";
const email = process.env.E2E_ADMIN_EMAIL;
const password = process.env.E2E_ADMIN_PASSWORD;

const stamp = Date.now().toString(36);
const title = `E2E Childcare Center Startup ${stamp}`;
const slug = `e2e-childcare-center-startup-${stamp}`;
const editedTitle = `E2E Licensed Childcare Center Startup ${stamp}`;
const description = "Step-by-step startup guide — licensed childcare center formation.";

async function expectPublicTitle(page: Page, expected: string) {
  // The CMS purges the frontend cache on save; poll briefly to absorb timing.
  await expect(async () => {
    await page.goto(`/products/${slug}`);
    await expect(page.getByRole("heading", { level: 1 })).toHaveText(expected, { timeout: 2_000 });
  }).toPass({ timeout: 30_000 });
}

test.skip(!email || !password, "Set E2E_ADMIN_EMAIL and E2E_ADMIN_PASSWORD to run the end-to-end flow.");

test("admin manages a product and the public website follows", async ({ page, context }) => {
  // 1. Admin logs in.
  await page.goto(`${ADMIN_URL}/login`);
  await page.locator('input[type="email"]').fill(email!);
  await page.locator('input[type="password"]').fill(password!);
  await page.getByRole("button", { name: "Sign in" }).click();
  await expect(page.getByText(/Good (morning|afternoon|evening)/)).toBeVisible();

  // 2. Creates a product with a cover upload and a downloadable file, published.
  await page.goto(`${ADMIN_URL}/products/create`, { waitUntil: "networkidle" });
  const field = (name: string) => page.locator(`[id="form.${name}"]`);
  const section = (heading: string) => page.locator(".fi-section", { has: page.getByRole("heading", { name: heading, exact: true }) });

  await expect(async () => {
    await field("title").fill(title);
    await field("title").blur();
    await expect(field("slug")).toHaveValue(slug, { timeout: 3_000 });
  }).toPass({ timeout: 20_000 });
  await field("short_description").fill(description);
  await field("price_cents").fill("297");

  // Downloadable PDF (private storage).
  await section("Pricing & delivery").locator('input[type="file"]').first().setInputFiles(path.join(__dirname, "fixtures/guide.pdf"));
  await expect(section("Pricing & delivery").locator('.filepond--item[data-filepond-item-state="processing-complete"]').first()).toBeVisible({ timeout: 60_000 });

  // Cover: upload a new image into the media library straight from the picker.
  await section("Cover").getByRole("button", { name: "Create" }).click();
  const modal = page.getByRole("dialog");
  await modal.locator('input[type="file"]').setInputFiles(path.join(__dirname, "fixtures/cover.jpg"));
  await modal.getByRole("textbox", { name: /Alternative text/ }).fill(`${title} cover`);
  await expect(modal.locator('.filepond--item[data-filepond-item-state="processing-complete"]')).toBeVisible({ timeout: 60_000 });
  await modal.getByRole("button", { name: "Create", exact: true }).click();
  await expect(modal).toBeHidden();
  await expect(section("Cover").locator("img").first()).toBeVisible();

  // Publish immediately.
  await page.getByRole("combobox", { name: /^Status/ }).click();
  await page.getByRole("option", { name: "Published" }).click();

  await page.waitForTimeout(1_500);
  await page.getByRole("button", { name: "Create", exact: true }).last().click();
  await expect(page).toHaveURL(/\/admin\/products\/\d+\/edit/, { timeout: 30_000 });
  const editUrl = page.url();

  // 3. The product appears on the public catalogue.
  await expect(async () => {
    await page.goto(`/products?search=${encodeURIComponent(stamp)}`);
    await expect(page.getByRole("link", { name: title })).toBeVisible({ timeout: 2_000 });
  }).toPass({ timeout: 30_000 });

  // 4. Detail page works, with cover, price and purchase option.
  await page.getByRole("link", { name: title }).click();
  await expect(page).toHaveURL(new RegExp(`/products/${slug}$`));
  await expect(page.getByRole("heading", { level: 1 })).toHaveText(title);
  await expect(page.getByText("$297.00").first()).toBeVisible();
  await expect(page.getByRole("img", { name: `${title} cover` })).toBeVisible();
  await expect(page.getByRole("button", { name: /add to cart/i })).toBeVisible();

  // 5. SEO metadata is generated from admin content (as served to crawlers).
  const html = await (await page.request.get(`/products/${slug}`)).text();
  expect(html.match(/<meta name="description"/g)?.length).toBe(1);
  await page.goto(`/products/${slug}`);
  await expect(page).toHaveTitle(`${title} — CSinc91`);
  await expect(page.locator('head meta[name="description"]')).toHaveAttribute("content", description);
  await expect(page.locator('head meta[property="og:title"]')).toHaveAttribute("content", title);
  await expect(page.locator('head meta[property="og:image"]').first()).toHaveAttribute("content", /\/storage\/media\/covers\//);
  await expect(page.locator('head link[rel="canonical"]')).toHaveAttribute("href", new RegExp(`/products/${slug}$`));
  const jsonLd = await page.locator('script[type="application/ld+json"]').allTextContents();
  expect(jsonLd.some((block) => block.includes('"Product"') && block.includes(title))).toBe(true);

  // 6. Admin edits the product → public site updates.
  const admin = await context.newPage();
  await admin.goto(editUrl, { waitUntil: "networkidle" });
  await admin.locator('[id="form.title"]').fill(editedTitle);
  await admin.getByRole("button", { name: "Save changes" }).click();
  await expect(admin.getByText("Saved")).toBeVisible();
  await expectPublicTitle(page, editedTitle);

  // 7. Admin unpublishes → the product disappears from the public site.
  await admin.getByRole("button", { name: "More" }).click();
  await admin.getByRole("button", { name: "Unpublish" }).click();
  await admin.getByRole("button", { name: "Confirm", exact: true }).click();
  await expect(admin.getByText("Product unpublished")).toBeVisible();

  await expect(async () => {
    const response = await page.goto(`/products/${slug}`);
    expect(response?.status()).toBe(404);
  }).toPass({ timeout: 30_000 });
  await page.goto(`/products?search=${encodeURIComponent(stamp)}`);
  await expect(page.getByText("No products found")).toBeVisible();

  // Clean up: move the test product to trash.
  await admin.getByRole("button", { name: "More" }).click();
  await admin.getByRole("button", { name: "Move to trash" }).click();
  await admin.locator(".fi-modal-window").getByRole("button", { name: /^(Delete|Confirm)$/ }).click();
  await expect(admin).toHaveURL(/\/admin\/products$/);
});
