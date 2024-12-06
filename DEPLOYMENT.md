# Deploying to Google Cloud Run with Cloud SQL

This guide explains how to deploy this Laravel application to Google Cloud Run with Cloud SQL as the database provider.

## Prerequisites

1. Google Cloud Account with billing enabled
2. Google Cloud CLI installed locally
3. GitHub account with access to the repository
4. Basic understanding of Laravel and Docker

## Step-by-Step Deployment Guide

### 1. Initial Google Cloud Setup

1. Create a new Google Cloud Project
   ```bash
   gcloud projects create YOUR_PROJECT_ID
   gcloud config set project YOUR_PROJECT_ID
   ```

2. Enable required APIs:
   ```bash
   gcloud services enable \
     run.googleapis.com \
     sql-component.googleapis.com \
     cloudbuild.googleapis.com \
     artifactregistry.googleapis.com
   ```

### 2. Set Up Cloud SQL

1. Create a Cloud SQL instance:
   ```bash
   gcloud sql instances create ssd-test-db \
     --database-version=MYSQL_8_0 \
     --region=asia-southeast2 \
     --root-password=YOUR_DB_PASSWORD
   ```

2. Create a database:
   ```bash
   gcloud sql databases create ssd_test --instance=ssd-test-db
   ```

3. Note down your instance connection name:
   ```
   YOUR_PROJECT_ID:asia-southeast2:ssd-test-db
   ```

### 3. Set Up Artifact Registry

1. Create a Docker repository:
   ```bash
   gcloud artifacts repositories create ssd-test \
     --repository-format=docker \
     --location=asia-southeast2
   ```

### 4. Create Service Account

1. Create a service account:
   ```bash
   gcloud iam service-accounts create github-actions-service
   ```

2. Add necessary roles:
   ```bash
   gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
     --member="serviceAccount:github-actions-service@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
     --role="roles/run.admin"

   gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
     --member="serviceAccount:github-actions-service@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
     --role="roles/artifactregistry.admin"

   gcloud projects add-iam-policy-binding YOUR_PROJECT_ID \
     --member="serviceAccount:github-actions-service@YOUR_PROJECT_ID.iam.gserviceaccount.com" \
     --role="roles/cloudsql.client"
   ```

3. Generate and download the key:
   ```bash
   gcloud iam service-accounts keys create key.json \
     --iam-account=github-actions-service@YOUR_PROJECT_ID.iam.gserviceaccount.com
   ```

### 5. GitHub Repository Setup

1. Go to your repository's Settings > Environments
2. Create a new environment named `env`
3. Add the following secrets:

   ```
   GCP_SA_KEY: (Content of your key.json file)
   CLOUD_SQL_INSTANCE: YOUR_PROJECT_ID:asia-southeast2:ssd-test-db
   PROJECT_ID: YOUR_PROJECT_ID
   REGION: asia-southeast2
   SERVICE_NAME: ssd-test

   APP_KEY: (Your Laravel app key)
   APP_DEBUG: false
   APP_ENV: production
   
   DB_CONNECTION: mysql
   DB_HOST: /cloudsql/YOUR_PROJECT_ID:asia-southeast2:ssd-test-db
   DB_PORT: 3306
   DB_DATABASE: ssd_test
   DB_USERNAME: root
   DB_PASSWORD: YOUR_DB_PASSWORD
   ```

### 6. First Deployment

1. Push your code to the main branch
2. GitHub Actions will automatically:
   - Build the Docker image
   - Push to Artifact Registry
   - Deploy to Cloud Run

3. After first deployment, run database migrations using Cloud Run Jobs:
   - Go to Cloud Run > Jobs
   - Create a new job
   - Use the same image as your service
   - Set command to: `php artisan migrate`
   - Add the same environment variables as your service
   - Run the job

### 7. Verify Deployment

1. Get your service URL:
   ```bash
   gcloud run services describe ssd-test --region=asia-southeast2 --format='value(status.url)'
   ```

2. Visit the URL to verify the application is working

## Important Notes

1. The application uses Cloud SQL Auth Proxy for secure database connections
2. All assets are served over HTTPS
3. The deployment uses the latest Docker image tag for consistency
4. Environment variables are managed through GitHub Secrets
5. Database migrations should be run manually after deployment

## Troubleshooting

1. If you see 500 errors, check Cloud Run logs
2. For database connection issues, verify your Cloud SQL instance name and credentials
3. For HTTPS/asset issues, ensure APP_URL and ASSET_URL are properly set
4. For permission issues, verify service account roles

## Security Considerations

1. All sensitive data is stored in GitHub Secrets
2. Cloud SQL Auth Proxy is used for secure database connections
3. HTTPS is enforced for all traffic
4. Service account has minimal required permissions
5. Database credentials are never exposed in the codebase
