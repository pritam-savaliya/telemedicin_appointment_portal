# Vercel Deployment Guide for TeleMedicine App

This guide explains how to deploy your PHP application to Vercel. Since Vercel is a serverless platform, there are specific requirements for PHP applications, particularly regarding the database and file storage.

## Prerequisites

1.  **Vercel Account**: Sign up at [vercel.com](https://vercel.com).
2.  **GitHub Repository**: Your code is already connected to GitHub.
3.  **Remote MySQL Database**: Vercel does *not* host databases. You need a cloud database.
    *   **Free/Cheap Options**:
        *   [Clever Cloud](https://www.clever-cloud.com/) (Free tier available)
        *   [Railway](https://railway.app/) (Trial/Paid)
        *   [Aiven](https://aiven.io/) (Free tier for MySQL)
        *   [PlanetScale](https://planetscale.com/) (Good scaling, but different pricing model)
        *   Any shared hosting database (if they allow remote connections).

---

## Step 1: Set Up Your Remote Database

1.  Create a MySQL database on your chosen provider.
2.  Import your local database schema:
    *   Export your local `telemedicine_db` from phpMyAdmin as a `.sql` file.
    *   Import this `.sql` file into your new remote database using the provider's tools (e.g., their phpMyAdmin or CLI).
3.  Get the connection details: `Host`, `Database Name`, `User`, `Password`, `Port` (default 3306).

---

## Step 2: Push Code to GitHub

Since your project is already linked to GitHub, push the latest changes (including `vercel.json` and updated `db.php`):

```bash
git add .
git commit -m "Prepare for Vercel deployment"
git push origin main
```

---

## Step 3: Deploy on Vercel

1.  Go to your [Vercel Dashboard](https://vercel.com/dashboard).
2.  Click **"Add New..."** -> **"Project"**.
3.  Import your GitHub repository (`telemedicin_appointment_portal`).
4.  In the **Configure Project** step:
    *   **Framework Preset**: Leave as "Other".
    *   **Root Directory**: Leave as `./`.
    *   **Environment Variables**: expand and add the following:
        *   `DB_HOST`: Your remote database host (e.g., `mysql-123.railway.app`)
        *   `DB_USER`: Your remote database username
        *   `DB_PASS`: Your remote database password
        *   `DB_NAME`: Your remote database name
        *   `BASE_URL`: The URL Vercel assigns you (e.g., `https://telemedicin-appointment.vercel.app/`). *Note: You can update this after deployment if you don't know it yet.*
5.  Click **Deploy**.

---

## Step 4: Finalize

1.  Once deployed, Vercel will give you a domain (e.g., `https://your-project.vercel.app`).
2.  Update the `BASE_URL` environment variable in Vercel settings if you didn't set it correctly in Step 3, and redeploy (or just check if it works).
3.  **Important Limitations**:
    *   **File Uploads**: Files uploaded to `uploads/` will **NOT** persist. In a serverless environment, the filesystem is temporary. You would need to update the code to upload to cloud storage (like AWS S3 or Cloudinary) for permanent storage.
    *   **Sessions**: PHP sessions might not stick if Vercel scales your functions across different servers. For a production app, use database-backed sessions or Redis.
