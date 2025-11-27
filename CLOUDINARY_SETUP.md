# Cloudinary Setup Guide

## Step 1: Sign Up for Cloudinary (FREE)

1. Go to https://cloudinary.com
2. Click "Sign Up Free"
3. Fill in your details
4. Verify your email

## Step 2: Get Your Credentials

1. After login, you'll see your **Dashboard**
2. Look for **Account Details** section
3. Copy these 3 values:
   - **Cloud Name**
   - **API Key**
   - **API Secret**

## Step 3: Update .env File

Add these to your `.env` file:

```env
CLOUDINARY_CLOUD_NAME=your_cloud_name_here
CLOUDINARY_API_KEY=your_api_key_here
CLOUDINARY_API_SECRET=your_api_secret_here
CLOUDINARY_URL=cloudinary://your_api_key:your_api_secret@your_cloud_name
```

## Step 4: Clear Config Cache

```bash
php artisan config:clear
```

## Step 5: Test Upload

Try uploading a customer photo or room image. It will now go to Cloudinary!

## How It Works

- **Local Development**: Files stored in `storage/app/public` (as before)
- **Production (Railway)**: Files stored in Cloudinary cloud
- **Automatic**: No code changes needed, works automatically based on APP_ENV

## Cloudinary Free Tier

- ✅ 25 GB Storage
- ✅ 25 GB Bandwidth/month
- ✅ Unlimited transformations
- ✅ Perfect for your hostel management system!

## Viewing Files

Files are accessible via Cloudinary URLs:
- Photos: `https://res.cloudinary.com/your_cloud_name/image/upload/...`
- Laravel handles this automatically with `Storage::url()`

## Railway Environment Variables

Add these to Railway:
1. Go to your Railway project
2. Click on your service
3. Go to "Variables" tab
4. Add:
   - `CLOUDINARY_CLOUD_NAME`
   - `CLOUDINARY_API_KEY`
   - `CLOUDINARY_API_SECRET`
   - `APP_ENV=production`

## Done!

Your file uploads will now work perfectly on Railway! 🎉
