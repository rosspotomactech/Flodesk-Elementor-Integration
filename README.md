# Flodesk Integration for Elementor Pro Forms

A lightweight, powerful WordPress plugin that connects your Elementor Pro forms directly to the Flodesk email marketing platform via the Flodesk REST API. 

This plugin extends Elementor Pro's "Actions After Submit" to seamlessly create or update subscribers, apply custom fields, and intelligently route users into specific Flodesk segments based on their form responses.

## ✨ Features

* **Native "Action After Submit":** Seamlessly integrates into the Elementor Form editor.
* **Smart Upserting:** Automatically creates a new subscriber or updates an existing one if the email already exists in your Flodesk account.
* **Dynamic Segment Dropdowns:** Automatically fetches and caches your Flodesk Segment IDs directly into the Elementor editor, saving you from having to look up IDs manually.
* **Static Segment Assignment:** Easily select one or multiple segments to assign to *all* users who submit a specific form.
* **Conditional Segment Mapping:** Route users to specific segments based on their form answers (e.g., If the "Role" field equals "Student", assign them to the "Students" segment in Flodesk).
* **Custom Field Mapping:** Map any Elementor form field to any Flodesk custom field.
* **Double Opt-in Support:** Toggle Flodesk's double opt-in confirmation email on or off for new subscribers.
* **Automatic Updates:** Integrated with `plugin-update-checker` to automatically receive updates directly from this GitHub repository.

## 📋 Requirements

* WordPress 5.8 or higher.
* Elementor Pro (Required for the Forms widget).
* A Flodesk account and an active API Key.

## 🚀 Installation

Since this plugin is hosted on GitHub, you can install it manually:

1. Go to the [Releases page](https://github.com/rosspotomactech/Flodesk-Elementor-Integration/releases) of this repository (or download the `main` branch as a `.zip` file).
2. Log in to your WordPress admin dashboard.
3. Navigate to **Plugins > Add New** and click **Upload Plugin**.
4. Choose the downloaded `.zip` file and click **Install Now**.
5. Click **Activate Plugin**.

## ⚙️ Configuration & Usage

### 1. Set Your API Key
Before using the integration, you need to connect the plugin to your Flodesk account using Basic Authentication[span_8](start_span)[span_8](end_span)[span_9](start_span)[span_9](end_span).
1. Log into your Flodesk account and navigate to **Settings > Integrations > API keys** to generate a new API Key.
2. In your WordPress dashboard, go to **Settings > Flodesk Settings**.
3. Paste your API Key into the field and click **Save Changes**.

### 2. Configure Your Elementor Form
1. Open a page or post in the Elementor editor and add/edit a **Form** widget.
2. In the left panel, expand the **Actions After Submit** section.
3. Click the "Add Action" field and select **Flodesk**.
4. A new **Flodesk** settings section will appear below. Expand it.
5. **Map Core Fields:** Enter the Elementor Field IDs for Email, First Name, and Last Name. *(You can find a field's ID by clicking on it in the "Form Fields" section and looking at the "Advanced" tab).*
6. **Assign Static Segments:** Select any segments you want applied to everyone who submits this form. *(Note: Segments are cached for 1 hour to respect Flodesk's API rate limits.*
7. **Set Conditional Segments (Optional):** Add routing rules based on form responses. Enter the Elementor Field ID, the exact value you want to match, and select the corresponding Flodesk segment target.
8. **Map Custom Fields (Optional):** Map additional Elementor Field IDs to your custom Flodesk field keys.
9. **Update/Publish** your page.

## 🔒 Security & Performance

* **API Rate Limiting:** Flodesk limits API requests to 100 per minute. To prevent hitting this limit while editing Elementor forms, segment lookups are cached in WordPress transients for 60 minutes.
* **Sanitization:** All form data and inputs are sanitized according to WordPress best practices before being passed to the Flodesk API.

## 👨‍💻 Developer Notes

This plugin utilizes the Flodesk `/v1/subscribers` API endpoint. It requires the `plugin-update-checker` library located in the `/plugin-update-checker/` directory for GitHub release tracking. Ensure this folder remains intact if you are forking or cloning the repository.

## 📝 License

This project is licensed under the GPL-3.0 License.
