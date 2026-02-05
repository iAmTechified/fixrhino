# FixRhino Landing Page Setup

## 1. Prerequisites
- **PHP Environment**: You need a local server (like XAMPP, MAMP, or standard PHP installed) to run the `submit.php` script.
- **Composer**: Needed to install PHPMailer.

## 2. Installation Steps

### Step 1: Install Dependencies
Open a terminal in this directory and run:
`composer require phpmailer/phpmailer`

### Step 2: Configure Google Sheets
To connect to Google Sheets without complex API keys, the easiest method is using a Google Apps Script Web App.

1. Create a new Google Sheet.
2. Go to **Extensions > Apps Script**.
3. Paste the code below into the script editor:

```javascript
function doPost(e) {
  var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
  var params = e.parameter;
  
  sheet.appendRow([
    params.timestamp,
    params.fullname,
    params.email,
    params.phone,
    params.devices
  ]);
  
  return ContentService.createTextOutput("Success");
}
```

4. Click **Deploy > New Deployment**.
5. Select **Type: Web app**.
6. Set **Execute as: Me**.
7. Set **Who has access: Anyone** (Important for the PHP script to be able to post to it).
8. Copy the **Web App URL**.
9. Open `submit.php` in your code editor and replace `'YOUR_GOOGLE_APPS_SCRIPT_WEB_APP_URL'` with the URL you just copied.

### Step 3: Configure Email (PHPMailer)
1. Open `submit.php`.
2. Update the SMTP settings (`Host`, `Username`, `Password`, `Port`) with your email provider's details (e.g., Gmail, SendGrid, Mailgun).

## 3. Running the Project
1. Start your local PHP server (e.g., `php -S localhost:8000`).
2. Open `http://localhost:8000` in your browser.
