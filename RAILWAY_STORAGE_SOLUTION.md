# Railway Storage Solution

## Problem
Railway's filesystem is ephemeral - uploaded files are lost on each deployment.

## Solutions

### Option 1: Railway Volumes (Recommended for Production)
1. Go to your Railway project dashboard
2. Click on your service
3. Go to "Settings" tab
4. Scroll to "Volumes" section
5. Click "New Volume"
6. Set mount path: `/app/storage/app/public`
7. Redeploy your service

**Cost**: Railway volumes are paid feature (~$0.25/GB/month)

### Option 2: Use Cloudinary (Free Tier Available)
1. Sign up at https://cloudinary.com (free tier: 25GB storage, 25GB bandwidth/month)
2. Install package: `composer require cloudinary-labs/cloudinary-laravel`
3. Add to `.env`:
   ```
   CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
   FILESYSTEM_DISK=cloudinary
   ```
4. Update `config/filesystems.php` to add cloudinary disk
5. Images will be stored in cloud, persist across deployments

### Option 3: Re-upload Images After Deployment
- Simply re-upload room images through admin panel after each deployment
- Quick fix but not ideal for production

### Option 4: Seed with Placeholder Images
- Use placeholder image URLs (like via.placeholder.com or unsplash)
- No storage needed, always available

## Current Setup
- Images are stored in `storage/app/public/rooms/`
- Accessed via `/storage/rooms/` URL
- Storage link created on startup via `start.sh`

## Recommendation
For production: Use Railway Volumes or Cloudinary
For testing: Re-upload images or use placeholder URLs
