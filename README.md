# Flodesk Integration for Elementor Forms

**Version:** 1.0.2

**Tested up to:** WordPress 7.0.2

**Requires PHP:** 7.4

**Author:** [Potomac Technologies, LLC](https://potomactech.net)

## Overview

Flodesk Integration for Elementor Forms is a lightweight WordPress plugin that connects Elementor Pro forms to the **Flodesk REST API**. It adds a **Flodesk** option to the Elementor form widget's "Actions After Submit", which creates or updates a Flodesk subscriber each time the form is submitted.

Each form can assign subscribers to Flodesk segments, either for every submission or conditionally based on the visitor's answers, and can pass additional form fields to Flodesk custom fields.

---

## Key features

* **Native form action:** Appears in the Elementor Pro form editor alongside the built-in actions.
* **Subscriber upsert:** Creates a new subscriber, or updates the existing subscriber when the email address is already in Flodesk.
* **Segment dropdowns:** Loads the account's Flodesk segments directly into the Elementor editor, so segment IDs never need to be looked up manually.
* **Static segment assignment:** Assigns one or more segments to every subscriber submitted through a given form.
* **Conditional segment assignment:** Assigns a segment when a form field matches a specific value (e.g., when "Role" equals "Student", assign the "Students" segment).
* **Custom field mapping:** Maps any Elementor form field to a Flodesk custom field.
* **Double opt-in:** Optionally sends Flodesk's confirmation email before the subscriber is added.
* **Automatic updates:** Uses the Plugin Update Checker library to install new releases from this repository through the standard WordPress updater.

---

## Requirements

* WordPress with **Elementor Pro** active (the Forms widget is a Pro feature).
* PHP 7.4 or higher.
* A Flodesk account and API key.

---

## Installation

1. Download [`flodesk-elementor-integration.zip`](https://github.com/rosspotomactech/Flodesk-Elementor-Integration/releases/latest/download/flodesk-elementor-integration.zip) from the latest release.
2. Log in to the WordPress admin dashboard.
3. Navigate to **Plugins** > **Add New** > **Upload Plugin**.
4. Upload the `.zip` file, click **Install Now**, and then click **Activate**.

Use the `flodesk-elementor-integration.zip` file listed under the release's **Assets**, not the "Source code" archives or the green **Code** > **Download ZIP** button. The GitHub-generated archives include the version number in the folder name, which WordPress then treats as part of the plugin's directory.

---

## Configuration

### 1. Connect the Flodesk account

1. In Flodesk, navigate to **Account Settings** > **Integrations** > **API keys** and generate a new API key.
2. In WordPress, navigate to **Settings** > **Flodesk Settings**.
3. Paste the API key into the **Flodesk API Key** field and click **Save Changes**.

### 2. Add the action to an Elementor form

1. Open a page in the Elementor editor and select the **Form** widget.
2. In the **Actions After Submit** section, add the **Flodesk** action.
3. Expand the new **Flodesk** section and configure the following settings:

| Setting | Description |
| --- | --- |
| **Email Field ID** | The ID of the form field containing the email address. Defaults to `email`. Required. |
| **First Name Field ID** | Defaults to `first_name`. |
| **Last Name Field ID** | Defaults to `last_name`. |
| **Static Segment Assignment** | Segments applied to every submission from this form. |
| **Double Opt-In** | When enabled, Flodesk sends a confirmation email before the subscriber is added. |
| **Custom Fields Mapping** | Pairs of Flodesk custom field keys and Elementor form field IDs. |
| **Conditional Segment Mapping** | Rules of the form "if field *X* equals value *Y*, assign segment *Z*". |

4. Click **Update** or **Publish** to save the page.

A field's ID is shown in the Elementor editor under the field's **Advanced** tab, in the **ID** setting.

### Conditional segment rules

* Matching ignores letter case and surrounding spaces, so "student" matches "Student".
* The value must match the whole field value. Partial matches are not supported.
* A submission can match several rules and receive several segments. Duplicate segments are removed.
* For checkbox and multi-select fields, Elementor submits all selected options as one value, so a rule matches only when that exact combination is selected.

---

## Behavior and limitations

* **Segment caching:** The segment list is cached for 1 hour to stay within Flodesk's API rate limit. A segment created in Flodesk may take up to an hour to appear in the Elementor editor. To refresh it immediately, delete the cached list:

  ```bash
  wp transient delete flodesk_segments_options
  ```

* **Segment limit:** Only the first 100 segments in the Flodesk account are loaded into the dropdowns.
* **Error handling:** If Flodesk rejects a submission (e.g., because of an invalid API key or a missing email address), the form still submits successfully for the visitor. The Flodesk error message is shown only to logged-in users who can edit the page, e.g., administrators testing the form.
* **Data sent to Flodesk:** The email address, first name, last name, selected segments, mapped custom fields, the double opt-in setting and the visitor's IP address (as the opt-in IP).
* **Sanitization:** All form values are sanitized before they are sent to Flodesk.

---

## WP-CLI support

The API key can be set with WP-CLI, which is useful for scripted deployments:

```bash
wp option update flodesk_api_key "your_flodesk_api_key"
```

### Forcing plugin updates via WP-CLI

To check GitHub for an update and install it immediately:

```bash
wp transient delete update_plugins && wp plugin update flodesk-elementor-integration
```

---

## Development

* The plugin uses the Flodesk `/v1/subscribers` and `/v1/segments` endpoints.
* The `plugin-update-checker/` directory is committed to the repository and must stay inside the plugin folder for automatic updates to work.
* Publishing a GitHub release runs the **Build plugin ZIP** workflow, which attaches `flodesk-elementor-integration.zip` to the release. Files listed in `.distignore` are left out of the ZIP. To release a new version, update the `Version` header in `flodesk-elementor-integration.php` and this README, then publish a release tagged with the matching version (e.g., `v1.0.2`).

---

## License

Licensed under the GPL-3.0 License. See [LICENSE](LICENSE).
